<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        $backendUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'uuid' => $notifiable->uuid, // Use UUID instead of the primary key
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Replace the API URL with the frontend URL
        $frontendUrl = config('app.frontend_url') . '/email/verify';

        // Extract the query string and append it to the frontend URL
        $queryString = parse_url($backendUrl, PHP_URL_QUERY);

        return "{$frontendUrl}?{$queryString}";
    }
}
