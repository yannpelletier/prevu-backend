<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
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

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->unique()->firstName,
        'last_name' => $faker->unique()->lastName,
        'email' => $faker->unique()->email,
        'password' => 'Secret1',
    ];
});

$factory->state(App\User::class, 'john_doe', function (Faker $faker) {
    return [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe1@mailinator.com',
        'password' => 'Secret1',
        'stripe_connect_id' => 'acct_1FRtCjDQY5rNzOBX'
    ];
});

