<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Enum;

use Dayploy\JsDtoBundle\Attributes\JsDto;

#[JsDto]
enum ColorEnum: string
{
    case RED = 'red';
    case BLUE = 'blue';
}
