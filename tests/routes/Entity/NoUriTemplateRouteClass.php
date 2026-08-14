<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Get;
use Dayploy\JsDtoBundle\Attributes\JsDto;

/**
 * An operation with NO uriTemplate — API Platform derives its path from the
 * shortName, so the attribute alone cannot say which family it belongs to.
 *
 * ⚠️ The two filters read that unknown the opposite way, on purpose:
 * --uri-prefix drops it (not provably in), --exclude-uri-prefix keeps it (not
 * provably out).
 */
#[Get]
#[JsDto]
class NoUriTemplateRouteClass
{
    private int $id;
    private string $label;
}
