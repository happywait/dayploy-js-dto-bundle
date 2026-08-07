<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use Dayploy\JsDtoBundle\Attributes\JsDto;

/**
 * #[JsDto] but NO operation — a "standalone" DTO, which the generator writes
 * group-less into a file of its own.
 *
 * ⚠️ Under a filter it must be dropped like any other unmatched class. The
 * standalone branch is reached on "no classes generated", which a filter also
 * produces — so without care the filter would shrink every file's contents
 * without ever removing a file.
 */
#[JsDto]
class StandaloneRouteClass
{
    private int $id;
    private string $label;
}
