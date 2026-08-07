<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

/**
 * A nested DTO: NO #[JsDto], on purpose.
 *
 * It is never a file of its own — ClassGenerator inlines it into the type of
 * whichever DTO references it. That is why a route filter cannot break nested
 * DTOs, and why only enums need the reference-following pass.
 */
class NestedRouteClass
{
    private int $id;
    private string $label;
}
