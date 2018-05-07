<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

function randDate()
{
    return \Carbon\Carbon::now()
        ->subDays(rand(1, 100))
        ->subHours(rand(1, 23))
        ->subMinutes(rand(1, 60));
}

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $createdAt = randDate();

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail,
        'password' => app('hash')->make('secret'),
        'active'   => rand(0,1),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ];
});

$factory->define(App\Models\Destiny::class, function (Faker\Generator $faker) {
    $createdAt = randDate();

    return [
        'name' => $faker->sentence(10),
        'country' => $faker->country,
        'lat' => $faker->latitude(),
        'long' => $faker->longitude(),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ];
});


$factory->define(App\Models\Event::class, function (Faker\Generator $faker) {
    $createdAt = randDate();

    return [
        'destiny_id' => factory(App\Models\Destiny::class)->create()->id,
        'user_id' => factory(App\Models\User::class)->create()->id,
        'title' => $faker->sentence(10),
        'desc' => $faker->sentence(20),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ];
});
