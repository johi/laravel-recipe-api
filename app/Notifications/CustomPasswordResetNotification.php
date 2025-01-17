<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Support\Facades\URL;

class CustomPasswordResetNotification extends BaseResetPassword
{
    /**
     * Get the reset password URL.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        // Retrieve the token
        $token = $this->token;

        // Retrieve the user's email
        $email = urlencode($notifiable->getEmailForPasswordReset());

        // Generate the signed URL
        $backendUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(config('auth.passwords.users.expire', 60)),
            [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]
        );

        // Define the frontend URL
        $frontendUrl = config('app.frontend_url') . '/password/validate-reset-token';

        // Extract the token and construct the final URL
        return "{$frontendUrl}/{$token}?email={$email}";
    }
}
