<?php

namespace Dayploy\JsDtoBundle\Generator;

use Psr\Log\LoggerInterface;
use ReflectionClass;

class EntityGenerator
{
    private static string $classTemplate = '<imports>

export interface <entityClassName> {
<entityBody>
}
';

    public function __construct(
        private string $sourceDirectory,
        private string $destinationDirectory,
        private LoggerInterface $logger,
        private Extractor $extractor,
        private TypeGenerator $typeGenerator,
        private FilenameService $filenameService,
        private ContentCleaner $contentCleaner,
    ) {
    }

    private function generateEntityClass(
        ReflectionClass $reflectionClass,
        bool $useBody = true,
    ): string {
        $placeHolders = [
            '<imports>',
            '<namespace>',
            '<entityClassName>',
            '<entityBody>',
        ];

        if ($useBody) {
            $bodyReplacement = $this->generateEntityBody($reflectionClass);
        }

        $importStrings = '';

        foreach ($this->filenameService->getImports() as $classname => $path) {
            $importStrings .= "import { type $classname } from \"$path\"\n";
        }

        $this->filenameService->clearImports();

        return str_replace($placeHolders, [
            $importStrings,
            $reflectionClass->getNamespaceName(),
            $this->generateEntityClassName($reflectionClass),
            $bodyReplacement,
        ], static::$classTemplate);
    }

    public function writeEntityClass(
        ReflectionClass $reflectionClass,
    ): void {
        $this->logger->info('CLASS: '.$reflectionClass->getName());

        $content = $this->generateEntityClass($reflectionClass);
        $content = $this->contentCleaner->removeLeadingNewLines($content);
        $content = $this->contentCleaner->removeTrailingSpacesAndTab($content);
        $cleanedContent = $this->contentCleaner->removeDoubleEndLine($content);

        $targetFileName = $this->getTargetedFilename($reflectionClass->getFileName());
        $this->createDirectories(
            targetFileName: $targetFileName,
        );

        file_put_contents($targetFileName, $cleanedContent);
    }

    private function createDirectories(
        string $targetFileName,
    ): void {
        $parentPath = dirname($targetFileName);

        if (!is_dir($parentPath)) {
            if (!mkdir($parentPath, 0755, true)) {
                throw new \Exception("Failed creating $parentPath");
            }
        }
    }

    private function getTargetedFilename(
        string $originFileName,
    ): string {
        $transformedFileName = str_replace(
            $this->sourceDirectory,
            $this->destinationDirectory,
            $originFileName,
        );

        // remove .php
        $withoutExtension = substr($transformedFileName, 0, strlen($transformedFileName) - 4);

        return $withoutExtension.'.ts';
    }

    private function generateEntityClassName(ReflectionClass $reflectionClass): string
    {
        return $reflectionClass->getShortName();
    }

    protected function generateEntityProperties(ReflectionClass $reflectionClass): string
    {
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

    protected function generateEntityBody(ReflectionClass $reflectionClass): string
    {
        $stubMethods = $this->generateEntityProperties($reflectionClass);
        $code = [];

        if ($stubMethods) {
            $code[] = $stubMethods;
        }

        return implode("\n", $code);
    }
}
