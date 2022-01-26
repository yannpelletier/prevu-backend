<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Product;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Product::class, function (Faker $faker) {
    return [
        // mock_file_id must already exist have its own preview, original and thumbnail file (in S3).
        'private_file_id' => 'mock_file_id',
        'public_file_id' => 'mock_file_id',
        'extension' => 'jpeg',
        'name' => $faker->text(80),
        'description' => $faker->text(1000),
        'user_id' => factory(App\User::class),
        'slug' => Str::limit($faker->slug(), 80),
        'price' => rand(200, 1000000),
        'currency' => 'USD',
        'compilation_state' => 'compiled'
    ];
});
