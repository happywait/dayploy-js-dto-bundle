<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use Acme\Service\Foo;
use Symfony\Component\HttpKernel\KernelInterface;
use Dayploy\JsDtoBundle\Generator\EntityGenerator;
use Dayploy\JsDtoBundle\Generator\Generator;

class GeneratorTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */

        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(\Dayploy\JsDtoBundle\JsDtoBundle::class);
        $kernel->handleOptions($options);
        $kernel->addTestConfig(__DIR__.'/../config.yml');

        return $kernel;
    }

    public function testGenerate(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->assertTrue($container->has(Generator::class));

        /** @var Generator */
        $service = $container->get(Generator::class);
        $service->generate(['./tests/src']);

        $this->assertGeneratedFile('MyClass');
        $this->assertGeneratedFile('ForeignClass');
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
