<?php

namespace Tests;

use App\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected const SELLER_CONNECT_ID = "acct_1FRtCjDQY5rNzOBX";

    protected function loginAs(Browser $browser, User $user): void
    {
        $token = $user->createToken('MyApp')->accessToken;
        $browser->visit('/');
        $cookie = new Cookie('token', $token);
        $browser->driver->manage()->addCookie($cookie);
    }

    /**
     * Temporal solution for cleaning up session
     */
    protected function setUp(): void
    {
        parent::setUp();
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            // '--headless',
            '--window-size=1200,850',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY, $options
        )
        );
    }

    /**
     * Links a stripe account to a user in order to be able to receive payments.
     *
     * @param $user - The user with an unconfirmed seller account.
     */
    protected function confirmSellerAccount(User $user): void
    {
        $user->stripe_connect_id = self::SELLER_CONNECT_ID;
        $user->save();
    }
}
