<?php

namespace Dayploy\JsDtoBundle\Generator;

use ReflectionClass;

class EnumGenerator
{
    private static string $classTemplate = '
export enum <entityClassName> {
<entityBody>
}
';

    public function generateEntityClass(
        ReflectionClass $reflectionClass,
    ): string {
        $placeHolders = [
            '<entityClassName>',
            '<entityBody>',
        ];

        $bodyReplacement = $this->generateEntityBody($reflectionClass);
        $entityClassName = $reflectionClass->getShortName();

        return str_replace($placeHolders, [
            $entityClassName,
            $bodyReplacement,
        ], static::$classTemplate);
    }

    protected function generateEntityBody(
        ReflectionClass $reflectionClass,
    ): string {
        $code = [];
        $cases = $reflectionClass->getConstants();

        foreach ($cases as $case) {
            $value = $case->value;
            if (is_string($value)) {
                $value = '"'.$value.'"';
            }
            $code[] = '  '.$case->name.' = '.$value.',';
        }

        return implode("\n", $code);
    }
}
