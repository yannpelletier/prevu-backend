<?php

namespace Tests\Browser;

use App\Product;
use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ProductEdit;
use Tests\Browser\Components\ProductPreviewEdit;
use Tests\Browser\Components\ProductUpload;
use Tests\Browser\Pages\ProductsPage;
use Tests\DuskTestCase;

class ProductsTest extends DuskTestCase
{
    public function test_add_one_small_product_invalid_and_valid()
    {
        $user = factory(User::class)->create();
        $product = factory(Product::class)->make();
        $this->browse(function (Browser $browser) use ($product, $user) {
            $this->loginAs($browser, $user);
            $browser
                ->visit(new ProductsPage)
                ->waitFor('@add-new-products')
                ->openAddNewProductDialog()
                ->waitFor('.product-upload')
                ->within(new ProductUpload, function ($browser) {
                    $browser->uploadFile('images/max-1000x1000/Team_MEAT.jpg');
                })
                ->waitFor('.product-edit', 20000)
                ->within(new ProductEdit, function ($browser) use ($product) {
                    $browser
                        ->setSlug('a')
                        ->setName($product->name)
                        ->save()
                        ->waitForText('The slug must be at least 3 characters.')
                        ->assertSee('The slug must be at least 3 characters.');
                    $browser
                        ->setSlug($product->slug)
                        ->setName('')
                        ->save()
                        ->waitForText('The name field is required.')
                        ->assertSee('The name field is required.');
                    $browser
                        ->setName($product->name)
                        ->setPrice('$ 0.01')
                        ->save()
                        ->waitForText('The price must be at least $ 2 USD.')
                        ->assertSee('The price must be at least $ 2 USD.');
                    $browser
                        ->setPrice((string) $product->price)
                        ->setDescription("This is my super cool pixel picture\nFeel free to share!")
                        ->save();
                })
                ->waitFor('.product-preview-edit')
                ->within(new ProductPreviewEdit, function ($browser) {
                    $browser
                        ->assertSee('Blur: 0%')
                        ->assertSee('Pixel Size: 0%')
                        ->setBlur(5)
                        ->waitForText('Blur: 5%')
                        ->assertSee('Blur: 5%')
                        ->setPixelSize(0.6)
                        ->waitForText('Pixel Size: 0.6%')
                        ->assertSee('Pixel Size: 0.6%')
                        ->save();
                })
                ->waitForText('Generating preview...')
                ->assertSee('Generating preview...')
                ->waitForText('Create a store to sell your product', 10000)
                ->assertSee('Create a store to sell your product')
                ->pause(1000)
                ->clickLink('Create a store')
                ->waitForLocation('/store-editor')
                ->assertPathIs('/store-editor')
                ->pause(1000)
            ;
        });
    }

    public function test_add_one_big_product_valid()
    {
        $user = factory(User::class)->create();
        $product = factory(Product::class)->make();
        $this->browse(function (Browser $browser) use ($product, $user) {
            $this->loginAs($browser, $user);
            $browser
                ->visit(new ProductsPage)
                ->waitFor('@add-new-products')
                ->openAddNewProductDialog()
                ->waitFor('.product-upload')
                ->within(new ProductUpload, function ($browser) {
                    $browser->uploadFile('images/max-10000x10000/DSC07852.JPG');
                })
                ->waitFor('.product-edit', 20000)
                ->within(new ProductEdit, function ($browser) use ($product) {
                    $browser
                        ->setSlug($product->slug)
                        ->setName($product->name)
                        ->save();
                })
                ->waitFor('.product-preview-edit')
                ->within(new ProductPreviewEdit, function ($browser) {
                    $browser
                        ->assertSee('Blur: 0%')
                        ->assertSee('Pixel Size: 0%')
                        ->setBlur(5)
                        ->waitForText('Blur: 5%')
                        ->assertSee('Blur: 5%')
                        ->setPixelSize(0.6)
                        ->waitForText('Pixel Size: 0.6%')
                        ->assertSee('Pixel Size: 0.6%')
                        ->save();
                })
                ->waitForText('Generating preview...')
                ->assertSee('Generating preview...')
                ->waitForText('Create a store to sell your product', 20000)
                ->assertSee('Create a store to sell your product');
        });
    }
}
