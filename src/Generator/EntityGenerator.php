<?php

namespace Dayploy\JsDtoBundle\Generator;

use Psr\Log\LoggerInterface;
use ReflectionClass;

class EntityGenerator
{
    private static string $classTemplate = 'export interface <entityClassName> {
<entityBody>
}
';

    public function __construct(
        private string $sourceDirectory,
        private string $destinationDirectory,
        private LoggerInterface $logger,
        private Extractor $extractor,
        private TypeGenerator $typeGenerator,
    ) {
    }

    private function generateEntityClass(
        ReflectionClass $reflectionClass,
        bool $useBody = true,
    ): string {
        $placeHolders = [
            '<namespace>',
            '<entityClassName>',
            '<entityBody>',
        ];

        $replacements = [
            $reflectionClass->getNamespaceName(),
            $this->generateEntityClassName($reflectionClass),
        ];

        if ($useBody) {
            $replacements[] = $this->generateEntityBody($reflectionClass);
        }

        return str_replace($placeHolders, $replacements, static::$classTemplate);
    }

    public function writeEntityClass(ReflectionClass $reflectionClass): void
    {
        $this->logger->info('ENTITY: '.$reflectionClass->getName());
        $content = $this->generateEntityClass($reflectionClass);
        $content = $this->removeTrailingSpacesAndTab($content);
        $cleanedContent = $this->removeDoubleEndLine($content);

        $targetFileName = $this->getTraitFileName($reflectionClass->getFileName());

        file_put_contents($targetFileName, $cleanedContent);
    }

    private function getTraitFileName(string $originFileName): string
    {
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

    protected function removeTrailingSpacesAndTab($content): string
    {
        $pattern = '/[ ]*\n/';
        $replacement = "\n";
        $cleanedContent = preg_replace($pattern, $replacement, $content);

        return $cleanedContent;
    }

    private function removeDoubleEndLine($content): string
    {
        $pattern = '/\n\n/';
        $replacement = "\n";
        $cleanedContent = preg_replace($pattern, $replacement, $content);

        return $cleanedContent;
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
