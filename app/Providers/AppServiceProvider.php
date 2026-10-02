<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $baseUrl = rtrim((string) config('services.password_reset.url', config('app.url')), '/');
            $path = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false);

            $url = $baseUrl.$path;

            $broker = config('auth.defaults.passwords');
            $expireMinutes = config("auth.passwords.{$broker}.expire", 60);

            return (new MailMessage)
                ->subject('Reset your Village Link password')
                ->greeting('Hello '.$notifiable->name.',')
                ->line('We received a request to reset the password for your Village Link account.')
                ->action('Reset Password', $url)
                ->line("This secure password reset link will expire in {$expireMinutes} minutes.")
                ->line('If you did not request this reset, you can safely ignore this email.');
        });
    }
}
