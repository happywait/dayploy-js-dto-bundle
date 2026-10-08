<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\GetCollection;
use Dayploy\JsDtoBundle\Attributes\JsDto;
use Dayploy\JsDtoBundle\TestRoutes\Enum\ColorEnum;
use Dayploy\JsDtoBundle\TestRoutes\Enum\SizeEnum;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * An array of enums, and no other nested class: the enum is typed through the
 * PHPDoc, which reaches the generator as an ObjectType, not as an enum type.
 */
#[GetCollection(
    uriTemplate: '/v4/enum-array/things',
    normalizationContext: ['groups' => ['enumArray:read']],
)]
#[JsDto]
class EnumArrayRouteClass
{
    #[Groups(['enumArray:read'])]
    private ColorEnum $color;

    /**
     * @var SizeEnum[]
     */
    #[Groups(['enumArray:read'])]
    private array $sizes;

    #[Groups(['enumArray:read'])]
    private bool $flag;
}
