<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithJWT
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'message' => 'Không tìm thấy người dùng',
                    'code' => 1001,
                ], 401);
            }
            $request->merge(['user_id' => $user->id]);
            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token đã hết hạn',
                'code' => 1002,
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token không hợp lệ',
                'code' => 1003,
            ], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
