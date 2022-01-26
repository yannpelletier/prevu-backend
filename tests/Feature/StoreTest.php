<?php

namespace Tests\Feature;

use App\Store;
use App\User;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;

class StoreTest extends FeatureTestCase
{
    // TODO: return error code 422 when store updated with non-existent asset id.

    protected function setUp(): void
    {
        parent::setUp();

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (Store::where('user_id', $this->sellers[0]->id)->get())
            Store::where('user_id', $this->sellers[0]->id)->delete();
        if (Store::where('user_id', $this->sellers[1]->id)->get())
            Store::where('user_id', $this->sellers[1]->id)->delete();
        if (Store::where('user_id', $this->unauthorizedUsers[0]->id)->get())
            Store::where('user_id', $this->unauthorizedUsers[0]->id)->delete();
    }

    public function test_update_nonexistent_store_invalid()
    {
        $updateResponse = $this->actingAs($this->sellers[0])->json('PATCH', '/api/stores/abcdef', [
            'slug' => 'new_test_slug'
        ]);

        $updateResponse
            ->assertStatus(404);

        $updateResponse = $this->actingAs($this->sellers[0])->json('PATCH', '/api/stores/abcdef', [
            'slug' => 'new_test_slug'
        ]);

        $updateResponse
            ->assertStatus(404);
    }

    public function test_update_unauthorized_user_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');

        $updateResponse = $this->updateStoreSlug($store, $this->unauthorizedUsers[0], 'new_test_slug');

