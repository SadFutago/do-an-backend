<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;

class VoucherController extends Controller
{
    private const SEARCH_FIELDS = ['code'];
    private const FILTER_FIELDS = [];

    public function approve(Request $request, string $id) {
        $approverId = $request->user_id;
        $voucher = Voucher::findOrFail($id);
        $voucher->approved_by = $approverId;
        $voucher->save();
        return response()->json(['message' => 'success'], 200);
    }

    public function checkCoupon(Request $request, string $couponCode) {
        $userId = $request->user_id;
        $voucher = Voucher::where('code', $couponCode)->first();
        if (!$voucher || !$voucher->isValid()) {
            return response()->json(['message' => 'Mã giảm giá không hợp lệ'], 400);
        }
        if ($voucher->isExhausted()) {
            return response()->json(['message' => 'Mã giảm giá đã hết lượt sử dụng'], 400);
        }
        if ($voucher->isUsedByUser($userId)) {
            return response()->json(['message' => 'Mã giảm giá đã được sử dụng'], 400);
        }
        return response()->json(['message' => 'Mã giảm giá hợp lệ', 'id' => $voucher->id, 'value' => $voucher->value]);
    }

    public function index(Request $request) {
        $query = Voucher::with([
            'creator:id,name',
            'approver:id,name',
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Voucher::onlyTrashed()->with([
            'creator:id,name',
            'approver:id,name',
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function show(string $id) {
        $voucher = Voucher::findOrFail($id);
        return response()->json($voucher);
    }
    public function store(Request $request) {
        Voucher::create($request->all());
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $voucher = Voucher::findOrFail($id);
        $voucher->update($request->all());
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $voucher = Voucher::onlyTrashed()->findOrFail($id);
        $voucher->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $voucher = Voucher::onlyTrashed()->findOrFail($id);
        $voucher->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Voucher::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Voucher::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Voucher::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ'], 400);
        }
    }
}
