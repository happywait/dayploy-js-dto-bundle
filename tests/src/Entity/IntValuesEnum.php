<?php

namespace Dayploy\JsDtoBundle\Tests\src\Entity;

use Dayploy\JsDtoBundle\Attributes\JsDto;

#[JsDto]
enum IntValuesEnum: int
{
    case none = 0;
    case warning = 10;
    case error = 20;
}
