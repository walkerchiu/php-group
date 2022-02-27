<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Group\Models\Entities\Group;
use WalkerChiu\Group\Models\Entities\GroupLang;

$factory->define(Group::class, function (Faker $faker) {
    return [
        'serial'         => $faker->isbn10,
        'identifier'     => $faker->slug,
        'order'          => $faker->randomNumber,
        'is_highlighted' => $faker->boolean
    ];
});

$factory->define(GroupLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence
    ];
});
