<?php

namespace Dayploy\JsDtoBundle\Tests\Attributes;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Dayploy\JsDtoBundle\Attributes\AnnotationCollectionFactory;
use Dayploy\JsDtoBundle\Attributes\JsDto;
use Dayploy\JsDtoBundle\Tests\src\Entity\MyClass;

class AnnotationCollectionFactoryTest extends KernelTestCase
{
    public function testCreate(): void
    {
        $factoryAnnotation = new AnnotationCollectionFactory(['./tests/src']);
        $classes = $factoryAnnotation->create();

        $this->assertCount(2, $classes);
        $this->assertArrayHasKey(MyClass::class, $classes);
        $firstClass = $classes[MyClass::class];

        $this->assertCount(5, $firstClass->getProperties());
        $this->assertCount(1, $firstClass->getAttributes());
        $this->assertSame(JsDto::class, $firstClass->getAttributes()[0]->getName());
    }
}
