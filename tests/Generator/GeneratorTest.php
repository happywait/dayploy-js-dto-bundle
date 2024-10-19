<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Dayploy\JsDtoBundle\Generator\Generator;
use Dayploy\JsDtoBundle\Tests\AbstractTestCase;

class GeneratorTest extends AbstractTestCase
{
    public function testGenerate(): void
    {
        $container = self::getContainer();

        $this->assertTrue($container->has(Generator::class));

        /** @var Generator */
        $service = $container->get(Generator::class);
        $service->generate(['./tests/src']);

        $this->assertGeneratedFile('MyClass');
        $this->assertGeneratedFile('ForeignClass');
        $this->assertGeneratedFile('AutorefClass');
        $this->assertGeneratedFile('IntValuesEnum');
        $this->assertGeneratedFile('StringValuesEnum');
    }

    private function assertGeneratedFile(string $filename): void
    {
        $expectedForeignClassTrait = \file_get_contents(__DIR__.'/Expected'.$filename.'.ts');
        $foreignClassTrait = \file_get_contents(__DIR__.'/../src/Entity/'.$filename.'.ts');

        // line kept for dev purpose
        // file_put_contents(__DIR__.'/Expected'.$filename.'.ts', $foreignClassTrait);

        $this->assertSame($expectedForeignClassTrait, $foreignClassTrait);
    }
}
