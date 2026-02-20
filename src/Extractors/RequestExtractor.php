<?php

declare(strict_types=1);

namespace Compass\Extractors;

use Compass\Schema\SchemaMapper;
use Illuminate\Foundation\Http\FormRequest;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

final class RequestExtractor
{
    public function __construct(
        private readonly SchemaMapper $schemaMapper,
    ) {}

    public function extract(array $route): ?array
    {
        $formRequestClass = $this->findFormRequest($route['controller']);

        if ($formRequestClass === null) {
            return null;
        }

        $rules = $this->getRules($formRequestClass);

        if ($rules === []) {
            return null;
        }

        return $this->buildSchema($rules, $route['method']);
    }

    public function findFormRequest(string $controller): ?string
    {
        if (! str_contains($controller, '@') && ! str_contains($controller, '::')) {
            // Single-action controller — look at __invoke
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

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $types = [];

            if ($type instanceof ReflectionNamedType) {
                $types[] = $type;
            } elseif ($type instanceof ReflectionUnionType) {
                $types = $type->getTypes();
            }

            foreach ($types as $t) {
                if (! $t instanceof ReflectionNamedType || $t->isBuiltin()) {
                    continue;
                }

                $typeName = $t->getName();

                if (class_exists($typeName) && is_subclass_of($typeName, FormRequest::class)) {
                    return $typeName;
                }
            }
        }

        return null;
    }

    public function getRules(string $formRequestClass): array
    {
        if (! class_exists($formRequestClass)) {
            return [];
        }

        $reflection = new ReflectionClass($formRequestClass);

        if (! $reflection->hasMethod('rules')) {
            return [];
        }

        try {
            // Create instance without constructor side effects
            $instance = $reflection->newInstanceWithoutConstructor();
            $rulesMethod = $reflection->getMethod('rules');
            $rulesMethod->setAccessible(true);

            return $rulesMethod->invoke($instance);
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildSchema(array $rules, string $method): array
    {
        $properties = [];
        $required = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : (array) $fieldRules;

            $schema = $this->schemaMapper->map($ruleList);
            $properties[$field] = $schema;

            if (in_array('required', $ruleList, true)) {
                $required[] = $field;
            }
        }

        if (in_array($method, ['GET', 'HEAD', 'DELETE'], true)) {
            $parameters = [];
            foreach ($properties as $name => $schema) {
                $parameters[] = [
                    'name' => $name,
                    'in' => 'query',
                    'required' => in_array($name, $required, true),
                    'schema' => $schema,
                ];
            }

            return ['parameters' => $parameters];
        }

        $body = [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => $properties,
                    ],
                ],
            ],
        ];

        if ($required !== []) {
            $body['content']['application/json']['schema']['required'] = $required;
        }

        return ['body' => $body];
    }
}
