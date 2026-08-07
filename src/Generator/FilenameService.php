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

    /**
     * Every FQCN referenced by a generated file, for the whole run.
     *
     * Unlike $imports, this is NEVER cleared: ClassGenerator empties $imports
     * after each file it writes, so by the end of the run that array only
     * remembers the last one. Under --uri-prefix we need the union — an enum
     * imported by a kept DTO must ship, whichever file happened to pull it in.
     *
     * @var array<string, true>
     */
    private array $referencedClassnames = [];

    public function clearImports()
    {
        $this->imports = [];
    }

    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @return array<string, true> FQCN => true
     */
    public function getReferencedClassnames(): array
    {
        return $this->referencedClassnames;
    }

    public function getObjectFromClassname(
        string $classname,
        string $suffix = ''
    ): string {
        $elements = explode('\\', $classname);

        $objectName = end($elements);

        $this->referencedClassnames[$classname] = true;

        $this->imports[$objectName] = $this->getPathFromClassname(
            classname: $classname,
            prefixToRemove: 'App\\',
        );

        if (!str_contains($classname, '\\Enum\\')) {
            $objectName = $objectName.$suffix;
        }

        return $objectName;
    }

    public function getPathFromClassname(
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
