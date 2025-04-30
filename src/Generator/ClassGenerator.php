<?php

namespace Dayploy\JsDtoBundle\Generator;

use Psr\Log\LoggerInterface;
use ReflectionClass;

class ClassGenerator
{
    private static string $classTemplate = '<imports>

export interface <entityClassName> {
<entityBody>
}
';

    public function __construct(
        private LoggerInterface $logger,
        private Extractor $extractor,
        private TypeGenerator $typeGenerator,
        private FilenameService $filenameService,
    ) {
    }

    public function generateEntityClass(
        ReflectionClass $reflectionClass,
    ): string {
        $placeHolders = [
            '<imports>',
            '<namespace>',
            '<entityClassName>',
            '<entityBody>',
        ];

        $bodyReplacement = $this->generateEntityBody($reflectionClass);

        $entityClassName = $reflectionClass->getShortName();

        $importStrings = '';

        foreach ($this->filenameService->getImports() as $classname => $path) {
            // self referenced class does not add import
            if ($entityClassName === $classname) {
                continue;
            }

            $importStrings .= "import { type $classname } from '$path'\n";
        }

        $this->filenameService->clearImports();

        return str_replace($placeHolders, [
            $importStrings,
            $reflectionClass->getNamespaceName(),
            $entityClassName,
            $bodyReplacement,
        ], static::$classTemplate);
    }

    protected function generateEntityProperties(
        ReflectionClass $reflectionClass,
    ): string {
        $properties = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $this->logger->info('PROPERTY: '.$propertyName);
            $type = $this->extractor->getType($reflectionClass->getName(), $propertyName);

            // the mixed type gives a null value
            if (null === $type) {
                continue;
            }
            $properties[] = $this->typeGenerator->generate($propertyName, $type);
        }

        return implode("\n\n", array_filter($properties));
    }

    protected function generateEntityBody(
        ReflectionClass $reflectionClass,
    ): string {
        $code = [];

        // EnumType
        $stubMethods = $this->generateEntityProperties($reflectionClass);

        if ($stubMethods) {
            $code[] = $stubMethods;
        }

        return implode("\n", $code);
    }
}
