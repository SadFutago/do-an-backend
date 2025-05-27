<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (User::where('username', $request->username)->exists()) {
            return response()->json([
                'message' => 'Tên tài khoản đã tồn tại. Vui lòng chọn tên tài khoản khác.'
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        $token = auth('api')->login($user);

        return $this->respondWithToken($token);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không chính xác. Vui lòng kiểm tra lại.'], 422
            );
        }

        if (auth('api')->user()->status === User::STATUS_BANNED) {
            return response()->json([
                'message' => 'Tài khoản đã bị cấm. Vui lòng liên hệ quản trị viên.'
            ], 400);
        }

        return $this->respondWithToken($token);
    }
    
    public function me()
    {
        $user = auth('api')->user();
        return response()->json($user->load(['avatar:id,path']));
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'success']);
    }

    public function refresh()
    {
        auth('api')->factory()->setTTL(config('jwt.refresh_ttl'));
        $token = auth('api')->refresh();
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}