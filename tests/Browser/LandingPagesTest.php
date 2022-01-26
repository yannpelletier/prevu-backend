<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;

class LandingPagesTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                ->waitForText('Build your store in under 5 minutes')
                ->assertSee('Build your store in under 5 minutes');
        });
    }
}
