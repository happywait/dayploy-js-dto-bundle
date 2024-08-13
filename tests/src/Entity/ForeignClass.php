<?php

namespace Dayploy\JsDtoBundle\Tests\src\Entity;

use Symfony\Component\Uid\Uuid;
use Dayploy\JsDtoBundle\Attributes\JsDto;

#[JsDto]
class ForeignClass
{
    private Uuid $id;
    private ?MyClass $myClass;
}
