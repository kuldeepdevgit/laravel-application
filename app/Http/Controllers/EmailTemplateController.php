<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\TemplateMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email.index', [
            'templates' => EmailTemplate::ordered(),
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('admin.email.edit', [
            'template' => $emailTemplate,
            'placeholders' => $emailTemplate->placeholders(),
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->update($this->validated($request));

        return redirect()
            ->route('admin.email.edit', $emailTemplate)
            ->with('status', 'Email template saved.');
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->delete();

        return redirect()
            ->route('admin.email.index')
            ->with('status', 'Email template deleted.');
    }

    public function resend(Request $request, EmailTemplate $emailTemplate, TemplateMailer $mailer): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'No member uses that email.',
        ]);

        if (! $emailTemplate->is_active) {
            return back()->withErrors([
                'email' => 'Turn this template on before resending it.',
            ])->withInput();
        }

        $member = User::query()->where('email', $data['email'])->first();

        if ($member === null) {
            return back()->withErrors([
                'email' => 'No member uses that email.',
            ])->withInput();
        }

        $extra = $this->resendTokens($emailTemplate, $member);
        $delivery = $mailer->send($member, $emailTemplate->key, $extra);

        if ($delivery !== 'sent') {
            return back()->withErrors([
                'email' => 'The email could not be sent.',
            ])->withInput();
        }

        return redirect()
            ->route('admin.email.index')
            ->with('status', 'Email resent to '.$member->email.'.');
    }

    /**
     * @return array<string, string>
     */
    private function resendTokens(EmailTemplate $emailTemplate, User $member): array
    {
        if ($emailTemplate->key === EmailTemplate::FORGOT_PASSWORD) {
            $token = Password::broker()->createToken($member);

            return [
                '{reset_url}' => url(route('password.reset', [
                    'token' => $token,
                    'email' => $member->getEmailForPasswordReset(),
                ], false)),
            ];
        }

        if ($emailTemplate->key === EmailTemplate::WELCOME) {
            return [
                '{password}' => 'Not included on resend',
            ];
        }

        return [];
    }

    /**
     * @return array{subject: string, body: string, is_active: bool}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ]);

        return [
            'subject' => $data['subject'],
            'body' => $data['body'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
