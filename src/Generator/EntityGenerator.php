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

    public function writeEntityClass(
        ReflectionClass $reflectionClass,
    ): void {
        $this->logger->info('CLASS: '.$reflectionClass->getName());

        if ($reflectionClass->isEnum()) {
            $content = $this->enumGenerator->generateEntityClass($reflectionClass);
        } else {
            $content = $this->classGenerator->generateEntityClass($reflectionClass);
        }

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
}
