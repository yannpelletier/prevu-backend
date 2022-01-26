<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\UserPasswordRecoveryRequest;
use App\Http\Requests\UserRequest\UserResetPasswordRequest;
use App\Notifications\PasswordResetLinkNotification;
use App\User;
use App\Services\StripeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Newsletter\NewsletterFacade;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\UserRequest\UserRegisterRequest;

class AuthController extends Controller
{
    const FAIL_STATUS = 401;
    const COOKIE_LIFETIME_MINUTES =  60 * 24 * 365; // lifetime of one year

    /**
     * Login api
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $user = User::whereEmail($request->get('email'))->first();

        if ($user && Hash::check($request->get('password'), $user->password)) {
            $token = $this->createTokenCookie($user);
            return response()->json(['token' => $token]);
        } else {
            return response()->json(['message' => trans('auth.failed')], self::FAIL_STATUS);
        }
    }

    /**
     * Register api
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(UserRegisterRequest $request)
    {
        $validated = $request->validated();
        $user = User::create($validated);
        if (isset($validated['add_to_newsletter']) && $validated['add_to_newsletter'] === true && env('APP_ENV') === 'prod') {
            NewsletterFacade::subscribe($user->email);
        }
        $token = $this->createTokenCookie($user);
        return response()->json(['token' => $token, 'stripe_login_link' => StripeHelper::getConnectionLink($user)]);
    }

    /**
     * Reset password email api
     */
    public function sendPasswordResetLink(UserPasswordRecoveryRequest $request)
    {
        $validated = $request->validated();
        $user = User::whereEmail($validated['email'])->first();
        if($user->password) {
            $user->generateNewPasswordResetToken();
            $user->notify(new PasswordResetLinkNotification());
        } else {
            return response()->json(['message' => trans('auth.is_passwordless')], self::FAIL_STATUS);
        }
    }

    /**
     * Reset password email api
     */
    public function resetPassword(UserResetPasswordRequest $request)
    {
        $validated = $request->validated();
        $validated['password_reset_token'] = null;
        $user = User::wherePasswordResetToken($validated['token'])->first();
        $user->fill($validated);
        $user->save();

        $token = $this->createTokenCookie($user);
        return response()->json(['token' => $token]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::user()->token()->revoke();
    }

    public function createGoogleSignInLink(Request $request)
    {
        $signInLink = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();
        return response()->json(['link' => $signInLink]);
    }

    public function completeGoogleSignIn(Request $request)
    {
        $socialiteUser = Socialite::driver('google')
            ->stateless()
            ->user();
        Log::info(json_encode($socialiteUser));
        $email = $socialiteUser->getEmail();
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email,
            ]);
        }
        $token = $this->createTokenCookie($user);
        return response()->json(['token' => $token]);
    }

    private function createTokenCookie(User $user)
    {
        $token = $user->createToken('MyApp')->accessToken;
        Cookie::queue(Cookie::make('token', $token, self::COOKIE_LIFETIME_MINUTES));
        return $token;
    }
}
