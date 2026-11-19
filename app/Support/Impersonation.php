<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class Impersonation
{
    public const string KEY = 'impersonated_user_id';

    public static function start(Request $request, User $customer): void
    {
        $request->session()->put(self::KEY, (int) $customer->getKey());
    }

    public static function stop(Request $request): void
    {
        $request->session()->forget(self::KEY);
    }

    /**
     * The customer account a super admin is viewing, when one is selected.
     */
    public static function customer(Request $request): ?User
    {
        $id = $request->session()->get(self::KEY);

        if (! is_numeric($id)) {
            return null;
        }

        $customer = User::query()->find((int) $id);

        if (! $customer instanceof User || $customer->isSuperAdmin()) {
            $request->session()->forget(self::KEY);

            return null;
        }

        return $customer;
    }

    /**
     * The account shown on the member dashboard.
     * A super admin who is viewing a customer sees that customer. Everyone else sees their own account.
     */
    public static function account(Request $request): ?User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        if (! $user->isSuperAdmin()) {
            return $user;
        }

        return self::customer($request);
    }
}
