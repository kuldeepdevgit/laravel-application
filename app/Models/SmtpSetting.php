<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    protected $fillable = [
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting !== null) {
            return $setting;
        }

        return static::query()->create(self::defaults());
    }

    /**
     * @return array{host: string, port: int, encryption: string, username: string, password: string, from_address: string, from_name: string}
     */
    public static function defaults(): array
    {
        return [
            'host' => 'smtp-relay.brevo.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'b5018f001@smtp-brevo.com',
            'password' => '',
            'from_address' => 'muskanflutterdev@gmail.com',
            'from_name' => 'TMS',
        ];
    }

    public function applyMailConfig(): void
    {
        $encryption = (string) $this->encryption;

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => (int) $this->port,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->password,
            'mail.mailers.smtp.timeout' => 15,
            'mail.mailers.smtp.auto_tls' => $encryption !== 'none',
            'mail.mailers.smtp.require_tls' => $encryption === 'tls',
            'mail.from.address' => $this->from_address,
            'mail.from.name' => $this->from_name,
        ]);
    }
}
