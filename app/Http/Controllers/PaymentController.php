<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller      // chạy ngrok và đổi app_url trong .env
{   
    private function savePaymentInfo(Order $order, $transId, $amount, $paymentDate, $paymentInfo) {
        $payment_info = sprintf(
            "Mã GD: %s | Số tiền: %s | Thời gian: %s | Kiểu TT: %s",
            $transId,
            number_format($amount) . "đ",
            $paymentDate,
            $paymentInfo,
        );
        $order->payment_info = $payment_info;
        $order->payment_status = Order::PAYMENT_STATUS_PAID;

        return $order->save();
    }

    private function getOrderTotalAmount($orderId, $userId)
    {
        $order = Order::where('id', $orderId)->where('user_id', $userId)->where('payment_status', Order::PAYMENT_STATUS_PENDING)->firstOrFail();
        return $order->total_amount;
    }

    public function createMomoPayment(Request $request)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $orderInfo = "Thanh toan qua Momo don hang #" . $request->id;
        $amount = $this->getOrderTotalAmount($request->id, $request->user_id);
        $orderId = $request->id . '_' . time();
        $redirectUrl = env('APP_FE_URL') . "/theo-doi-don-hang/" . $request->id;
        $ipnUrl = env('APP_URL') . "/api/payments/momo/ipn";
        $requestType = "captureWallet"; //captureWallet, payWithATM
        $extraData = "";

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $orderId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            'storeId' => "MomoTestStore",
            'requestId' => $orderId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        return response()->json($jsonResult);
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function momoIPN(Request $request)
    {
        $resultCode = $request->resultCode;
        $orderId = explode('_', $request->orderId)[0];
        
        if ($resultCode == 0) {
            $order = Order::find($orderId);
            if ($order && $order->payment_status == Order::PAYMENT_STATUS_PENDING) {
                $this->savePaymentInfo(
                    $order,
                    $request->transId,
                    $request->amount,
                    date('Y-m-d H:i:s', $request->responseTime/1000),
                    'momo - ' . $request->payType
                );
                return response()->json(['message' => 'success']);
            }
        }
        return response()->json(['message' => 'failed']);
    }

    public function createVNPayPayment(Request $request) 
    {
        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = env('APP_URL') . '/api/payments/vnpay/return';

        $vnp_TxnRef = $request->id . '_' . time();
        $vnp_OrderInfo = "Thanh toan qua VNPay don hang #" . $request->id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $this->getOrderTotalAmount($request->id, $request->user_id) * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return response()->json([
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        ]);
    }

    public function vnpayIPN(Request $request)  // muốn hoạt động cần phải set trên tài khoản merchant VNPAY
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = array();
        $vnp_SecureHash = $request->input('vnp_SecureHash');
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash == $vnp_SecureHash) {
            $vnp_TxnRef = $request->vnp_TxnRef;
            $orderId = explode('_', $vnp_TxnRef)[0];
            if ($request->input('vnp_ResponseCode') == '00') {
                $order = Order::find($orderId);
                if ($order && $order->payment_status == Order::PAYMENT_STATUS_PENDING) {
                    $this->savePaymentInfo(
                        $order,
                        $request->vnp_TransactionNo,
                        $request->vnp_Amount/100,
                        date('Y-m-d H:i:s', strtotime($request->vnp_PayDate)),
                        'vnpay - ' . $request->vnp_BankCode
                    );
                    $order->save();
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            } else {
                $returnData['RspCode'] = '02';
                $returnData['Message'] = 'Transaction failed';
            }
        } else {
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid signature';
        }
        return response()->json($returnData);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = array();
        $vnp_SecureHash = $request->input('vnp_SecureHash');
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnp_TxnRef = $request->vnp_TxnRef;
        $orderId = explode('_', $vnp_TxnRef)[0];
        $redirectUrl = env('APP_FE_URL') . "/theo-doi-don-hang/" . $orderId;
        if ($secureHash == $vnp_SecureHash) {
            if ($request->input('vnp_ResponseCode') == '00') {
                $order = Order::find($orderId);
                if ($order && $order->payment_status == Order::PAYMENT_STATUS_PENDING) {
                    $this->savePaymentInfo(
                        $order,
                        $request->vnp_TransactionNo,
                        $request->vnp_Amount/100,
                        date('Y-m-d H:i:s', strtotime($request->vnp_PayDate)),
                        'vnpay - ' . $request->vnp_CardType
                    );
                }
            }
        }
        return redirect($redirectUrl);
    }

    public function createZaloPayPayment(Request $request)
    {
        $app_id = env('ZALOPAY_APP_ID');
        $key1 = env('ZALOPAY_KEY1');
        $endpoint = "https://sb-openapi.zalopay.vn/v2/create";

        $app_trans_id = date("ymd") . "_" . $request->id . '_' . time();
        $app_time = round(microtime(true) * 1000);
        $amount = (int)$this->getOrderTotalAmount($request->id, $request->user_id);
        $app_user = "user" . time();
        
        $embed_data = [
            "redirecturl" => env('APP_FE_URL') . "/theo-doi-don-hang/" . $request->id
        ];
        $item = [];
        $description = "Thanh toan qua ZaloPay don hang #" . $request->id;
        
        $order_data = array(
            'app_id' => $app_id,
            'app_trans_id' => $app_trans_id,
            'app_user' => $app_user,
            'app_time' => $app_time,
            'amount' => $amount,
            'embed_data' => json_encode($embed_data),
            'item' => json_encode($item),
            'description' => $description,
            'bank_code' => "",
            'callback_url' => env('APP_URL') . "/api/payments/zalopay/callback"
        );

        $data = $app_id . "|" . $app_trans_id . "|" . $app_user . "|" . $amount . "|" . $app_time . "|" . json_encode($embed_data) . "|" . json_encode($item);
        $order_data['mac'] = hash_hmac('sha256', $data, $key1);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        return response()->json($response);
    }

    public function zaloPayCallback(Request $request)
    {
        $key2 = env('ZALOPAY_KEY2');
        
        try {
            $data = $request->data;
            $mac = $request->mac;

            $mac_data = $data;
            $verify_mac = hash_hmac("sha256", $mac_data, $key2);
            
            if ($verify_mac !== $mac) {
                return response()->json([
                    "return_code" => -1,
                    "return_message" => "mac not equal"
                ]);
            }

            $data_array = json_decode($data, true);
            
            $app_trans_id_parts = explode('_', $data_array["app_trans_id"]);
            $orderId = $app_trans_id_parts[1];
            
            $order = Order::find($orderId);
            if ($order && $order->payment_status == Order::PAYMENT_STATUS_PENDING) {
                $channel = [
                    36 => 'Visa/Master/JCB',
                    37 => 'Bank Account',
                    38 => 'ZaloPay Wallet',
                    39 => 'ATM',
                    41 => 'Visa/Master Debit'
                ];
                $this->savePaymentInfo(
                    $order,
                    $data_array["zp_trans_id"],
                    $data_array["amount"],
                    date('Y-m-d H:i:s', $data_array["server_time"]/1000),
                    'zaloPay - ' . $channel[$data_array["channel"]]
                );
                return response()->json([
                    "return_code" => 1,
                    "return_message" => "success"
                ]);
            }
            
            return response()->json([
                "return_code" => 0,
                "return_message" => "failed"
            ]);
            
        } catch (\Exception $e) {
            Log::error("ZaloPay Callback Error: " . $e->getMessage());
            return response()->json([
                "return_code" => -1,
                "return_message" => "Internal Server Error"
            ]);
        }
    }

    public function CODPayment(Request $request)
    {
        $order = Order::find($request->id)->where('user_id', $request->user_id)->where('payment_status', Order::PAYMENT_STATUS_PENDING)->firstOrFail();
        if ($order) {
            $order->payment_status = Order::PAYMENT_STATUS_PAID;
            $order->payment_info = "Thanh toan COD";
            $order->save();
            return response()->json([
                "message" => "success"
            ]);
        }
        return response()->json([
            "message" => "failed"
        ]);
    }

    public function createQRCodePayment(Request $request)
    {
        $vietQrUrl = "https://api.vietqr.io/v2/generate";
        $bankId = env('VIETQR_BANK_ID');
        $accountNo = env('VIETQR_ACCOUNT_NO'); 
        $accountName = env('VIETQR_ACCOUNT_NAME');
        $description = "Thanh toan don hang #" . $request->id;
        $totalAmount = $this->getOrderTotalAmount($request->id, $request->user_id);

        $data = [
            'accountNo' => $accountNo,
            'accountName' => $accountName,
            'acqId' => $bankId,
            'amount' => $totalAmount,
            'addInfo' => $description,
            'template' => 'compact',
            'format' => 'png'
        ];

        $apiKey = env('VIETQR_API_KEY');
        $apiSecret = env('VIETQR_API_SECRET');
        $ch = curl_init($vietQrUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'x-client-id: ' . $apiSecret,
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        return response()->json($response);
    }
}