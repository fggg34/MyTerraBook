<?php

namespace App\Enums;

enum DriveType: string
{
    case Fwd = 'fwd';
    case Rwd = 'rwd';
    case Awd = 'awd';
    case FourByFour = '4wd';

    public function label(): string
    {
        return match ($this) {
            self::Fwd => 'FWD',
            self::Rwd => 'RWD',
            self::Awd => 'AWD',
            self::FourByFour => '4×4',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
