<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        // Prepare the UUID and hash
        $uuid = $notifiable->uuid;
        $hash = sha1($notifiable->getEmailForVerification());

        // Set expiration time
        $expires = now()->addMinutes(config('auth.verification.expire', 60));

        // Generate a signed URL
        $url = URL::temporarySignedRoute(
            'verification.verify',
            $expires,
            [
                'uuid' => $uuid,
                'hash' => $hash,
            ]
        );

        // Combine backend URL with frontend URL
        $frontendUrl = config('app.frontend_url') . '/email/verify';

        // Extract the query string and append to frontend URL
        $queryString = parse_url($url, PHP_URL_QUERY);

        // Return final URL
        return "{$frontendUrl}/{$uuid}/{$hash}?{$queryString}";
    }
}
