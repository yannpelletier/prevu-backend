<?php

namespace App\Observers;

use App\Product;
use App\Purchase;

class ProductObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param \App\Product $product
     * @return void
     */
    public function created(Product $product)
    {
        $product->createMainFileThumbnail('private_thumbnails');
        $product->filters = [];
        $product->save();
    }

    /**
     * Handle the product "updated" event.
     *
     * @param \App\Product $product
     * @return void
     */
    public function updated(Product $product)
    {
        $product->createPublicThumbnail();
    }


    /**
     * Handle the product "deleted" event.
     *
     * @param \App\Product $product
     * @return void
     */
    public function deleted(Product $product)
    {
        $product->deleteDirectoryFile('previews');
        $product->deleteDirectoryFile('public_thumbnails');

        $purchase = Purchase::where('private_file_id', '=', $product->private_file_id)->first();
        if ($purchase === null) {
            $product->deleteMainFile();
            $product->deleteDirectoryFile('private_thumbnails');
        }
    }
}
