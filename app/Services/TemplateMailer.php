<?php

namespace App\Services;

use App\Mail\TemplateMessage;
use App\Models\EmailTemplate;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TemplateMailer
{
    /**
     * Send one stored template to a member.
     *
     * @param  array<string, string>  $extra
     * @return 'sent'|'skipped'|'failed'
     */
    public function send(User $member, string $key, array $extra = []): string
    {
        try {
            $template = EmailTemplate::query()->where('key', $key)->first();

            if ($template === null || ! $template->is_active) {
                return 'skipped';
            }

            $smtp = SmtpSetting::query()->first();

            if ($smtp === null) {
                return 'failed';
            }

            if (! app()->runningUnitTests()) {
                $smtp->applyMailConfig();
                Mail::purge('smtp');
            }

            Mail::to($member->email)->send(new TemplateMessage(
                $member,
                $template,
                $smtp,
                $template->tokens($member, $extra),
            ));

            return 'sent';
        } catch (Throwable $exception) {
            report($exception);

            return 'failed';
        }
    }
}
