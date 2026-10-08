<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Dayploy\JsDtoBundle\Generator\Generator;
use Dayploy\JsDtoBundle\Tests\AbstractTestCase;

/**
 * An array of enums typed by the PHPDoc (`@var SizeEnum[]`) used to be taken for an
 * array of DTOs: the enum was generated as a nested class (`{ name, value }`), whose
 * generation skipped its own import, then cleared the DTO's — the array's enum was
 * referenced without being imported, and the front did not compile.
 */
class EnumArrayTest extends AbstractTestCase
{
    private const ENTITY_DIR = __DIR__.'/../routes/Entity';

    protected function setUp(): void
    {
        parent::setUp();

        @unlink(self::ENTITY_DIR.'/EnumArrayRouteClass/EnumArrayRead.ts');

        /** @var Generator $generator */
        $generator = self::getContainer()->get(Generator::class);
        $generator->generate(['./tests/routes'], ['/v4/enum-array']);
    }

    public function testAnArrayOfEnumsIsImportedAndNotGeneratedAsAClass(): void
    {
        $path = self::ENTITY_DIR.'/EnumArrayRouteClass/EnumArrayRead.ts';
        $this->assertFileExists($path);

        $this->assertSame(<<<'TS'
            import { type ColorEnum } from 'model/Dayploy/JsDtoBundle/TestRoutes/Enum/ColorEnum'
            import { type SizeEnum } from 'model/Dayploy/JsDtoBundle/TestRoutes/Enum/SizeEnum'

            export type EnumArrayRouteClassEnumArrayRead = {
              color: ColorEnum
              sizes: SizeEnum[]
              flag: boolean
            }
            TS, trim((string) file_get_contents($path)));
    }
}
