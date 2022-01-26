<?php


namespace App\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;

class StripeHelper
{
    public static function getConnectionLink($user): string
    {
        if ($user->stripe_connect_id == '') {
            return self::getRegisterLink($user);
        } else {
            return self::createLoginLink($user);
        }
    }

    private static function getRegisterLink($user): string
    {
        $clientId = config('services.stripe.client_id');
        $email = $user->email;
        $redirectUri = config('app.frontend_url') . '/payouts-setup-complete';
        $state = csrf_token();
        return "https://connect.stripe.com/express/oauth/authorize?client_id=$clientId&stripe_user[email]=$email&redirect_uri=$redirectUri&state=$state";
    }

    public static function addStripeAccount($user, string $stripeAuthorizationCode): bool
    {
        /*
        if ($csrfToken != csrf_token()) {
            throw new HttpException(500, 'Invalid CSRF token');
        }*/
        $httpClient = new \GuzzleHttp\Client(['base_uri' => 'https://connect.stripe.com/']);
        try {

            $response = json_decode($httpClient->request('POST', 'https://connect.stripe.com/oauth/token', [
                'auth' => [
                    config('services.stripe.secret_key'),
                    ''
                ],
                'json' => [
                    'client_secret' => config('services.stripe.secret_key'),
                    'code' => $stripeAuthorizationCode,
                    'grant_type' => 'authorization_code'
                ]
            ])->getBody(), true);
            $success = isset($response['stripe_user_id']);
            if ($success) {
                $user->stripe_connect_id = $response['stripe_user_id'];
                $user->save();
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
            throw new HttpException(500, $exception->getMessage());
        }
        return $success;
    }

    public static function createLoginLink($user): string
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));
        try {
            return \Stripe\Account::createLoginLink($user->stripe_connect_id, [
                'redirect_url' => config('app.frontend_url') . '/dashboard',

            ])->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new HttpException(400, $e->getError());
        }
    }

}
