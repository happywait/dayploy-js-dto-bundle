<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Enum;

use Dayploy\JsDtoBundle\Attributes\JsDto;

#[JsDto]
enum SizeEnum: string
{
    case SMALL = 'small';
    case LARGE = 'large';
}
