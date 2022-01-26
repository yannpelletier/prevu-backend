<?php

namespace Tests\Browser;

use App\Product;
use App\Store;
use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\PurchasesPage;
use Tests\Browser\Pages\StoreCheckoutPage;
use Tests\Browser\Pages\StoreHomePage;
use Tests\Browser\Pages\StoreProductPage;
use Tests\DuskTestCase;

class PurchaseTest extends DuskTestCase
{
    public function test_purchase_valid()
    {
        $seller = factory(User::class)->create();
        $store = factory(Store::class)->create([
            'user_id' => $seller->id
        ]);
        $product = factory(Product::class)->create([
            'user_id' => $seller->id
        ]);
        $buyer = factory(User::class)->make();
        $this->browse(function (Browser $browser) use ($store, $product, $seller, $buyer) {
            $browser
                ->visit(new StoreHomePage($store->slug))
                ->pause(5000)
                ->clickOnProduct($product)
                ->waitForText($product->description)
                ->on(new StoreProductPage($store->slug, $product->slug))
                ->assertSee($product->name)
                ->assertSee($product->description)
                ->assertSee('JPEG file')
                ->buyNow()
                ->waitForText('Checkout')
                ->on(new StoreCheckoutPage($store->slug))
                ->assertSee('This store has not yet been confirmed. You are not allowed to purchase anything on it.')
                ->assertSee('Checkout')
                ->assertDontSee($product->name)
                ->assertDontSee('remove');
            $this->confirmSellerAccount($seller);
            $browser
                ->visit(new StoreHomePage($store->slug))
                ->pause(5000)
                ->clickOnProduct($product)
                ->waitForText($product->description)
                ->on(new StoreProductPage($store->slug, $product->slug))
                ->assertSee($product->name)
                ->assertSee($product->description)
                ->assertSee('JPEG file')
                ->buyNow()
                ->waitForText('Checkout')
                ->on(new StoreCheckoutPage($store->slug))
                ->assertDontSee('This store has not yet been confirmed. You are not allowed to purchase anything on it.')
                ->assertSee('Checkout')
                ->assertSee($product->name)
                ->assertSee('remove')
                ->loginWithEmail($seller->email, 'Secret1')
                ->waitForText('Logged in as ' . $seller->email)
                ->enterCardInfosAndPay()
                ->waitForText('You cannot buy your own products.')
                ->assertSee('You cannot buy your own products.')
                ->clickLogout()
                ->waitForText("I'm new")
                ->registerWithEmail($buyer->email, 'Secret1', true, true)
                ->pause(3000)
                ->waitForText('Logged in as ' . $buyer->email)
                ->pause(1000)
                ->enterCardInfosAndPay()
                ->pause(10000)
                ->on(new PurchasesPage())
                ->assertSee($product->name);
        });
    }
}
