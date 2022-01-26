<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class ProductEdit extends BaseComponent
{
    public function setSlug(Browser $browser, string $slug): Browser
    {
        return $browser
            ->keys($this->elements()['@product-slug-input'], ...array_fill(0, 100, '{backspace}'))
            ->type('@product-slug-input', $slug);
    }

    public function setName(Browser $browser, string $name): Browser
    {
        return $browser
            ->keys($this->elements()['@product-name-input'], ...array_fill(0, 100, '{backspace}'))
            ->type('@product-name-input', $name);
    }

    public function setPrice(Browser $browser, string $price): Browser
    {
        return $browser
            ->keys($this->elements()['@product-price-input'], ...array_fill(0, 100, '{backspace}'))
            ->keys($this->elements()['@product-price-input'], $price);
    }

    public function setDescription(Browser $browser, string $description): Browser
    {
        return $browser
            ->keys($this->elements()['@product-description-input'], ...array_fill(0, 100, '{backspace}'))
            ->type('@product-description-input', $description);
    }


    public function save(Browser $browser): Browser
    {
        return $browser->click('@save-btn');
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '.product-edit';
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param Browser $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@product-slug-input' => '.product-slug-input input[type=text]',
            '@product-name-input' => '.product-name-input input[type=text]',
            '@product-price-input' => '.product-price-input input[type=text]',
            '@product-description-input' => '.product-description-input textarea',
            '@save-btn' => '.save'
        ];
    }
}
