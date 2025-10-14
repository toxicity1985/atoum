<?php

namespace atoum\atoum\tools\parameter;

class analyzer
{
    public function getTypeHintString(\reflectionParameter $parameter, bool $force_nullable = false): string
    {
        if (!$parameter->hasType()) {
            return '';
        }

        $parameterType = $parameter->getType();

        if (
            $parameterType instanceof \reflectionNamedType
            && in_array($parameterType->getName(), ['mixed', 'null'])
        ) {
            // 'mixed' and 'null' cannot be prefixed by nullable flag '?'
            return $parameterType->getName();
        }

        $parameterTypes = $parameterType instanceof \ReflectionUnionType
            ? $parameterType->getTypes()
            : [$parameterType];

        $names = [];
        foreach ($parameterTypes as $type) {
            $name = $type instanceof \reflectionNamedType ? $type->getName() : (string) $type;
            if ($name === 'self') {
                $name = $parameter->getDeclaringClass()->getName();
            }
            $names[] = ($type instanceof \reflectionType && !$type->isBuiltin() ? '\\' : '') . $name;
        }

        if ($parameterType instanceof \ReflectionUnionType && $force_nullable && !in_array('null', $names)) {
            $names[] = 'null';
        }

        $canBeNull = $force_nullable || $parameter->allowsNull() || ($parameter->isOptional() && !$parameter->isDefaultValueAvailable());
        $prefix = $canBeNull && !($parameterType instanceof \ReflectionUnionType) ? '?' : '';

        $typeString = implode('|', $names);

        return $prefix . $typeString;
    }

    /**
     * Format a ReflectionType into a string representation
     * Handles: NamedType, UnionType
     * Special handling for named types:
     * - 'self' is resolved to the declaring class name
     * - 'parent' is resolved to the parent class name
     * - 'static' is kept as-is (late static binding)
     */
    protected function formatReflectionType(\ReflectionType $type, ?\ReflectionClass $declaringClass = null): string
    {
        // PHP 8.0+: Named types
        if ($type instanceof \reflectionNamedType) {
            $typeName = $type->getName();

            // Handle special keyword types: 'self', 'parent', 'static'
            if ($typeName === 'static') {
                // 'static' is always kept as-is (late static binding keyword)
                return 'static';
            }

            if ($declaringClass !== null) {
                if ($typeName === 'self') {
                    $typeName = $declaringClass->getName();
                } elseif ($typeName === 'parent') {
                    $parentClass = $declaringClass->getParentClass();
                    if ($parentClass !== false) {
                        $typeName = $parentClass->getName();
                    }
                }
            }

            $prefix = '';
            // Only add backslash for non-builtin named types
            if (!$type->isBuiltin()) {
                $prefix = '\\';
            }

            return $prefix . $typeName;
        }

        // PHP 8.0+: Union types
        if ($type instanceof \ReflectionUnionType) {
            $types = array_map(
                function ($t) use ($declaringClass) {
                    return $this->formatReflectionType($t, $declaringClass);
                },
                $type->getTypes()
            );
            return implode('|', $types);
        }

        // Fallback for unknown types
        return (string) $type;
    }
}
