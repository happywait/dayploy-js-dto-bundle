<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Get;
use Dayploy\JsDtoBundle\Attributes\JsDto;
use Symfony\Component\Serializer\Attribute\Groups;

#[Get(uriTemplate: '/v4/output/things/{id}', normalizationContext: ['groups' => ['thing:read']])]
#[JsDto]
class OutputReadClass
{
    #[Groups(['thing:read'])]
    private int $id;

    #[Groups(['thing:read', 'thing:update'])]
    private string $label;

    #[Groups(['thing:read'])]
    private NestedRouteClass $nested;
}
