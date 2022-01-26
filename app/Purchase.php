<?php

namespace App;

use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use HasFiles;

    public $timestamps = true;

    protected $attributes = [
        'charge_id' => '',
        'approved' => false
    ];

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'product_id',
        'name',
        'description',
        'price',
        'file_id',
        'extension',
        'currency',
        'charge_id',
        'approved'
    ];

    protected $casts = [
        "id" => "integer",
        "seller_id" => "integer",
        "buyer_id" => "integer",
        "product_id" => "integer",
        "price" => "integer",
        "approved" => "boolean",
    ];

    protected $directories = [
        'originals' => [
            'type' => 'main',
            'private' => true,
        ],
        'private_thumbnails' => [
            'type' => 'thumbnail',
            'private' => true,
        ]
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getFileNameAttribute()
    {
        return sprintf('%s.%s', Str::slug($this->name), $this->extension);
    }
}
