<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class RegisterPage extends Page
{
    public function registerWithEmail(Browser $browser, string $email, string $password,
                                          bool $agreesToTerms = true, bool $subscribeNewsletter = false):Browser
    {
        $browser
            ->waitForText('Open your Prev-U store')
            ->type('@email', $email)
            ->type('@password', $password);

        if ($agreesToTerms) {
            $browser->click('@agrees-to-terms');
        }
        if (!$subscribeNewsletter) {
            $browser->click('@add-to-newsletter');
        }
        return $browser->click('@submit');
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/register';
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
            '@agrees-to-terms' => '.agrees-to-terms .v-input--selection-controls__input',
            '@add-to-newsletter' => '.add-to-newsletter .v-input--selection-controls__input',
            '@submit' => 'button[type=submit]',
        ];
    }
}
