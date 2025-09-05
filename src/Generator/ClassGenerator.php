<?php

namespace Dayploy\JsDtoBundle\Generator;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Dayploy\JsDtoBundle\Attributes\JsDto;
use Dayploy\JsDtoBundle\Attributes\JsDtoIgnore;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\TypeInfo\Type;

class ClassGenerator
{
    private static string $fileTemplate = '<imports>

<classes>
';
    private static string $classTemplate = 'export type <entityClassName> = {
<entityBody>
}
';
    private static string $fieldTemplate = '  <fieldName>: <type>';

    public function __construct(
        private LoggerInterface $logger,
        private Extractor $extractor,
        private TypeConverter $typeConverter,
        private FilenameService $filenameService,
    ) {
    }

    public function generateEntityClasses(
        ReflectionClass $reflectionClass,
    ): array {
        $routeAttributes = $reflectionClass->getAttributes(HttpOperation::class, \ReflectionAttribute::IS_INSTANCEOF);

        $result = [];

        /** @var HttpOperation $routeAttribute */
        foreach ($routeAttributes as $routeAttribute) {
            $args = $routeAttribute->getArguments();
            if (array_key_exists('normalizationContext', $args) && array_key_exists('groups', $args['normalizationContext'])) {
                $groups = $args['normalizationContext']['groups'];
            } else {
                $groups = ['default'];
            }

            foreach ($groups as $group) {
                $result[] = [
                    'name' => $this->generateFilepath($reflectionClass, $group),
                    'content' => $this->generateEntityClass($reflectionClass, $group)
                ];
            }
        }

        return $result;
    }

    public function generateEntityClass(
        ReflectionClass $reflectionClass,
        string $group
    ): string {
        $placeHolders = [
            '<imports>',
            '<namespace>',
            '<classes>'
        ];

        $data = $this->generateEntityClassData($reflectionClass, $group);

        return str_replace($placeHolders, [
            $data[0],
            $data[1],
            implode("\n\n", $data[2])
        ], static::$fileTemplate);
    }

    public function generateEntityClassData(
        ReflectionClass $reflectionClass,
        string $group
    ): array {
        $placeHolders = [
            '<entityClassName>',
            '<entityBody>'
        ];
        $entityClassName = $reflectionClass->getShortName();

        $bodyData = $this->generateEntityBody($reflectionClass, $group);

        $bodyReplacement = $bodyData['body'];

        $classesDefinition = [str_replace($placeHolders, [
            $entityClassName.$this->generateFilename($group),
            $bodyReplacement,
        ], static::$classTemplate)];

        $importStrings = '';

        /** @var Type\ObjectType $subClass */
        foreach ($bodyData['subClasses'] as $subClass) {
            $subClassesData = $this->generateEntityClassData($subClass, $group);
            $importStrings .= $subClassesData[0];

            foreach ($subClassesData[2] as $def) {
                if (!in_array($def, $classesDefinition)) {
                    $classesDefinition[] = $def;
                }
            }

        }


        foreach ($this->filenameService->getImports() as $classname => $path) {
            // self referenced class does not add import
            if ($entityClassName === $classname) {
                continue;
            }

            // Include only enums
            if (!str_contains($path, '/Enum/')) {
                continue;
            }

            $importStrings .= "import { type $classname } from '$path'\n";
        }

        $this->filenameService->clearImports();

        return [
            $importStrings,
            $reflectionClass->getNamespaceName(),
            $classesDefinition
        ];
    }

    protected function generateEntityProperties(
        ReflectionClass $reflectionClass,
        string $group
    ): array {
        $properties = [];
        $subClasses = [];

        $reflectionProperties = [];
        $currentClass = $reflectionClass;
        /** @var ReflectionClass $parent */
        do {
            foreach ($currentClass->getProperties() as $property) {
                $reflectionProperties[] = $property;
            }
        } while ($currentClass = $currentClass->getParentClass());

        foreach ($reflectionProperties as $property) {
            $propertyName = $property->getName();
            $this->logger->info('PROPERTY: '.$propertyName);

            $attributes = $property->getAttributes();
            if (!$this->isPropertyIncluded($attributes, $group)) {
                $this->logger->info('IGNORED');
                continue;
            }

            $type = $this->extractor->getType($reflectionClass->getName(), $propertyName);

            // the mixed type gives a null value
            if (null === $type) {
                continue;
            }
            $field = $this->generateField($propertyName, $type, $group);

            $properties[] = $field['method'];
            $subClasses = array_merge($subClasses, $field['subClasses']);
        }

        return [
            'body' => implode("\n\n", array_filter($properties)),
            'subClasses' => $subClasses
        ];
    }

    protected function generateEntityBody(
        ReflectionClass $reflectionClass,
        string $group
    ): array {
        $code = [];

        // EnumType
        $stubMethods = $this->generateEntityProperties($reflectionClass, $group);

        if ($stubMethods['body']) {
            $code[] = $stubMethods['body'];
        }

        return [
            'body' => implode("\n", $code),
            'subClasses' => $stubMethods['subClasses']
        ];
    }

    /**
     * @param \ReflectionAttribute[] $attributes
     * @param string $group
     * @return bool
     */
    private function isPropertyIncluded(
        array $attributes,
        string $group
    ): bool {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === JsDtoIgnore::class) {
                return false;
            }
            if (in_array($attribute->getName(), [Groups::class, \Symfony\Component\Serializer\Annotation\Groups::class])) {
                $args = $attribute->getArguments();
                $groups = [];
                if (array_key_exists('groups', $args)) {
                    $groups = $args['groups'];
                } else if (array_key_exists(0, $args)) {
                    $groups = $args[0];
                } else {
                    $groups = ['default'];
                }
                if (!in_array($group, $groups)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function generateField(
        string $fieldName,
        Type $type,
        string $group
    ): array {
        $replacements = [
            '<type>' => $this->typeConverter->convertType($type, $this->generateFilename($group)),
            '<fieldName>' => $fieldName,
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            self::$fieldTemplate
        );

        return [
            "method" => $method,
            "subClasses" => $this->typeConverter->extractOtherDtosClasses($type)
        ];
    }

    private function generateFilepath(
        ReflectionClass $reflectionClass,
        string $group
    ): string {
        $folder = $reflectionClass->getFileName();
        $folder = explode('.', $folder);
        $extension = $folder[1];
        $folder = $folder[0];

        return $folder.'/'.$this->generateFilename($group).'.'.$extension;
    }

    private function generateFilename(
        string $group
    ): string {
        $fileName = explode(':', $group);
        $fileName = implode('', array_map('ucfirst', $fileName));

        return $fileName;
    }
}
