<?php


namespace Packages\ShaunSocial\Core\Traits;

trait EnumToArray
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
