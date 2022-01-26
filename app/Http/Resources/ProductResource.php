<?php

namespace App\Http\Resources;

use App\Asset;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    private static $requestOriginalLink;

    /**
     * Sets whether or not the temporary url of the original file and thumbnail original
     * should be in the response resource.
     *
     * @param $requestOriginalLink
     */
    public static function requestOriginalLink(bool $requestOriginalLink)
    {
        self::$requestOriginalLink = $requestOriginalLink;
    }

    private function isAuthUserOwner()
    {
        $authUser = Auth::user();
        $authUserId = $authUser !== null ? $authUser->id : null;

        return $authUserId == $this->user_id;
    }

    private function getRealThumbnailUrl()
    {
        if ($this->custom_thumbnail_id !== null) {
            $thumbnailAsset = Asset::find($this->custom_thumbnail_id);
            if ($thumbnailAsset) {
                return Storage::url(Asset::find($this->custom_thumbnail_id)->getMainStoragePath());
            }
        }
        return Storage::temporaryUrl($this->getThumbnailStoragePath(), now()->addMinutes(5));
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $productUser = $this->user;
        $store = $productUser->store;
        $storeId = $store ? $store->id : null;

        // TODO: Change when more than one currency
        $currency = $store ? $store->currency : 'USD';

        if ($store) {
            $storeUrl = config('app.frontend_url') . "/store/" . $store->slug;
            $link = sprintf('%s/?product=%s', $storeUrl, $this->slug);
        } else {
            $link = null;
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'store_id' => $storeId,
            'extension' => $this->extension,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $currency,
            'infos' => $this->infos,
            'views' => $this->views,
            'add_to_carts' => $this->add_to_carts,
            'sales' => $this->sales,
            'filters' => $this->filters,
            'filters_info' => $this->filtersInfo,
            'compilation_state' => $this->compilation_state,
            'custom_thumbnail_id' => $this->custom_thumbnail_id,
            'thumbnail_type' => $this->thumbnail_type,

            //Links
            'preview' => Storage::url($this->getDirectoryStoragePath('previews')),
            'public_thumbnail' => Storage::url($this->getDirectoryStoragePath('public_thumbnails')),
            'link' => $link,
            $this->mergeWhen($this->isAuthUserOwner() && self::$requestOriginalLink, [
                'original' => Storage::url($this->getMainStoragePath()),
                'private_thumbnail' => Storage::url($this->getDirectoryStoragePath('private_thumbnails')),
            ])
        ];
    }
}
