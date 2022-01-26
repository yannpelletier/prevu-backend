<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class StoreProductPage extends Page
{
    private $storeSlug;
    private $productSlug;

    public function __construct(string $storeSlug, string $productStore)
    {
        $this->storeSlug = $storeSlug;
        $this->productSlug = $productStore;
    }

    public function buyNow(Browser $browser): Browser
    {
        return $browser->click('@buy-now-btn');
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/store/' . $this->storeSlug;
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
        $browser->assertQueryStringHas('product', $this->productSlug);
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@buy-now-btn' => '.buy-now-btn',
        ];
    }
}
