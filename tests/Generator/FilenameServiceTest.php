<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Dayploy\JsDtoBundle\Generator\FilenameService;
use Dayploy\JsDtoBundle\Tests\AbstractTestCase;

class FilenameServiceTest extends AbstractTestCase
{
    public function testGetObjectFromClassname(): void
    {
        $container = self::getContainer();

        /** @var FilenameService */
        $service = $container->get(FilenameService::class);

        $this->assertSame('SomeString', $service->getObjectFromClassname('SomeString'));
        $this->assertSame('Class', $service->getObjectFromClassname('Path\To\Class'));

        $this->assertSame([
            'SomeString' => '@model/SomeString',
            'Class' => '@model/Path/To/Class',
        ], $service->getImports());
    }
}
