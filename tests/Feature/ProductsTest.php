<?php

namespace Tests\Feature;

use App\Jobs\Compilation\CompileImagePreview;
use App\Product;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class ProductsTest extends FeatureTestCase
{
    // TODO: Testing compile preview job execution?

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_upload_jpg_valid()
    {
        $fileJpg = UploadedFile::fake()->image('test-picture.jpg', 10, 10);
        $fileJpeg = UploadedFile::fake()->image('test-picture.jpeg', 4000, 5000);

        $responseJpg = $this->uploadProductOriginal($this->sellers[0], $fileJpg);
        $responseJpeg = $this->uploadProductOriginal($this->sellers[0], $fileJpeg);

        $responseJpg
            ->assertStatus(201)
            ->assertJsonStructure(['id']);

        $responseJpeg
            ->assertStatus(201)
            ->assertJsonStructure(['id']);

        $productJpg = Product::findOrFail($responseJpg->decodeResponseJson()['id']);
        $productJpeg = Product::findOrFail($responseJpg->decodeResponseJson()['id']);

        Storage::disk('s3')->assertExists("originals/{$productJpg->private_file_id}.jpg");
        Storage::disk('s3')->assertExists("originals/{$productJpeg->private_file_id}.jpg");
    }

    public function test_upload_png_valid()
    {
        $file = UploadedFile::fake()->image('test-picture.png');

        $response = $this->uploadProductOriginal($this->sellers[0], $file);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['id']);

        $product = Product::findOrFail($response->decodeResponseJson()['id']);

        Storage::disk('s3')->assertExists("originals/{$product->private_file_id}.png");
    }

    public function test_upload_extension_invalid()
    {
        $invalidFileExe = UploadedFile::fake()->image('test-picture.exe');
        $invalidFileBmp = UploadedFile::fake()->image('test-picture.bmp');
        $invalidFileGiff = UploadedFile::fake()->image('test-picture.giff');

        $responseExe = $this->uploadProductOriginal($this->sellers[0], $invalidFileExe);
        $responseBmp = $this->uploadProductOriginal($this->sellers[0], $invalidFileBmp);
        $responseGiff = $this->uploadProductOriginal($this->sellers[0], $invalidFileGiff);

        $responseExe
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file must be a file of type: png, jpg, jpeg, gif.']]]);

        $responseBmp
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file must be a file of type: png, jpg, jpeg, gif.']]]);

        $responseGiff
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file must be a file of type: png, jpg, jpeg, gif.']]]);
    }

    public function test_upload_image_too_big_dimensions_invalid()
    {
        $fileTooWide = UploadedFile::fake()->image('test-picture.png', 100001, 10);
        $fileTooHigh = UploadedFile::fake()->image('test-picture.png', 10, 100001);

        $responseTooWide = $this->uploadProductOriginal($this->sellers[0], $fileTooWide);
        $responseTooHigh = $this->uploadProductOriginal($this->sellers[0], $fileTooHigh);

        $responseTooWide
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file has invalid image dimensions.']]]);

        $responseTooHigh
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file has invalid image dimensions.']]]);
    }

    public function test_upload_image_too_small_dimensions_invalid()
    {
        $fileTooThin = UploadedFile::fake()->image('test-picture.png', 4, 1000);
        $fileTooShort = UploadedFile::fake()->image('test-picture.png', 1000, 4);

        $responseTooThin = $this->uploadProductOriginal($this->sellers[0], $fileTooThin);
        $responseTooShort = $this->uploadProductOriginal($this->sellers[0], $fileTooShort);

        $responseTooThin
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file has invalid image dimensions.']]]);

        $responseTooShort
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file has invalid image dimensions.']]]);
    }

    public function test_upload_big_file_size_valid()
    {
        $file = UploadedFile::fake()->image('test-picture.png')->size(100000);

        $response = $this->uploadProductOriginal($this->sellers[0], $file);

        $response
            ->assertStatus(201);
    }

    public function test_upload_too_big_file_size_invalid()
    {
        $file = UploadedFile::fake()->image('test-picture.png')->size(100001);

        $response = $this->uploadProductOriginal($this->sellers[0], $file);

        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['file' => ['The file may not be greater than 100000 kilobytes.']]]);
    }

    public function test_update_slug_already_used_same_seller_invalid()
    {
        $file1 = UploadedFile::fake()->image('test-picture1.jpg');
        $file2 = UploadedFile::fake()->image('test-picture2.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);
        $response2 = $this->uploadProductOriginal($this->sellers[0], $file2);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);
        $product2 = Product::findOrFail($response2->decodeResponseJson()['id']);

        $responseUpdateSlug = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product2->id}",
            ['slug' => $product1->slug]);

        $responseUpdateSlug
            ->assertStatus(422)
            ->assertJson(['errors' => ['slug' => ['The slug has already been taken.']]]);
    }

    public function test_upload_slug_already_used_same_seller_invalid()
    {
        $file1 = UploadedFile::fake()->image('test PictUre.jpg');
        $file2 = UploadedFile::fake()->image('test-picture.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);
        $response2 = $this->uploadProductOriginal($this->sellers[0], $file2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);
        $product2 = Product::findOrFail($response2->decodeResponseJson()['id']);

        $this->assertEquals($product1->slug, 'test-picture');
        $this->assertRegExp('/test[-]picture/', $product2->slug);
        $this->assertNotEquals('test-picture', $product2->slug);
    }

    public function test_update_slug_already_used_different_seller_valid()
    {
        $file1 = UploadedFile::fake()->image('test-picture1.jpg');
        $file2 = UploadedFile::fake()->image('test-picture2.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);
        $response2 = $this->uploadProductOriginal($this->sellers[1], $file2);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);
        $product2 = Product::findOrFail($response2->decodeResponseJson()['id']);

        $responseUpdateSlug = $this->actingAs($this->sellers[1])->json('PATCH', "/api/products/{$product2->id}",
            ['slug' => $product1->slug]);

        $responseUpdateSlug
            ->assertStatus(200)
            ->assertJson(['slug' => $product1->slug]);
    }

    public function test_upload_slug_already_used_different_seller_valid()
    {
        $file1 = UploadedFile::fake()->image('TEST piCtUre.jpg');
        $file2 = UploadedFile::fake()->image('test-picture.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);
        $response2 = $this->uploadProductOriginal($this->sellers[1], $file2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);
        $product2 = Product::findOrFail($response2->decodeResponseJson()['id']);

        $this->assertEquals('test-picture', $product1->slug);
        $this->assertEquals('test-picture', $product2->slug);
    }

    public function test_update_price_valid()
    {
        $file1 = UploadedFile::fake()->image('test-picture1.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);

        // Minimum: 2 $
        $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product1->id}",
            ['price' => 200])
            ->assertStatus(200);

        // Maximum: 10 000 $
        $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product1->id}",
            ['price' => 1000000])
            ->assertStatus(200);

    }

    public function test_update_price_too_low_invalid()
    {

        $file1 = UploadedFile::fake()->image('test-picture1.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);

        // 1.99 $
        $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product1->id}",
            ['price' => 199])
            ->assertStatus(422)
            ->assertJson(['errors' => ['price' => ['The price must be at least $ 2 USD.']]]);
    }

    public function test_update_price_too_high_invalid()
    {
        $file1 = UploadedFile::fake()->image('test-picture1.jpg');

        $response1 = $this->uploadProductOriginal($this->sellers[0], $file1);

        $product1 = Product::findOrFail($response1->decodeResponseJson()['id']);

        // 10 000.01 $
        $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product1->id}",
            ['price' => 1000001])
            ->assertStatus(422)
            ->assertJson(['errors' => ['price' => ['The price may not be greater than $ 10000 USD.']]]);
    }

    public function test_update_filters_valid()
    {
        $file = UploadedFile::fake()->image('test-picture.jpg');

        $responseUploadOriginal = $this->uploadProductOriginal($this->sellers[0], $file);

        $product = Product::findOrFail($responseUploadOriginal->decodeResponseJson()['id']);

        $responseUpdateFilters = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['pixel_size' => 0, 'blur' => 4, 'watermark' => null]]);

        $responseUpdateFilters
            ->assertStatus(200)
            ->assertJson(['filters' => [
                'blur' => 4,
                'pixel_size' => 0,
                'watermark' => null
            ]]);

        $responseUpdateFilters2 = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['pixel_size' => 2, 'blur' => 4, 'watermark' => null]]);

        $responseUpdateFilters2
            ->assertStatus(200)
            ->assertJson(['filters' => [
                'blur' => 4,
                'pixel_size' => 2,
                'watermark' => null
            ]]);

        // TODO: Add watermark tests
    }

    public function test_update_filters_names_invalid()
    {
        $file = UploadedFile::fake()->image('test-picture.jpg');

        $responseUploadOriginal = $this->uploadProductOriginal($this->sellers[0], $file);

        $product = Product::findOrFail($responseUploadOriginal->decodeResponseJson()['id']);

        $responseUpdateFilters = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['blur_invalid' => 4]]);

        $responseUpdateFilters
            ->assertStatus(422)
            ->assertJsonMissing(['filters' => ['blur_invalid' => 4]]);
    }

    public function test_update_filters_values_invalid()
    {
        $file = UploadedFile::fake()->image('test-picture.jpg');
        $responseUploadOriginal = $this->uploadProductOriginal($this->sellers[0], $file);
        $product = Product::findOrFail($responseUploadOriginal->decodeResponseJson()['id']);

        $responseUpdateFilters1 = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['blur' => 11]]);
        $responseUpdateFilters1
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['filters' => ['blur']]]);

        $responseUpdateFilters2 = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['pixel_size' => -1]]);
        $responseUpdateFilters2
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['filters' => ['pixel_size']]]);

        $responseUpdateFilters3 = $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['watermark' => 479589353]]);
        $responseUpdateFilters3
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['filters' => ['watermark']]]);
    }

    public function test_preview_compilation_job_pushed_valid()
    {
        // TODO: Test watermark
        $file1 = UploadedFile::fake()->image('test-picture1.jpg', 5, 5);
        $file2 = UploadedFile::fake()->image('test-picture2.jpg', 2000, 6000);

        $responseUploadOriginal1 = $this->uploadProductOriginal($this->sellers[0], $file1);
        $product1 = Product::findOrFail($responseUploadOriginal1->decodeResponseJson()['id']);
        Queue::assertNotPushed(CompileImagePreview::class, function ($job) use ($product1) {
            return $job->compiledModel->id === $product1->id;
        });
        $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product1->id}",
            ['filters' => ['blur' => 10]]);
        Queue::assertPushed(CompileImagePreview::class, function ($job) use ($product1) {
            return $job->compiledModel->id === $product1->id;
        });

        $responseUploadOriginal2 = $this->uploadProductOriginal($this->sellers[0], $file2);
        $product2 = Product::findOrFail($responseUploadOriginal2->decodeResponseJson()['id']);
        Queue::assertNotPushed(CompileImagePreview::class, function ($job) use ($product2) {
            return $job->compiledModel->id === $product2->id;
        });
        $this->actingAs($this->sellers[0])->json('PATCH', "/api/products/{$product2->id}",
            ['filters' => ['pixel_size' => 1.1, 'blur' => 0]]);

        Queue::assertPushed(CompileImagePreview::class, function ($job) use ($product2) {
            return $job->compiledModel->id === $product2->id;
        });
    }

    public function test_delete_valid()
    {
        $file = UploadedFile::fake()->image('test-picture.jpg');
        $responseUploadOriginal = $this->uploadProductOriginal($this->sellers[0], $file);
        $product = Product::findOrFail($responseUploadOriginal->decodeResponseJson()['id']);

        Storage::assertExists("originals/{$product->private_file_id}.{$product->extension}");
        $response = $this->actingAs($this->sellers[0])->json('DELETE', "/api/products/{$product->id}");

        $response->assertStatus(200);
        Storage::assertMissing("originals/{$product->private_file_id}.{$product->extension}");
    }

    public function test_update_filters_unauthorized_user_invalid()
    {
        $file = UploadedFile::fake()->image('test-picture.jpg');
        $responseUploadOriginal = $this->uploadProductOriginal($this->sellers[0], $file);
        $product = Product::findOrFail($responseUploadOriginal->decodeResponseJson()['id']);
        $responseUpdateFilters = $this->actingAs($this->unauthorizedUsers[0])->json('PATCH', "/api/products/{$product->id}",
            ['filters' => ['blur' => 4]]);

        $responseUpdateFilters
            ->assertStatus(403)
            ->assertJson(['message' => trans('exceptions.access_denied')]);
    }

    // TODO: Test access to product's original
    /*
    public function test_access_original_valid_seller()
    {
        $file = UploadedFile::fake()->image('test-picture.png');

        $response = $this->actingAs($this->sellers[0])->json('POST', '/api/products', [
            'file' => $file
        ]);

        $originalLink = $response->decodeResponseJson()['original'];
        dump($originalLink);
        $productOriginalResponse = $this->actingAs($this->sellers[0])->get($originalLink);

        dd($productOriginalResponse);
        $productOriginalResponse
            ->assertStatus(200)
            ->assertHeader('content-type', 'image/png');
    }
    */

    private function uploadProductOriginal(User $user, UploadedFile $file)
    {
        return $this->actingAs($user)->json('POST', '/api/products', [
            'file' => $file
        ]);
    }

}
