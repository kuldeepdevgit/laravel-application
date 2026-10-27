<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $home = $request->user()->isSuperAdmin()
            ? route('admin.dashboard')
            : route('dashboard');

        return redirect()->intended($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function createRegister(Request $request): View
    {
        return view('auth.register', [
            'sponsorName' => (string) $request->query('ref', ''),
        ]);
    }

    public function sponsor(Request $request): JsonResponse
    {
        $name = User::sponsorFullName((string) $request->query('ref', ''));

        if ($name === null) {
            return response()->json(['name' => null], 404);
        }

        return response()->json(['name' => $name]);
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sponsor_name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! User::isValidSponsor((string) $value)) {
                        $fail('The sponsor name is not valid.');
                    }
                },
            ],
            'position' => ['required', 'in:left,right'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $data['user_name'] = User::generateUserName();
        $data['user_role'] = 'users';

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('registered')
            ->with('signup_user_name', $user->user_name)
            ->with('signup_password', $data['password']);
    }

    public function registered(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('signup_user_name')) {
            return redirect()->route('dashboard');
        }

        return view('auth.registered', [
            'userName' => $request->session()->get('signup_user_name'),
            'password' => $request->session()->get('signup_password'),
        ]);
    }

    public function createForgot(): View
    {
        return view('auth.forgot');
    }

    public function storeForgot(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return back()->with('status', __($status));
    }

    public function createReset(string $token): View
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => request('email', ''),
        ]);
    }

    public function storeReset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return redirect()
            ->route('login')
            ->with('status', __($status));
    }
}
