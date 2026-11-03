<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $customers = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('name', 'like', $like)
                        ->orWhere('user_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone_number', 'like', $like)
                        ->orWhere('sponsor_name', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
            'total' => User::query()->count(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.customers.edit', [
            'customer' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $password = $data['password'] ?? null;
        unset($data['password']);

        if ($user->isSuperAdmin()) {
            $data['email'] = $user->email;
        }

        if (is_string($password) && $password !== '') {
            $data['password'] = $password;
        }

        $user->update($data);

        return redirect()
            ->route('admin.customers.index')
            ->with('status', 'Customer saved.');
    }

    public function login(Request $request, User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            abort(403);
        }

        $adminId = (int) $request->user()->getKey();

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('impersonator_id', $adminId);

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonator_id');
        $admin = is_numeric($adminId)
            ? User::query()->find((int) $adminId)
            : null;

        if (! $admin instanceof User || ! $admin->isSuperAdmin()) {
            abort(403);
        }

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.customers.index')
            ->with('status', 'Signed back in as Super Admin.');
    }

    /**
     * @return array{
     *     name: string,
     *     user_name: string,
     *     email: string,
     *     phone_number: string|null,
     *     sponsor_name: string|null,
     *     position: string|null,
     *     address: string|null,
     *     city: string|null,
     *     state: string|null,
     *     zip_code: string|null,
     *     usdt_bep20: string|null,
     *     password?: string|null
     * }
     */
    private function validated(Request $request, User $user): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'user_name')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => [$user->isSuperAdmin() ? 'nullable' : 'required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'sponsor_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', Rule::in(['left', 'right'])],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'usdt_bep20' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'user_name.regex' => 'Enter a user name using letters and numbers.',
            'phone_number.regex' => 'Enter a valid phone number.',
            'usdt_bep20.regex' => 'Enter a USDT BEP 20 address.',
        ]);
    }
}
