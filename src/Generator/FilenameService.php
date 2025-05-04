<?php

namespace Dayploy\JsDtoBundle\Generator;

class FilenameService
{
    public function __construct(
        private readonly string $modelPath,
    ) {
    }

    /** store converted object to add to the top import */
    private $imports = [];

    public function clearImports()
    {
        $this->imports = [];
    }

    public function getImports(): array
    {
        return $this->imports;
    }

    public function getObjectFromClassname(
        string $classname,
    ): string {
        $elements = explode('\\', $classname);

        $objectName = end($elements);

        $this->imports[$objectName] = $this->getPathFromClassname(
            classname: $classname,
            prefixToRemove: 'App\\',
        );

        return $objectName;
    }

    private function getPathFromClassname(
        string $classname,
        string $prefixToRemove,
    ): string {
        $classname = str_replace(
            $prefixToRemove,
            '',
            $classname,
        );

        $classname = str_replace(
            '\\',
            '/',
            $classname,
        );
        $classname = $this->modelPath.'/'.$classname;

        return str_replace(
            '//',
            '/',
            $classname,
        );
    }
}
