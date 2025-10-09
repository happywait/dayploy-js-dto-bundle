<?php

namespace Dayploy\JsDtoBundle\Generator;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\Uid\Uuid;

class TypeConverter
{
    public function __construct(
        private FilenameService $filenameService,
    ) {
    }

    public function extractOtherDtosClasses(
        Type $type
    ): array {
        $result = [];
        switch ($type::class) {
            case ObjectType::class:
                /** @var ObjectType $type */
                if (in_array($type->getClassName(), [Uuid::class, Collection::class, DateTimeImmutable::class, UploadedFile::class, File::class])) {
                    return [];
                }

                return [new \ReflectionClass($type->getClassName())];
            case UnionType::class:
                /** @var UnionType $type */
                $types = $type->getTypes();
                foreach ($types as $index => $subType) {
                    $result = array_merge($result, $this->extractOtherDtosClasses($subType));
                }

                return $result;
            case CollectionType::class:
                /** @var CollectionType $type */
                return array_merge($result, $this->extractOtherDtosClasses($type->getWrappedType()));
            case GenericType::class:
                /** @var GenericType $type */
                $variableType = $type->getVariableTypes() ? $type->getVariableTypes()[1] : null;
                if ($variableType) {
                    return array_merge($result, $this->extractOtherDtosClasses($variableType));
                }

                return [];
            case NullableType::class:
                return array_merge($result, $this->extractOtherDtosClasses($type->getWrappedType()));
            default:
                return [];
        }

        throw new \LogicException('Class '.$type::class.' not handled');
    }

    public function convertType(
        Type $type,
        string $suffix = '',
    ): string {
        switch ($type::class) {
            case ObjectType::class:
                /** @var ObjectType $type */
                if ($type->getClassName() === Uuid::class) {
                    return 'string';
                }
                if ($type->getClassName() === Collection::class) {
                    return '[]';
                }
                if ($type->getClassName() === DateTimeImmutable::class) {
                    return 'string';
                }
                if ($type->getClassName() === UploadedFile::class) {
                    return 'File';
                }
                if ($type->getClassName() === File::class) {
                    return 'string';
                }

                return $this->filenameService->getObjectFromClassname(
                    classname: $type->getClassName(),
                    suffix: $suffix
                );
            case BuiltinType::class:
                /** @var BuiltinType $type */
                if ($type->getTypeIdentifier()->value === 'int') {
                    return 'number';
                }
                if ($type->getTypeIdentifier()->value === 'float') {
                    return 'number';
                }
                if ($type->getTypeIdentifier()->value === 'array') {
                    return '[]';
                }
                if ($type->getTypeIdentifier()->value === 'bool') {
                    return 'boolean';
                }
                if ($type->getTypeIdentifier()->value === 'mixed') {
                    return 'any';
                }

                return $type->__toString();
            case UnionType::class:
                /** @var UnionType $type */
                $types = $type->getTypes();
                $str = '';
                foreach ($types as $index => $subType) {
                    $str .= $this->convertType($subType, $suffix);
                    if (($index + 1) < count($types)) {
                        $str .= ' | ';
                    }
                }

                return $str;
            case BackedEnumType::class:
                /** @var BackedEnumType $type */
                return $this->filenameService->getObjectFromClassname(
                    classname: $type->getClassName(),
                    suffix: $suffix
                );
            case EnumType::class:
                /** @var EnumType $type */
                return '\\'.$type->getClassName();
            case CollectionType::class:
                /** @var CollectionType $type */
                if ($type->isList()) {
                    return $this->convertType($type->getWrappedType(), $suffix);
                }

                return $this->convertType($type->getWrappedType(), $suffix);
            case GenericType::class:
                /** @var GenericType $type */
                $variableType = $type->getVariableTypes() ? $type->getVariableTypes()[1] : null;
                $variableTypesuffix = '';
                if ($variableType) {
                    $variableTypesuffix =  $this->convertType($variableType, $suffix);
                }

                return $variableTypesuffix.$this->convertType($type->getWrappedType(), $suffix);
            case NullableType::class:
                /** @var NullableType $type */
                return $this->convertType($type->getWrappedType(), $suffix).' | null';
        }

        throw new \LogicException('Class '.$type::class.' not handled');
    }
}
