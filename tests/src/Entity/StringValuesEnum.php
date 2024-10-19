<?php

namespace Dayploy\JsDtoBundle\Tests\src\Entity;

use Dayploy\JsDtoBundle\Attributes\JsDto;

#[JsDto]
enum StringValuesEnum: string
{
    case none = 'none';
    case warning = 'warning';
    case error = 'error';
}
