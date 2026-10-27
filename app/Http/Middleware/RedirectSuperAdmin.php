<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperAdmin
{
    /**
     * Send the super admin to the admin desk instead of the member dashboard.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
