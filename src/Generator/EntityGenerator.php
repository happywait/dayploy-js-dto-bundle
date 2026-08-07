<?php

namespace Dayploy\JsDtoBundle\Generator;

use Psr\Log\LoggerInterface;
use ReflectionClass;

class EntityGenerator
{
    public function __construct(
        private string $sourceDirectory,
        private string $destinationDirectory,
        private LoggerInterface $logger,
        private ContentCleaner $contentCleaner,
        private ClassGenerator $classGenerator,
        private EnumGenerator $enumGenerator,
    ) {
    }

    /**
     * @param string[] $uriPrefixes see ClassGenerator::generateEntityClasses()
     */
    public function writeEntityClass(
        ReflectionClass $reflectionClass,
        array $uriPrefixes = [],
    ): void {
        $this->logger->info('CLASS: '.$reflectionClass->getName());

        if ($reflectionClass->isEnum()) {
            $content = $this->enumGenerator->generateEntityClass($reflectionClass);

            $this->writeEntityClassFile($content, $reflectionClass->getFileName());
        } else {
            $classes = $this->classGenerator->generateEntityClasses($reflectionClass, $uriPrefixes);

            // Standalone DTO (no HttpOperations): generate like enum, direct file
            if (empty($classes)) {
                // ⚠️ Under a filter, an empty result means "nothing matched",
                // NOT "standalone DTO". Falling back here would write every
                // filtered-out class as a group-less type — the filter would
                // shrink the output without ever removing a class.
                if ([] !== $uriPrefixes) {
                    return;
                }

                $content = $this->classGenerator->generateStandaloneClass($reflectionClass);
                $this->writeEntityClassFile($content, $reflectionClass->getFileName());
            } else {
                foreach ($classes as $class) {
                    $this->writeEntityClassFile($class['content'], $class['name']);
                }
            }
        }
    }

    public function writeEntityClassFile(string $content, string $filename)
    {
        $content = $this->contentCleaner->removeLeadingNewLines($content);
        $content = $this->contentCleaner->removeTrailingSpacesAndTab($content);
        $cleanedContent = $this->contentCleaner->removeDoubleEndLine($content);

        $targetFileName = $this->getTargetedFilename($filename);
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
}
