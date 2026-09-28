<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Dayploy\JsDtoBundle\Generator\Generator;
use Dayploy\JsDtoBundle\Tests\AbstractTestCase;

/**
 * An operation declaring `output:` answers with that class. Its normalization groups used
 * to describe the INPUT class, which has no property in them: the response type came out
 * as `{}`, and the front had to type it by hand.
 */
class OutputClassTest extends AbstractTestCase
{
    private const ENTITY_DIR = __DIR__.'/../routes/Entity';

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ThingRead.ts', 'ThingCreate.ts', 'ThingUpdate.ts'] as $file) {
            @unlink(self::ENTITY_DIR.'/OutputActionClass/'.$file);
        }

        /** @var Generator $generator */
        $generator = self::getContainer()->get(Generator::class);
        $generator->generate(['./tests/routes'], ['/v4/output']);
    }

    /** ⚠️ THE test: the response type is the output class, under the action's own name and path. */
    public function testTheResponseTypeDescribesTheOutputClass(): void
    {
        $this->assertSame(<<<'TS'
            export type OutputActionClassThingRead = {
              id: number
              label: string
              nested: NestedRouteClassThingRead
            }

            export type NestedRouteClassThingRead = {
              id: number
              label: string
            }
            TS, $this->generated('ThingRead.ts'));
    }

    public function testTheRequestTypeStillDescribesTheInputClass(): void
    {
        $this->assertStringContainsString("export type OutputActionClassThingCreate = {\n  name: string\n}", $this->generated('ThingCreate.ts'));
    }

    /** A group serving the request AND the response keeps typing the request. */
    public function testAGroupAlsoDenormalizedKeepsTheInputClass(): void
    {
        $this->assertStringContainsString("export type OutputActionClassThingUpdate = {\n  name: string\n}", $this->generated('ThingUpdate.ts'));
    }

    private function generated(string $file): string
    {
        $path = self::ENTITY_DIR.'/OutputActionClass/'.$file;
        $this->assertFileExists($path);

        return trim((string) file_get_contents($path));
    }
}
