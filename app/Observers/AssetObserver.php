<?php

namespace App\Observers;

use App\Asset;

class AssetObserver
{
    /**
     * Handle the product "deleted" event.
     *
     * @param Asset $asset
     * @return void
     */
    public function deleted(Asset $asset)
    {
        $asset->deleteMainFile();
    }
}
