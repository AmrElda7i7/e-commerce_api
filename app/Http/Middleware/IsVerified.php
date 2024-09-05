<?php

namespace App\Http\Middleware;

use App\Traits\ApiHandlerTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsVerified
{
    use ApiHandlerTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user->is_verified) {
            return $this->errorResponse('You need to verify your email first.', 403);
        }

        return $next($request);
    }
}
