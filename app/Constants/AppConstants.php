<?php

namespace App\Constants;

class AppConstants
{
    public const CONFIGURATION_PRICE_TYPES_BASE_CHARGE = 'b';
    public const CONFIGURATION_PRICE_TYPES_SUR_CHARGE = 's';

    public const CONFIGURATION_PRICE_TYPES_TITLE = [
        self::CONFIGURATION_PRICE_TYPES_BASE_CHARGE => 'Base Charge',
        self::CONFIGURATION_PRICE_TYPES_SUR_CHARGE => 'Sur Charge',
    ];

    public const CONFIGURATION_PRICE_TYPES_SNAKE_CASE = [
        self::CONFIGURATION_PRICE_TYPES_BASE_CHARGE => 'base_charge',
        self::CONFIGURATION_PRICE_TYPES_SUR_CHARGE => 'sur_charge',
    ];

    // public const ADULT_CHILD_COMBINATIONS = [
    //     ['adults' => 1, 'children' => 0],
    //     ['adults' => 1, 'children' => 1],
    //     ['adults' => 1, 'children' => 2],
    //     ['adults' => 1, 'children' => 3],
    //     ['adults' => 1, 'children' => 4],
    //     ['adults' => 2, 'children' => 0],
    //     ['adults' => 2, 'children' => 1],
    //     ['adults' => 2, 'children' => 2],
    //     ['adults' => 2, 'children' => 3],
    //     ['adults' => 3, 'children' => 0],
    //     ['adults' => 3, 'children' => 1],
    //     ['adults' => 3, 'children' => 2],
    //     ['adults' => 4, 'children' => 0],
    //     ['adults' => 4, 'children' => 1],
    //     ['adults' => 5, 'children' => 0],
    // ];
    public const ADULT_CHILD_COMBINATIONS = [
        ['adults' => 1, 'children' => 0],
        ['adults' => 1, 'children' => 1],
        ['adults' => 1, 'children' => 2],
        ['adults' => 1, 'children' => 3],
        ['adults' => 2, 'children' => 0],
        ['adults' => 2, 'children' => 1],
        ['adults' => 2, 'children' => 2],
        ['adults' => 3, 'children' => 0],
        ['adults' => 3, 'children' => 1],
        ['adults' => 4, 'children' => 0],
    ];

    public const TESTING_IMAGES = [
        'room-types/test1.jpg',
        'room-types/test2.png',
        'room-types/test3.jpg'
    ];
}
