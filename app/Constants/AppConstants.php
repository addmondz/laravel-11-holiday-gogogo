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
}
