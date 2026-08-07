<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use Dayploy\JsDtoBundle\Attributes\JsDto;

/** Referenced ONLY by a /v4/promoter route — the enum a customer filter must drop. */
#[JsDto]
enum PromoterOnlyEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
}
