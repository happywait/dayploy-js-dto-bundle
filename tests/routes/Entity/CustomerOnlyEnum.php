<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use Dayploy\JsDtoBundle\Attributes\JsDto;

/** Referenced ONLY by a /v4/customer route. */
#[JsDto]
enum CustomerOnlyEnum: string
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';
}
