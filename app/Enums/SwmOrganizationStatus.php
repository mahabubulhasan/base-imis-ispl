<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class SwmOrganizationStatus extends Enum
{
    const NonOperational = false;

    const Operational = true;

    public static function getDescription($value): string
    {
        return match ((bool) $value) {
            self::NonOperational => 'Non-operational',
            self::Operational => 'Operational',
        };
    }
}
