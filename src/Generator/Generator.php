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
     * @param string[] $uriPrefixes when non-empty, only DTOs serving one of
     *                              these route prefixes are generated, plus the
     *                              enums they reference
     */
    public function generate(array $directories, array $uriPrefixes = []): void
    {
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

            $this->entityGenerator->writeEntityClass($reflectionClass, $uriPrefixes);
        }

        // No filter: every enum ships, exactly as before.
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
