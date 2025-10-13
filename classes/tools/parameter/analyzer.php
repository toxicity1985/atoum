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

        // Format the type into a string representation
        $typeString = $this->formatReflectionType($parameterType, $parameter->getDeclaringClass());

        // Handle nullable for non-union/intersection types
        $canBeNull = $force_nullable || $parameter->allowsNull() || ($parameter->isOptional() && !$parameter->isDefaultValueAvailable());
        $isComplexType = $parameterType instanceof \ReflectionUnionType
            || (class_exists(\ReflectionIntersectionType::class) && $parameterType instanceof \ReflectionIntersectionType);

        $prefix = $canBeNull && !$isComplexType && !str_contains($typeString, '|') ? '?' : '';

        // Add null to union if forced nullable
        if ($parameterType instanceof \ReflectionUnionType && $force_nullable && !str_contains($typeString, 'null')) {
            $typeString .= '|null';
        }

        return $prefix . $typeString;
    }

    /**
     * Format a ReflectionType into a string representation
     * Handles: NamedType, UnionType, IntersectionType (PHP 8.1+), and DNF types (PHP 8.2+)
     * 
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

        // PHP 8.1+: Intersection types
        if (class_exists(\ReflectionIntersectionType::class) && $type instanceof \ReflectionIntersectionType) {
            $types = array_map(
                function ($t) use ($declaringClass) {
                    $formatted = $this->formatReflectionType($t, $declaringClass);
                    // If the formatted type contains a union (|), wrap in parentheses for DNF
                    if (strpos($formatted, '|') !== false) {
                        return '(' . $formatted . ')';
                    }
                    return $formatted;
                },
                $type->getTypes()
            );
            return implode('&', $types);
        }

        // Fallback for unknown types
        return (string) $type;
    }
}
