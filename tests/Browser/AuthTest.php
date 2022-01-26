<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\RegisterPage;
use Tests\DuskTestCase;

class AuthTest extends DuskTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_register_valid()
    {
        $user = factory(User::class)->make();
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit(new RegisterPage)
                ->registerWithEmail($user->email, 'Secret900')
                ->pause('6000')
                ->waitForText('Welcome to Prev-U!')
                ->assertSee('Welcome to Prev-U!');
        });
        User::where('email', $user->email)->delete();
    }

    public function test_register_does_not_accept_terms_invalid()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new RegisterPage)
                ->registerWithEmail('test.2@prev-u.com', 'Secret900', false)
                ->waitForText('You must accept PREV-U\'s terms of service.')
                ->assertSee('You must accept PREV-U\'s terms of service.');
        });
    }

    public function test_register_no_password_invalid()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new RegisterPage)
                ->registerWithEmail('test.3@prev-u.com', '')
                ->waitForText('The password field is required.')
                ->assertSee('The password field is required.');
        });
    }

    public function test_register_password_short_invalid()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new RegisterPage)
                ->registerWithEmail('test.3@prev-u.com', 'a')
                ->waitForText('The password must be at least 7 characters.')
                ->assertSee('The password must be at least 7 characters.');
        });
    }

    public function test_login_valid()
    {
        $user = factory(User::class)->create();
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit(new LoginPage)
                ->loginWithEmail($user->email, 'Secret1')
                ->waitForText('Dashboard')
                ->assertSee('Dashboard');
        });
    }

    public function test_login_wrong_password_invalid()
    {
        $user = factory(User::class)->create();
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit(new LoginPage)
                ->loginWithEmail($user->email, 'invalid_pass')
                ->waitForText('Invalid email or password')
                ->assertSee('Invalid email or password');
        });
    }

    public function test_login_non_existent_user_invalid()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new LoginPage)
                ->loginWithEmail('non-existent-user@email.com', 'pass')
                ->waitForText('Invalid email or password')
                ->assertSee('Invalid email or password');
        });
    }
}
