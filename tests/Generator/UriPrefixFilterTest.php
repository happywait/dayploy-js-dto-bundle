<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Dayploy\JsDtoBundle\Generator\Generator;
use Dayploy\JsDtoBundle\Tests\AbstractTestCase;

/**
 * `--uri-prefix` — generate only the DTOs serving a family of routes.
 *
 * The point is to hand a front only the types its own routes need, instead of
 * the whole model.
 *
 * ⚠️ Enums are the ONLY cross-file dependency. Nested DTOs are inlined into
 * their parent's file, so a route filter cannot leave one dangling — but an
 * enum is a file of its own that kept DTOs `import`. Dropping one that a kept
 * DTO references would produce a broken import, which is why the enum pass
 * follows references rather than routes.
 */
class UriPrefixFilterTest extends AbstractTestCase
{
    /**
     * ⚠️ These fixtures live in tests/routes, NOT tests/src.
     *
     * tests/src holds AutorefClass, which references itself — and
     * ClassGenerator::generateEntityClassData() recurses into referenced DTOs
     * with no cycle guard, so generating over that directory never returns.
     * That defect predates this filter (it reproduces on an untouched
     * checkout); a separate fixture directory keeps these tests runnable
     * without pretending it is fixed.
     */
    private const SCAN_DIR = './tests/routes';
    private const ENTITY_DIR = __DIR__.'/../routes/Entity';

    /**
     * Everything the filter decides about, cleared before each run so no
     * assertion can pass on a file an earlier test left behind.
     */
    private const VOLATILE = [
        'CustomerRouteClass/Default.ts',
        'PromoterRouteClass/Default.ts',
        'CustomerOnlyEnum.ts',
        'PromoterOnlyEnum.ts',
        'StandaloneRouteClass.ts',
        'NestedRouteClass.ts',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::VOLATILE as $path) {
            @unlink(self::ENTITY_DIR.'/'.$path);
        }
    }

    /** ⚠️ THE test: a foreign route is not generated, and neither is its enum. */
    public function testAPrefixKeepsOnlyTheMatchingRoutes(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('CustomerRouteClass/Default.ts');
        $this->assertNotGenerated('PromoterRouteClass/Default.ts');
    }

    /** An enum a kept DTO uses ships, or the kept DTO would not compile. */
    public function testAReferencedEnumIsPulledIn(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('CustomerOnlyEnum.ts');
    }

    /** ⚠️ And one only a dropped DTO used must NOT ship — otherwise the filter filters nothing. */
    public function testAnUnreferencedEnumIsDropped(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertNotGenerated('PromoterOnlyEnum.ts');
    }

    /**
     * ⚠️ A nested DTO is inlined, never a file. If this stopped holding, the
     * filter would need a second closure pass — this is the assumption the
     * whole design rests on.
     */
    public function testANestedDtoIsInlinedInItsParent(): void
    {
        $this->generate(['/v4/customer']);

        $content = file_get_contents(self::ENTITY_DIR.'/CustomerRouteClass/Default.ts');

        $this->assertStringContainsString('export type NestedRouteClassDefault = {', $content);
        $this->assertFileDoesNotExist(self::ENTITY_DIR.'/NestedRouteClass.ts');
    }

    /**
     * ⚠️ A DTO with no operation at all is a "standalone" one, which the
     * generator writes group-less. Under a filter it must be dropped like any
     * other unmatched class — falling back to the standalone branch would
     * shrink the output without ever removing a class.
     */
    public function testAStandaloneDtoIsDroppedWhenFiltering(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertNotGenerated('StandaloneRouteClass.ts');
    }

    /** …but it still ships when nothing is filtered. */
    public function testAStandaloneDtoStillShipsUnfiltered(): void
    {
        $this->generate([]);

        $this->assertGenerated('StandaloneRouteClass.ts');
    }

    /** No prefix = the previous behaviour, untouched. */
    public function testWithoutAPrefixEverythingIsGenerated(): void
    {
        $this->generate([]);

        $this->assertGenerated('CustomerRouteClass/Default.ts');
        $this->assertGenerated('PromoterRouteClass/Default.ts');
        $this->assertGenerated('PromoterOnlyEnum.ts');
    }

    /** Several prefixes are a union, not a narrowing. */
    public function testPrefixesAccumulate(): void
    {
        $this->generate(['/v4/customer', '/v4/promoter']);

        $this->assertGenerated('CustomerRouteClass/Default.ts');
        $this->assertGenerated('PromoterRouteClass/Default.ts');
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @param string[] $uriPrefixes
     */
    private function generate(array $uriPrefixes): void
    {
        /** @var Generator $generator */
        $generator = self::getContainer()->get(Generator::class);
        $generator->generate([self::SCAN_DIR], $uriPrefixes);
    }

    private function assertGenerated(string $path): void
    {
        $this->assertFileExists(self::ENTITY_DIR.'/'.$path);
    }

    private function assertNotGenerated(string $path): void
    {
        $this->assertFileDoesNotExist(self::ENTITY_DIR.'/'.$path);
    }
}
