<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Post;
use Dayploy\JsDtoBundle\Attributes\JsDto;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * An action whose response is another class (`output:`): its normalization group
 * describes OutputReadClass, its denormalization group describes itself.
 */
#[Post(
    uriTemplate: '/v4/output/things',
    normalizationContext: ['groups' => ['thing:read']],
    denormalizationContext: ['groups' => ['thing:create']],
    output: OutputReadClass::class,
)]
#[Post(
    uriTemplate: '/v4/output/things/{id}',
    normalizationContext: ['groups' => ['thing:update']],
    denormalizationContext: ['groups' => ['thing:update']],
    output: OutputReadClass::class,
)]
#[JsDto]
class OutputActionClass
{
    #[Groups(['thing:create', 'thing:update'])]
    private string $name;
}
