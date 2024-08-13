<?php

namespace Dayploy\JsDtoBundle\Tests\src\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Dayploy\JsDtoBundle\Attributes\JsDto;

#[JsDto]
class MyClass
{
    private Uuid $id;
    private int $number;
    private string $name;

    /**
     * @var Collection<ForeignClass>
     */
    private Collection $foreignClasses;

    /**
     * @var array<int>
     */
    private array $references;
}
