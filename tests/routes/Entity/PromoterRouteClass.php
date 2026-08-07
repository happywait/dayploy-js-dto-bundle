<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Get;
use Dayploy\JsDtoBundle\Attributes\JsDto;

#[Get(uriTemplate: '/v4/promoter/things')]
#[JsDto]
class PromoterRouteClass
{
    private int $id;
    private PromoterOnlyEnum $status;
}
