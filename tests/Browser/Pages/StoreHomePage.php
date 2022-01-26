<?php

namespace Tests\Browser\Pages;

use App\Product;
use Laravel\Dusk\Browser;

class StoreHomePage extends Page
{
    private $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function clickOnProduct(Browser $browser, Product $product): Browser
    {
        return $browser->click('.product-' . $product->slug);
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return "/store/" . $this->slug;
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
            '@element' => '#selector',
        ];
    }
}
