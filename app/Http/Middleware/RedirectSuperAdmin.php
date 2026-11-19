<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Impersonation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
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
            $customer = Impersonation::customer($request);

            if (! $customer instanceof User) {
                return redirect()->route('admin.dashboard');
            }

            View::share('impersonatedCustomer', $customer);
            View::share('dashboardAccount', $customer);

            return $next($request);
        }

        View::share('impersonatedCustomer', null);
        View::share('dashboardAccount', $user);

        return $next($request);
    }
}
