<?php

namespace App;

use App\Events\ProductCompilationEnded;
use App\Traits\HasEnums;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFiles, HasEnums;

    const DEFAULT_PRICE = 500;
    const DEFAULT_CURRENCY = 'USD';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'price', 'slug', 'custom_thumbnail_id', 'currency', 'file', 'thumbnail_type', 'infos'];
    protected $enumThumbnailTypes = [
        'original',
        'preview',
        'custom',
    ];


    /**
     * Default values for attributes
     * @var  array an array with attribute as key and default as value
     */
    protected $attributes = [
        "price" => self::DEFAULT_PRICE,
        "currency" => self::DEFAULT_CURRENCY,
        "name" => "",
        'description' => "",
        "custom_thumbnail_id" => null,
        "thumbnail_type" => "preview",
        "demo_id" => "",
        "views" => 0,
        "add_to_carts" => 0,
        "sales" => 0,
        "slug" => "",
        "infos" => "{}",
    ];

    protected $casts = [
        "id" => "integer",
        "user_id" => "integer",
        "infos" => "array",
        "price" => "integer",
        "views" => "integer",
        "add_to_carts" => "integer",
        "sales" => "integer"
    ];

    protected $directories = [
        'originals' => [
            'type' => 'main',
            'private' => true,
        ],
        'public_thumbnails' => [
            'type' => 'thumbnail',
            'private' => false,
        ],
        'private_thumbnails' => [
            'type' => 'thumbnail',
            'private' => true,
        ],
        'previews' => [
            'type' => 'compiled',
            'private' => false,
        ]
    ];

    public function getCompilationEndEvent()
    {
        return new ProductCompilationEnded($this->user, $this);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private function mustCompileOriginalPublicThumbnail()
    {
        return $this->isDirty('private_file_id');
    }

    private function mustCompilePreviewPublicThumbnail()
    {
        return $this->hasDirectoryFile('previews');
    }

    private function mustCompileCustomPublicThumbnail()
    {
        return isset($this->attributes['custom_thumbnail_id']) && $this->isDirty('custom_thumbnail_id');
    }

    private function mustCompilePublicThumbnail()
    {
        switch ($this->attributes['thumbnail_type']) {
            case 'original':
                if ($this->mustCompileOriginalPublicThumbnail()) {
                    return true;
                }
                break;
            case 'preview':
                if ($this->mustCompilePreviewPublicThumbnail()) {
                    return true;
                }
                break;
            case 'custom':
                if ($this->mustCompileCustomPublicThumbnail()) {
                    return true;
                }
                break;
            default:
                return $this->isDirty('thumbnail_type');
        }
    }


    private function createOriginalPublicThumbnail()
    {
        $this->createMainFileThumbnail('public_thumbnails');
    }

    private function createPreviewPublicThumbnail()
    {
        $this->createDirectoryFileThumbnail('previews', 'public_thumbnails');
    }

    private function createCustomPublicThumbnail()
    {

    }

    public function createPublicThumbnail()
    {
        if ($this->mustCompilePublicThumbnail()) {
            switch ($this->attributes['thumbnail_type']) {
                case 'original':
                    $this->createOriginalPublicThumbnail();
                    break;
                case 'preview':
                    $this->createPreviewPublicThumbnail();
                    break;
                case 'custom':
                    $this->createCustomPublicThumbnail();
                    break;
            }
        }
    }

    public function setFileAttribute(UploadedFile $file)
    {
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $this->attributes['name'] = Str::title($fileName);
        $this->attributes['slug'] = Str::slug($fileName);
        $this->uploadMainFile($file);
        $this->attributes['infos'] = json_encode($this->getMainFileMetaData($file));
    }

    public function getBeautifulNameAttribute()
    {
        return $this->slug . '.' . $this->extension;
    }
}
