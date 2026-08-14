<?php

namespace Dayploy\JsDtoBundle\Tests\Generator;

use Dayploy\JsDtoBundle\Generator\Generator;
use Dayploy\JsDtoBundle\Tests\AbstractTestCase;

/**
 * `--exclude-uri-prefix` — generate everything BUT one family of routes.
 *
 * The mirror of --uri-prefix: run both over the same model and each front gets
 * its own half, with no route contract leaking into the other's types.
 *
 * ⚠️ Two rules separate it from a plain negation of the include filter:
 *   - filtering is per operation, so a DTO serving an excluded route AND a
 *     kept one survives;
 *   - an operation with no uriTemplate is KEPT here, where the include filter
 *     drops it — neither can prove where it lives, and both err towards the
 *     front that asked for "everything else".
 */
class ExcludeUriPrefixFilterTest extends AbstractTestCase
{
    /** @see UriPrefixFilterTest::SCAN_DIR for why these fixtures are not in tests/src */
    private const SCAN_DIR = './tests/routes';
    private const ENTITY_DIR = __DIR__.'/../routes/Entity';

    private const VOLATILE = [
        'CustomerRouteClass/Default.ts',
        'PromoterRouteClass/Default.ts',
        'CustomerOnlyEnum.ts',
        'PromoterOnlyEnum.ts',
        'StandaloneRouteClass.ts',
        'NestedRouteClass.ts',
        'MixedRouteClass/Default.ts',
        'NoUriTemplateRouteClass/Default.ts',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::VOLATILE as $path) {
            @unlink(self::ENTITY_DIR.'/'.$path);
        }
    }

    /** ⚠️ THE test: the excluded family goes, everything else stays. */
    public function testAnExcludedPrefixDropsOnlyItsOwnRoutes(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertNotGenerated('CustomerRouteClass/Default.ts');
        $this->assertGenerated('PromoterRouteClass/Default.ts');
    }

    /** A kept DTO's enum ships, or that DTO would not compile. */
    public function testAKeptRoutesEnumStillShips(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('PromoterOnlyEnum.ts');
    }

    /**
     * ⚠️ …and so does an enum only the EXCLUDED routes used. Unlike
     * --uri-prefix, exclusion does not narrow the enum set: an unfiltered run
     * has always shipped every enum, referenced or not, and a front imports
     * enums its own code uses without any DTO mentioning them. Following
     * references here would delete those from the front that asked for
     * "everything else" — measured on hw-backend, 36 of them.
     *
     * The cost is this: a customer-only enum still lands in the promoter
     * output. An extra type file is harmless; a missing one breaks the build.
     */
    public function testAnEnumOnlyTheExcludedRoutesUsedStillShips(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('CustomerOnlyEnum.ts');
    }

    /**
     * ⚠️ Per operation, not per class: excluding one of a DTO's routes must
     * not take the DTO down with it.
     */
    public function testADtoServingBothFamiliesSurvives(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('MixedRouteClass/Default.ts');
    }

    /**
     * ⚠️ A DTO with no operation at all belongs to no family, so no exclusion
     * can name it. Unlike under --uri-prefix, it keeps shipping — otherwise
     * "everything but /v4/customer" would silently lose every standalone type.
     */
    public function testAStandaloneDtoStillShips(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('StandaloneRouteClass.ts');
    }

    /** ⚠️ Same for an operation whose path is unknowable: not provably excluded, so kept. */
    public function testAnOperationWithoutAUriTemplateIsKept(): void
    {
        $this->generate(['/v4/customer']);

        $this->assertGenerated('NoUriTemplateRouteClass/Default.ts');
    }

    /** No exclusion = the previous behaviour, untouched. */
    public function testWithoutAnExclusionEverythingIsGenerated(): void
    {
        $this->generate([]);

        $this->assertGenerated('CustomerRouteClass/Default.ts');
        $this->assertGenerated('PromoterRouteClass/Default.ts');
        $this->assertGenerated('CustomerOnlyEnum.ts');
        $this->assertGenerated('StandaloneRouteClass.ts');
    }

    /** Several exclusions accumulate. */
    public function testExclusionsAccumulate(): void
    {
        $this->generate(['/v4/customer', '/v4/promoter']);

        $this->assertNotGenerated('CustomerRouteClass/Default.ts');
        $this->assertNotGenerated('PromoterRouteClass/Default.ts');
    }

    /** Both filters at once: include first, then exclude out of what it kept. */
    public function testExclusionNarrowsAnInclusion(): void
    {
        $this->generateBoth(['/v4'], ['/v4/customer']);

        $this->assertNotGenerated('CustomerRouteClass/Default.ts');
        $this->assertGenerated('PromoterRouteClass/Default.ts');
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @param string[] $excludeUriPrefixes
     */
    private function generate(array $excludeUriPrefixes): void
    {
        $this->generateBoth([], $excludeUriPrefixes);
    }

    /**
     * @param string[] $uriPrefixes
     * @param string[] $excludeUriPrefixes
     */
    private function generateBoth(array $uriPrefixes, array $excludeUriPrefixes): void
    {
        /** @var Generator $generator */
        $generator = self::getContainer()->get(Generator::class);
        $generator->generate([self::SCAN_DIR], $uriPrefixes, $excludeUriPrefixes);
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
