<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Store;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Store::class, function (Faker $faker) {
    return [
        'slug' => Str::limit($faker->slug(), 80),
        'user_id' => factory(App\User::class),
        'currency' => 'USD'
    ];
});
