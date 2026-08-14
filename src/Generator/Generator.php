<?php

namespace Dayploy\JsDtoBundle\Generator;

use ReflectionClass;
use Dayploy\JsDtoBundle\Attributes\AnnotationCollectionFactory;
use Dayploy\JsDtoBundle\Generator\EntityGenerator;

class Generator
{
    public function __construct(
        private EntityGenerator $entityGenerator,
        private FilenameService $filenameService,
    ) {
    }

    /**
     * @param string[] $directories
     * @param string[] $uriPrefixes        when non-empty, only DTOs serving one of
     *                                     these route prefixes are generated, plus the
     *                                     enums they reference
     * @param string[] $excludeUriPrefixes the mirror: operations serving one of these
     *                                     prefixes are dropped, everything else is kept
     */
    public function generate(
        array $directories,
        array $uriPrefixes = [],
        array $excludeUriPrefixes = [],
    ): void {
        $factoryAnnotation = new AnnotationCollectionFactory($directories);
        $classes = $factoryAnnotation->create();

        $enums = [];

        /** @var ReflectionClass $reflectionClass */
        foreach ($classes as $class => $reflectionClass) {
            // Enums wait for the second pass: which ones are needed is only
            // known once every DTO has been generated.
            if ($reflectionClass->isEnum()) {
                $enums[$class] = $reflectionClass;

                continue;
            }

            $this->entityGenerator->writeEntityClass($reflectionClass, $uriPrefixes, $excludeUriPrefixes);
        }

        // ⚠️ Every enum ships unless an INCLUDE filter narrowed the run.
        //
        // --exclude-uri-prefix deliberately does not shrink the enum set: an
        // unfiltered run has always shipped all of them, referenced or not,
        // and a front imports enums its own code uses without any DTO
        // mentioning them (36 of them here — ModuleEnum, CurrencyEnum,
        // SortEnum…). Following references under exclusion would delete those
        // from the front that asked for "everything else". A customer-only
        // enum landing there too is the cheap side of that trade.
        if ([] === $uriPrefixes) {
            foreach ($enums as $reflectionClass) {
                $this->entityGenerator->writeEntityClass($reflectionClass);
            }

            return;
        }

        // ⚠️ Enums are the ONLY cross-file dependency: nested DTOs are inlined
        // into their parent's file, but an enum is a file of its own that kept
        // DTOs `import`. Dropping one a kept DTO references would leave a
        // dangling import, so the filter follows the references rather than
        // the routes here.
        $referenced = $this->filenameService->getReferencedClassnames();

        foreach ($enums as $class => $reflectionClass) {
            if (isset($referenced[$class])) {
                $this->entityGenerator->writeEntityClass($reflectionClass);
            }
        }
    }
}
