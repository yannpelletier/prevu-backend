<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class ProductPreviewEdit extends BaseComponent
{
    public function setBlur(Browser $browser, int $blur): Browser
    {
        return $browser->dragRight('@filter-slider-blur', $blur * 45);
    }

    public function setPixelSize(Browser $browser, float $pixelSize): Browser
    {
        return $browser->dragRight('@filter-slider-pixel-size', (int)($pixelSize * 225));
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
        return '.product-preview-edit';
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
            '@filter-slider-blur' => '.filter-slider-blur .v-slider__thumb.primary',
            '@filter-slider-pixel-size' => '.filter-slider-pixelSize .v-slider__thumb.primary',
            '@save-btn' => '.save-btn'
        ];
    }
}
