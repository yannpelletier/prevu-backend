<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $attributes = ['full_name' => ''];

    protected $fillable = ['message', 'full_name', 'email'];

    protected $casts = [
        "id" => "integer"
    ];
}
