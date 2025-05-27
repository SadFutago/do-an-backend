<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private const SEARCH_FIELDS = ['details.product.name'];
    private const FILTER_FIELDS = [
        'filterByVoucher' => ['column' => 'voucher_id'],
        'filterByPaymentStatus' => ['column' => 'payment_status'],
        'filterByStatus' => ['column' => 'status'],
        'filterByUnapproved' => ['column' => 'approved_by'],
    ];
    
    private function isVoucherValid(Voucher $voucher, $userId): bool {
        if (!$voucher->isValid()) {
            return false;
        }
        if ($voucher->isExhausted()) {
            return false;
        }
        if ($voucher->isUsedByUser($userId)) {
            return false;
        }
        return true;
    }

    private function applyVoucher(int $voucherId, int $userId, Order $order, float $totalAmount): float {
        $voucher = Voucher::find($voucherId);
        if (!$voucher || !$this->isVoucherValid($voucher, $userId)) {
            return $totalAmount;
        }
        $totalAmount -= $voucher->value;
        $order->update(['voucher_id' => $voucherId]);
        $voucher->increment('used_count');
        VoucherUsage::create([
            'user_id' => $userId,
            'voucher_id' => $voucherId,
            'order_id' => $order->id,
        ]);
        return max($totalAmount, 0);
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $userId = $request->user_id;
            $order = Order::create([
                'user_id' => $userId,
                'name' => $request->name,
                'phone' => $request->phone,
                'city_id' => $request->city_id,
                'district_id' => $request->district_id,
                'ward_id' => $request->ward_id,
                'shipping_address' => $request->shipping_address,
                'address_type' => $request->address_type,
                'payment_method' => $request->payment_method,
                'shipping_fee' => $request->shipping_fee,
                'total_amount' => 0,
            ]);
            $total_amount = $order->shipping_fee;
            foreach ($request->details as $detail) {
                $total_amount += $detail['price'] * $detail['quantity'];
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                ]);
            }
            if ($request->voucher_id) {
                $total_amount = $this->applyVoucher($request->voucher_id, $userId, $order, $total_amount);
            }
            $order->update(['total_amount' => max($total_amount, 0)]);
            DB::commit();

            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }   

    public function getOrderByUser(Request $request, string $id) {
        $userId = $request->user_id;
        $order = Order::with([
            'voucher:id,code,value',
            'details:id,order_id,product_id,quantity,price',
            'details.product:id,name,description,price,original_price,slug',
            'details.product.images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id')->limit(1);
            },
        ])->where('id', $id)->where('user_id', $userId)->firstOrFail();
        // gán biến isReviewed cho từng detail
        $reviewedProducts = Review::where('order_id', $id)
            ->where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
        foreach ($order->details as $detail) {
            $detail->setAttribute('isReviewed', in_array($detail->product_id, $reviewedProducts));
        }
        // gán thông tin thành phố, quận, phường
        $orderArray = $order->toArray();
        $orderArray['city'] = $order->getCity();
        $orderArray['district'] = $order->getDistrict();
        $orderArray['ward'] = $order->getWard();

        return response()->json($orderArray);
    }

    public function getOrdersByUser(Request $request) {
        $userId = $request->user_id;
        $query = Order::with([
            'details:id,order_id,product_id,quantity,price',
            'details.product:id,name,description,price,original_price,slug',
            'details.product.images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id')->limit(1);
            },
        ])->where('user_id', $userId)->orderBy('created_at', 'desc');
        $query = $this->search($query, $request->query('key'), self::SEARCH_FIELDS);
        $action = $request->input('action');
        switch ($action) {
            case 'filterUnpaid':
                $query->where('payment_status', Order::PAYMENT_STATUS_PENDING)->where('status', Order::STATUS_PENDING);
                break;
            case 'filterPacking':
                $query->where('status', Order::STATUS_PACKED);
                break;
            case 'filterShipping':
                $query->where('status', Order::STATUS_SHIPPED);
                break;
            case 'filterCompleted':
                $query->where('status', Order::STATUS_COMPLETED);
                break;
            case 'filterCancelled':
                $query->where('status', Order::STATUS_CANCELLED);
                break;
            default:
                break;
        }
        $perPage = config('app.per_page');
        $orders = $query->paginate($perPage);
        $response = [
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ];

        if (count($response['data'])) {
            $orderIds = collect($response['data'])->pluck('id')->toArray();
            $reviewedItems = Review::where('user_id', $userId)
                ->whereIn('order_id', $orderIds)
                ->select('order_id', 'product_id')
                ->get();
            $reviewMap = [];
            foreach ($reviewedItems as $review) {
                $key = $review->order_id . '-' . $review->product_id;
                $reviewMap[$key] = true;
            }
            foreach ($response['data'] as &$order) {
                if (isset($order['details'])) {
                    foreach ($order['details'] as &$detail) {
                        $key = $order['id'] . '-' . $detail['product_id'];
                        $detail['isReviewed'] = isset($reviewMap[$key]);
                    }
                }
            }
        }
        return response()->json($response);
    }

    public function cancelOrderByUser(Request $request, string $id) {
        $userId = $request->user_id;
        $order = Order::where('id', $id)->where('user_id', $userId)->firstOrFail();
        if ($order->status === Order::STATUS_PENDING) {
            $order->update(['status' => Order::STATUS_CANCELLED, 'cancel_reason' => $request->cancelReason, 'cancelled_at' => now()]);
            $order->load([
                'details' => function ($query) {
                    $query->select('id', 'order_id', 'product_id', 'quantity', 'price');
                },
                'details.product' => function ($query) {
                    $query->select('id', 'name', 'description', 'price', 'original_price', 'slug');
                },
                'details.product.images' => function ($query) {
                    $query->where('is_thumbnail', true)
                          ->select('id', 'path', 'product_id')
                          ->limit(1);
                },
            ]);
            return response()->json($order);
        }
    }

    public function changeOrderStatus(Request $request, string $id) {
        $userId = $request->user_id;
        $order = Order::findOrFail($id);
        $status = $request->status;
        switch ($status) {
            case 'paid':
                $order->payment_status = Order::PAYMENT_STATUS_PAID;
                $order->payment_info = $request->payment_info;
                break;
            case 'approved':
                $order->approved_by = $userId;
                break;
            case 'shipped':
                $order->status = Order::STATUS_SHIPPED;
                $order->shipped_at = now();
                break;
            case 'completed':
                $order->status = Order::STATUS_COMPLETED;
                $order->completed_at = now();
                break;
            default:
                return response()->json(['message' => 'invalid'], 400);
        }
        $order->save();
        return response()->json($order);
    }

    public function index(Request $request) {
        $query = Order::with([
            'user:id,name',
            'approver:id,name',
            'voucher:id,code,value',
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function trashed(Request $request) {
        $query = Order::onlyTrashed()->with([
            'user:id,name',
            'voucher:id,code,value',
            'approver:id,name',
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function show(string $id) {
        $order = Order::with([
            'approver:id,name',
            'voucher:id,code,value',
            'details:id,order_id,product_id,quantity,price',
            'details.product:id,name,description',
            'details.product.images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id')->limit(1);
            },
        ])->findOrFail($id);
        return response()->json($order);
    }

    public function destroy(string $id) {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function restore(string $id) {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();
        return response()->json(['message' => 'success'], 200);
    }

    public function forceDelete(string $id) {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }

    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Order::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Order::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Order::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Action is invalid'], 400);
        }
    }
}
