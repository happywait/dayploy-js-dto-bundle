<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Get;
use Dayploy\JsDtoBundle\Attributes\JsDto;

#[Get(uriTemplate: '/v4/customer/things')]
#[JsDto]
class CustomerRouteClass
{
    private int $id;
    private CustomerOnlyEnum $status;
    private NestedRouteClass $nested;
}
