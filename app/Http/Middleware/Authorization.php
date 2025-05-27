<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authorization
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        try {
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json([
                    'message' => 'Không tìm thấy người dùng',
                    'code' => 1001,
                ], 400);
            }
            if (!$user->hasPermission($permission)) {
                return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này'], 403);
            }
            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
