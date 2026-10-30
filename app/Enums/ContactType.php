<?php

namespace App\Enums;

enum ContactType: string
{
    case B2B = 'B2B';
    case B2C = 'B2C';

    public function label(): string
    {
        return match ($this) {
            self::B2B => 'B2B · Empresa',
            self::B2C => 'B2C · Persona',
        };
    }
}
