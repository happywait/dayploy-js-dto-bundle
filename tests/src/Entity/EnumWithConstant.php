<?php

namespace Dayploy\JsDtoBundle\Tests\src\Entity;

use Dayploy\JsDtoBundle\Attributes\JsDto;

/**
 * Regression fixture: an enum carrying non-case constants (an array and a scalar)
 * alongside its cases. getConstants() returns those too; the generator must skip
 * them and emit only the cases.
 */
#[JsDto]
enum EnumWithConstant: string
{
    case none = 'none';
    case warning = 'warning';
    case error = 'error';

    public const DEFAULT = 'none';

    private const PROGRESSION = [self::none, self::warning, self::error];

    public static function ordered(): array
    {
        return self::PROGRESSION;
    }
}
