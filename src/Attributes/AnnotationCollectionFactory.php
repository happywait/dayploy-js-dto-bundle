<?php declare(strict_types=1);

namespace Dayploy\JsDtoBundle\Attributes;

class AnnotationCollectionFactory
{
    public function __construct(private array $paths)
    {
    }

    public function create(): array
    {
        $classes = [];

        foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->paths) as $className => $reflectionClass) {
            if ($reflectionClass->getAttributes(JsDto::class)) {
                $classes[$className] = $reflectionClass;
            }
        }

        return $classes;
    }
}
