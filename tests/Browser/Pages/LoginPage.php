<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class LoginPage extends Page
{
    public function loginWithEmail(Browser $browser, string $email, string $password):Browser
    {
        return $browser
            ->waitForText('Login')
            ->type('@email', $email)
            ->type('@password', $password)
            ->click('@submit');
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/login';
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
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@email' => 'input[type=email]',
            '@password' => 'input[type=password]',
            '@submit' => 'button[type=submit]',
        ];
    }
}
