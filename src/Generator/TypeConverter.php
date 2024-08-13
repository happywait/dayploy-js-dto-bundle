<?php

namespace Dayploy\JsDtoBundle\Generator;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\Uid\Uuid;

class TypeConverter
{
    public function convertType(Type $type): string
    {
        switch ($type::class) {
            case ObjectType::class:
                /** @var ObjectType $type */
                if ($type->getClassName() ===  Uuid::class) {
                    return 'string';
                }
                if ($type->getClassName() ===  Collection::class) {
                    return '[]';
                }

                return '\\'.$type->getClassName();
            case BuiltinType::class:
                /** @var BuiltinType $type */
                if ($type->getTypeIdentifier()->value ===  'int') {
                    return 'number';
                }
                if ($type->getTypeIdentifier()->value ===  'array') {
                    return '[]';
                }
                return $type->__toString();
            case UnionType::class:
                /** @var UnionType $type */
                $types = $type->getTypes();
                $str = '';
                foreach ($types as $index => $subType) {
                    $str .= $this->convertType($subType);
                    if (($index + 1) < count($types)) {
                        $str .= '|';
                    }
                }

                return $str;
            case BackedEnumType::class:
                /** @var BackedEnumType $type */
                return '\\'.$type->getClassName();
            case EnumType::class:
                /** @var EnumType $type */
                return '\\'.$type->getClassName();
            case CollectionType::class:
                /** @var CollectionType $type */
                if ($type->isList()) {
                    return $this->convertType($type->getType());
                }

                return $this->convertType($type->getType());
            case GenericType::class:
                /** @var GenericType $type */
                return $this->convertType($type->getType());
        }

        throw new \LogicException('Class '.$type::class.' not handled');
    }
}
