<?php

namespace App\Http\Controllers;

use App\Models\SmtpSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmtpSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.smtp.edit', [
            'smtp' => SmtpSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        SmtpSetting::current()->update($this->validated($request));

        return redirect()
            ->route('admin.smtp.edit')
            ->with('status', 'SMTP settings saved.');
    }

    /**
     * @return array{host: string, port: int, encryption: string, username: string, password?: string, from_name: string, from_address: string}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
        ]);

        $password = trim((string) ($data['password'] ?? ''));

        $attributes = [
            'host' => $data['host'],
            'port' => (int) $data['port'],
            'encryption' => $data['encryption'],
            'username' => $data['username'],
            'from_name' => $data['from_name'],
            'from_address' => $data['from_address'],
        ];

        if ($password !== '') {
            $attributes['password'] = $password;
        }

        return $attributes;
    }
}
