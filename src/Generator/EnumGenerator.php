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
            // getConstants() returns *every* class constant, not only enum cases.
            // Skip regular constants (helper consts, arrays, …): they are not enum
            // instances and have no ->name/->value, so reading them would crash.
            if (!$case instanceof \UnitEnum) {
                continue;
            }

            $value = $case instanceof \BackedEnum ? $case->value : $case->name;
            if (is_string($value)) {
                $value = '\''.$value.'\'';
            }
            $code[] = '  '.$case->name.' = '.$value.',';
        }

        return implode("\n", $code);
    }
}
