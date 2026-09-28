<?php

namespace Dayploy\JsDtoBundle\TestRoutes\Entity;

use ApiPlatform\Metadata\Post;
use Dayploy\JsDtoBundle\Attributes\JsDto;

/**
 * No group declared: `default` serves the request AND the response. Its file keeps typing
 * the request; the `output:` class gets `DefaultOutput.ts`.
 */
#[Post(uriTemplate: '/v4/output/no-group', output: OutputResultClass::class)]
#[JsDto]
class OutputNoGroupActionClass
{
    private string $replacement;
}
