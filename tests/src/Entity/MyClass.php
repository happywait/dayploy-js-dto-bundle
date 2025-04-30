<?php

namespace Dayploy\JsDtoBundle\Tests\src\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Dayploy\JsDtoBundle\Attributes\JsDto;
use Dayploy\JsDtoBundle\Attributes\JsDtoIgnore;

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

    private IntValuesEnum $intEnum;
    private StringValuesEnum $stringEnum;

    #[JsDtoIgnore]
    private string $propertyToIgnore;
}
