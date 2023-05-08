<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class CheckIsAdminOrFriendOrSelf
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $id = $request->route()->parameter('id');
        $user = User::findOrFail($id);

        if (
            auth()->user()->role === 0 ||
            auth()->user()->id === $user->id ||
            auth()->user()->isFriendWith($user)
        ) return $next($request);

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
