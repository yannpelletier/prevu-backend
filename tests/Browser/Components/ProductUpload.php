<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class ProductUpload extends BaseComponent
{
    public function uploadFile(Browser $browser, string $filePath): Browser
    {
        return $browser->attach('@file-input', storage_path("testing/$filePath"));
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '.product-upload';
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
            '@file-input' => 'input[type=file]',
        ];
    }
}
