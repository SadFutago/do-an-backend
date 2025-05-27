<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SortMiddleware
{
    /**
     * Handle an incoming request.  
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sort = [
            'enabled' => $request->has('_sort'),
            'type' => $request->query('type', 'asc'),
            'column' => $request->query('column', 'id'),
        ];

        if (!$sort['enabled']) {
            $sort = [
                'enabled' => false,
                'type' => 'default',
            ];
        }

        $request->merge(['_sort' => $sort]);

        return $next($request);
    }
}
