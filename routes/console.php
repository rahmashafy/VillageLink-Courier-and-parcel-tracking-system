<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email}', function (string $email) {
    if (config('mail.default') !== 'smtp') {
        $this->warn('MAIL_MAILER is not smtp. Real inbox delivery needs SMTP settings in .env.');
    }

    if (blank(config('mail.from.address')) || config('mail.from.address') === 'hello@example.com') {
        $this->warn('MAIL_FROM_ADDRESS is still the default. Set it to your Gmail address before professor testing.');
    }

    try {
        Mail::raw(
            "Village Link email test passed.\n\nIf you received this, SMTP is working for password reset emails too.\nSent at: ".now()->format('Y-m-d H:i:s'),
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Village Link SMTP Test');
            }
        );

        $this->info("Test email sent to {$email}.");

        return 0;
    } catch (Throwable $exception) {
        $this->error('Email send failed: '.$exception->getMessage());

        return 1;
    }
})->purpose('Send a real SMTP test email to verify inbox delivery');
