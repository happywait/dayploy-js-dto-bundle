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

        foreach (['OutputActionClass/ThingRead.ts', 'OutputActionClass/ThingCreate.ts', 'OutputActionClass/ThingUpdate.ts', 'OutputActionClass/ThingUpdateOutput.ts', 'OutputNoGroupActionClass/Default.ts', 'OutputNoGroupActionClass/DefaultOutput.ts'] as $file) {
            @unlink(self::ENTITY_DIR.'/'.$file);
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

    /** A group serving the request AND the response keeps typing the request; the response gets `…Output.ts`. */
    public function testAGroupAlsoDenormalizedKeepsTheInputClass(): void
    {
        $this->assertStringContainsString("export type OutputActionClassThingUpdate = {\n  name: string\n}", $this->generated('ThingUpdate.ts'));
        $this->assertStringContainsString("export type OutputActionClassThingUpdateOutput = {\n  label: string\n}", $this->generated('ThingUpdateOutput.ts'));
    }

    /**
     * ⚠️ No group declared at all: `default` is the request's group too. It must keep typing
     * the body sent — answering it with the output class broke two fronts' request types.
     */
    public function testWithoutGroupsDefaultStillTypesTheRequest(): void
    {
        $this->assertStringContainsString("export type OutputNoGroupActionClassDefault = {\n  replacement: string\n}", $this->generated('Default.ts', 'OutputNoGroupActionClass'));
        $this->assertStringContainsString("export type OutputNoGroupActionClassDefaultOutput = {\n  completed: boolean\n}", $this->generated('DefaultOutput.ts', 'OutputNoGroupActionClass'));
    }

    private function generated(string $file, string $class = 'OutputActionClass'): string
    {
        $path = self::ENTITY_DIR.'/'.$class.'/'.$file;
        $this->assertFileExists($path);

        return trim((string) file_get_contents($path));
    }
}
