<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class ProductsPage extends Page
{
    public function openAddNewProductDialog(Browser $browser): Browser
    {
        return $browser->click('@add-new-products');
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/products';
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
            //
        ];
    }
}