        $updateResponse
            ->assertStatus(403);
    }

    public function test_update_slug_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');

        $updateResponse = $this->updateStoreSlug($store, $this->sellers[0], 'new_test_slug');

        $updateResponse
            ->assertStatus(200)
            ->assertJsonFragment(['slug' => 'new_test_slug']);
    }

    public function test_update_slug_already_taken_invalid()
    {
        $this->getNewStore($this->sellers[0], 'some_slug_1232');
        $store_1 = $this->getNewStore($this->sellers[1], 'valid_slug');
        $updateResponse = $this->updateStoreSlug($store_1, $this->sellers[1], 'some_slug_1232');

        $updateResponse
            ->assertStatus(422);
    }

    public function test_update_slug_invalid_characters_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');

        $invalidSlugs = ['aaaaa*', 'space space', 'dot.dot', 'ééééé', 'aaa###a', 'a"quote', '(parentheses)', 'slash/slash'];
        foreach ($invalidSlugs as $invalidSlug) {
            $this->updateStoreSlug($store, $this->sellers[0], $invalidSlug)
                ->assertStatus(422);
        }
    }

    public function test_update_slug_too_short_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');

        $this->updateStoreSlug($store, $this->sellers[0], 'aa')
            ->assertStatus(422);
    }

    public function test_update_slug_too_long_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');

        $this->updateStoreSlug($store, $this->sellers[0], 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')
            ->assertStatus(422);
    }

    public function test_update_store_title_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $this->updateRootSections($store, $this->sellers[0],
            [
                'general' =>
                    [
                        'variant' => 'default',
                        'parameters' =>
                            ['name' => 'My 1st cool site!!']
                    ]
            ]
        )->assertStatus(200);
    }

    public function test_update_store_title_too_short_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $this->updateRootSections($store, $this->sellers[0],
            [
                'general' =>
                    [
                        'variant' => 'default',
                        'parameters' =>
                            ['name' => 'a']
                    ]
            ]
        )->assertStatus(422);
    }

    public function test_update_store_title_too_long_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $this->updateRootSections($store, $this->sellers[0],
            [
                'general' =>
                    [
                        'variant' => 'default',
                        'parameters' =>
                            ['name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']
                    ]
            ]
        )->assertStatus(422);
    }

    public function test_update_colors_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'general' =>
                [
                    'variant' => 'default',
                    'parameters' =>
                        [
                            'primary_color' => 'rgb(23,0,255)',
                            'secondary_color' => 'rgb(23, 4, 255)',
                        ]
                ]
        ]);

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'banner' =>
                [
                    'variant' => 'default',
                    'parameters' =>
                        [
                            'overlay_color' => 'rgba(0, 1,145, 0.4)'
                        ]
                ]
        ]);
        $r->assertStatus(200);
    }

    public function test_update_banner_variant_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'banner' => [
                'variant' => 'fullscreen',
                'parameters' => []
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' => [
                'banner' => [
                    'variant' => 'fullscreen'
                ]
            ]
        ]);
    }

    public function test_update_banner_variant_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'banner' => [
                'variant' => 'invalid',
                'parameters' => []
            ]
        ]);
        print_r('fsd');
        $r->assertStatus(422);
    }

    public function test_update_colors_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'general' =>
                [
                    'variant' => 'default',
                    'parameters' =>
                        [
                            'primary_color' => 'rgb(23,0)'
                        ]
                ]
        ]);
        $r->assertStatus(422);
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'general' =>
                [
                    'variant' => 'default',
                    'parameters' =>
                        [
                            'secondary_color' => 'red'
                        ]
                ]
        ]);
        $r->assertStatus(422);
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'general' =>
                [
                    'variant' => 'default',
                    'parameters' =>
                        [
                            'secondary_color' => '#FFFFFF'
                        ]
                ]
        ]);
        $r->assertStatus(422);

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'banner' =>
                [
                    'variant' => 'default',
                    'parameters' =>
                        [
                            'overlay_color' => 'rgba(0, 1,145)'
                        ]
                ]
        ]);
        $r->assertStatus(422);

    }

    public function test_update_assets_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $file = UploadedFile::fake()->image('test-picture.jpg');

        $r = $this->actingAs($this->sellers[0])->json('POST', '/api/assets', [
            'file' => $file
        ]);
        $assetLink = "https://prev-u-storage.s3.us-east-2.amazonaws.com/user_assets/SE1VgqcC6suiDSAerGhQJuyLq6k1FB69zYfVxz0s.jpeg";

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'general' => [
                'variant' => 'default',
                'parameters' => [
                    'logo' => $assetLink
                ]
            ]
        ]);
        $r->assertJson(
            [
                'root_sections' =>
                    [
                        'general' => [
                            'variant' => 'default',
                            'parameters' => [
                                'logo' => $assetLink
                            ]
                        ],
                        'banner' => [
                            'variant' => 'default',
                            'parameters' => [
                                'image' => "http://localhost:5000/storage/glitter-lights.jpg"
                            ]
                        ]
                    ]
            ]
        );
        $r->assertStatus(200);

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'banner' => [
                'variant' => 'default',
                'parameters' => [
                    'image' => $assetLink
                ]
            ]
        ]);
        $r->assertJson([
            'root_sections' =>
                [
                    'general' => [
                        'variant' => 'default',
                        'parameters' => [
                            'logo' => $assetLink
                        ]
                    ],
                    'banner' => [
                        'variant' => 'default',
                        'parameters' => [
                            'image' => $assetLink
                        ]
                    ]
                ]
        ]);
        $r->assertStatus(200);
    }

    public function test_update_assets_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $assetLink = 'invalid_asset_link';

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'general' => [
                'variant' => 'default',
                'parameters' => [
                    'logo' => $assetLink
                ]
            ]
        ]);
        $r->assertStatus(422);

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'banner' => [
                'variant' => 'default',
                'parameters' => [
                    'image' => $assetLink
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    public function test_update_media_link_personal_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'personal' => 'https://sub-domain.domain.online/foo?bar=1'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'personal' => 'https://sub-domain.domain.online/foo?bar=1'
                        ]
                    ]
                ]
        ]);

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'personal' => 'http://abc123.com'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'personal' => 'http://abc123.com'
                        ]
                    ]
                ]
        ]);
    }

    public function test_update_media_link_personal_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'personal' => 'heyhttps://sub-domain.domain.online/foo?bar=1'
                ]
            ]
        ]);
        $r->assertStatus(422);

        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'personal' => 'invalid_url'
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    public function test_update_media_link_facebook_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'facebook' => 'https://facebook.com/Prev-U.foo.bar'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'facebook' => 'https://facebook.com/Prev-U.foo.bar'
                        ]
                    ]
                ]
        ]);
    }

    public function test_update_media_link_facebook_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'facebook' => 'https://not-facebook.com/Prev-U.foo.bar'
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    public function test_update_media_link_twitter_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'twitter' => 'https://twitter.com/a_page_1'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'twitter' => 'https://twitter.com/a_page_1'
                        ]
                    ]
                ]
        ]);
    }

    public function test_update_media_link_twitter_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'twitter' => 'https://not-twitter.com/a_page_1'
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    public function test_update_media_link_youtube_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'youtube' => 'https://www.youtube.com/channel/sfdfs874fsg'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'youtube' => 'https://www.youtube.com/channel/sfdfs874fsg'
                        ]
                    ]
                ]
        ]);
    }

    public function test_update_media_link_youtube_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'youtube' => 'https://not-youtube.com/channel/sfdfs874fsg'
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    public function test_update_media_link_instagram_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'instagram' => 'https://instagram.com/An_instagram.page'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'instagram' => 'https://instagram.com/An_instagram.page'
                        ]
                    ]
                ]
        ]);
    }

    public function test_update_media_link_instagram_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'instagram' => 'https://foo.com/page'
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    public function test_update_media_link_soundcloud_valid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'soundcloud' => 'https://soundcloud.com/PrevU'
                ]
            ]
        ]);
        $r->assertStatus(200);
        $r->assertJson([
            'root_sections' =>
                [
                    'media_links' => [
                        'variant' => 'default',
                        'parameters' => [
                            'soundcloud' => 'https://soundcloud.com/PrevU'
                        ]
                    ]
                ]
        ]);
    }

    public function test_update_media_link_soundcloud_invalid()
    {
        $store = $this->getNewStore($this->sellers[0], 'valid_slug');
        $r = $this->updateRootSections($store, $this->sellers[0], [
            'media_links' => [
                'variant' => 'default',
                'parameters' => [
                    'soundcloud' => 'https://soundcloud-foo.com/PrevU'
                ]
            ]
        ]);
        $r->assertStatus(422);
    }

    private function updateStoreSlug(Store $store, User $user, string $slug): TestResponse
    {
        return $this->actingAs($user)->json('PATCH', "/api/stores/{$store->id}", [
            'slug' => $slug
        ]);
    }

    private function updateRootSections(Store $store, User $user, array $sections): TestResponse
    {
        return $this->actingAs($user)->json('PATCH', "/api/stores/{$store->id}", [
            'root_sections' => $sections
        ]);
    }


}
