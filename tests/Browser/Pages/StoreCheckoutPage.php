<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class StoreCheckoutPage extends Page
{
    private $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function registerWithEmail(Browser $browser, string $email, string $password,
                                      bool $agreesToTerms = true, bool $subscribeNewsletter = false): Browser
    {
        $browser
            ->click('@register-radio-btn')
            ->type('@register-email', $email)
            ->type('@register-password', $password);

        if ($agreesToTerms) {
            $browser->click('@agrees-to-terms');
        }
        if (!$subscribeNewsletter) {
            $browser->click('@add-to-newsletter');
        }
        return $browser->click('@continue-btn');
    }

    public function loginWithEmail(Browser $browser, string $email, string $password): Browser
    {
        return $browser
            ->click('@login-radio-btn')
            ->type('@login-email', $email)
            ->type('@login-password', $password)
            ->click('@continue-btn');
    }

    public function enterCardInfosAndPay(Browser $browser,
                                         string $cardNumber = '4242 4242 4242 4242',
                                         string $expiry = '12/30',
                                         string $cvc = '000',
                                         string $postalCode = '10000'): Browser
    {
        return $browser
            ->waitFor('@stripe-card-iframe')
            ->withinFrame('@stripe-card-iframe', function ($browser)
            use ($cardNumber, $expiry, $cvc, $postalCode) {
                $browser
                    ->waitFor('@stripe-card-number', 10000)
                    ->keys('@stripe-card-number', $cardNumber)
                    ->keys('@stripe-card-expiry', $expiry)
                    ->keys('@stripe-card-cvc', $cvc)
                    ->keys('@stripe-card-postal', $postalCode);
            })
            ->click('@buy-now-btn');
    }

    public function clickLogout(Browser $browser): Browser
    {
        return $browser->click('@logout-btn');
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/store/' . $this->slug . '/checkout';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param Browser $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
        $browser->assertQueryStringMissing('product');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@login-radio-btn' => '.login-radio-btn',
            '@register-radio-btn' => '.register-radio-btn',
            '@register-email' => '.register-form input[type=email]',
            '@register-password' => '.register-form input[type=password]',
            '@login-email' => '.login-form input[type=email]',
            '@login-password' => '.login-form input[type=password]',
            '@agrees-to-terms' => '.agrees-to-terms .v-input--selection-controls__ripple',
            '@add-to-newsletter' => '.add-to-newsletter .v-input--selection-controls__ripple',
            '@continue-btn' => '.continue-btn',
            '@stripe-card-iframe' => '.payment-form .stripe-card iframe',
            '@stripe-card-number' => 'input[name=cardnumber]',
            '@stripe-card-expiry' => 'input[name=exp-date]',
            '@stripe-card-cvc' => 'input[name=cvc]',
            '@stripe-card-postal' => 'input[name=postal]',
            '@buy-now-btn' => '.payment-form .buy-now-btn',
            '@logout-btn' => '.payment-form .logout-btn'
        ];
    }
}
