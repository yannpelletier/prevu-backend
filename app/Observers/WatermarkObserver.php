<?php

namespace App\Observers;

use App\Watermark;

class WatermarkObserver
{
    /**
     * Handle the product "deleted" event.
     *
     * @param Watermark $watermark
     * @return void
     */
    public function deleted(Watermark $watermark)
    {
        $watermark->deleteMainFile();
    }
}
