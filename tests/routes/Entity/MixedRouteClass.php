<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Get;
use Dayploy\JsDtoBundle\Attributes\JsDto;

/**
 * Serves BOTH families.
 *
 * ⚠️ Filtering is per operation, not per class: excluding /v4/customer must
 * not take this DTO down with it, since the promoter front still reaches it
 * through the other route.
 */
#[Get(uriTemplate: '/v4/customer/mixed')]
#[Get(uriTemplate: '/v4/promoter/mixed')]
#[JsDto]
class MixedRouteClass
{
    private int $id;
    private string $label;
}
