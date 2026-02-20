<?php

declare(strict_types=1);

namespace Compass\Extractors;

use Illuminate\Http\Resources\Json\JsonResource;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

final class ResponseExtractor
{
    public function extract(array $route): ?array
    {
        $resourceClass = $this->findResourceClass($route['controller']);

        if ($resourceClass === null) {
            return null;
        }

        $schema = $this->extractResourceSchema($resourceClass);

        if ($schema === null) {
            return null;
        }

        return [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => $schema,
                    ],
                ],
            ],
        ];
    }

    public function findResourceClass(string $controller): ?string
    {
        if (! str_contains($controller, '@') && ! str_contains($controller, '::')) {
            $method = '__invoke';
            $class = $controller;
        } elseif (str_contains($controller, '@')) {
            [$class, $method] = explode('@', $controller);
        } else {
            return null;
        }

        if (! class_exists($class)) {
            return null;
        }

        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (\ReflectionException) {
            return null;
        }

        $returnType = $reflection->getReturnType();

        if ($returnType instanceof ReflectionNamedType && ! $returnType->isBuiltin()) {
            $typeName = $returnType->getName();

            if (class_exists($typeName) && is_subclass_of($typeName, JsonResource::class)) {
                return $typeName;
            }
        }

        return null;
    }

    public function extractResourceSchema(string $resourceClass): ?array
    {
        if (! class_exists($resourceClass)) {
            return null;
        }

        $reflection = new ReflectionClass($resourceClass);

        if (! $reflection->hasMethod('toArray')) {
            return null;
        }

        $method = $reflection->getMethod('toArray');

        // Only parse if the method is defined in the resource class itself
        if ($method->getDeclaringClass()->getName() !== $resourceClass) {
            return null;
        }

        // Try to extract keys from source code
        $properties = $this->parseToArrayKeys($method);

        if ($properties === []) {
            return null;
        }

        return [
            'type' => 'object',
            'properties' => $properties,
        ];
    }

    private function parseToArrayKeys(ReflectionMethod $method): array
    {
        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        if ($filename === false || $startLine === false || $endLine === false) {
            return [];
        }

        $lines = array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1);
        $source = implode('', $lines);

        $properties = [];

        // Match array keys like 'key' => ...
        if (preg_match_all("/['\"](\w+)['\"]\s*=>/", $source, $matches)) {
            foreach ($matches[1] as $key) {
                $properties[$key] = ['type' => 'string'];
            }
        }

        return $properties;
    }
}
