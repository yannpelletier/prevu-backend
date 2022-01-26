<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Watermark;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
$factory->define(Watermark::class, function (Faker $faker) {
    return [
        'name' => 'Centered PrevU Logo',
        'user_id' => null,
        'position' => 'center',
        'dimension' => 'same',
        'public_file_id' => 'logo-watermark',
        'extension' => 'png',
    ];
});

$factory->state(Watermark::class, 'center_logo', function (Faker $faker) {
    return [
        'name' => 'Centered PREV-U Logo',
        'user_id' => null,
        'position' => 'center',
        'dimension' => 'fill-width',
        'public_file_id' => 'logo-watermark',
        'extension' => 'png',
    ];
});

$factory->state(Watermark::class, 'fill_logo', function (Faker $faker) {
    return [
        'name' => 'Fill PREV-U Logo',
        'user_id' => null,
        'position' => 'center',
        'dimension' => 'fill-both',
        'public_file_id' => 'fill-watermark',
        'extension' => 'png',
    ];
});
