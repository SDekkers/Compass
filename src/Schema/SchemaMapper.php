<?php

declare(strict_types=1);

namespace Compass\Schema;

final class SchemaMapper
{
    public function map(array $rules): array
    {
        $schema = [];

        foreach ($rules as $rule) {
            if (is_object($rule)) {
                continue;
            }

            $rule = (string) $rule;

            match (true) {
                $rule === 'string' => $schema['type'] = 'string',
                $rule === 'integer', $rule === 'int' => $schema['type'] = 'integer',
                $rule === 'numeric' => $schema['type'] = 'number',
                $rule === 'boolean', $rule === 'bool' => $schema['type'] = 'boolean',
                $rule === 'array' => $schema['type'] = 'array',
                $rule === 'email' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'email']),
                $rule === 'url', $rule === 'active_url' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'uri']),
                $rule === 'uuid' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'uuid']),
                $rule === 'date' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'date']),
                $rule === 'date_format:Y-m-d H:i:s', str_starts_with($rule, 'date_format:') => $schema = array_merge($schema, ['type' => 'string', 'format' => 'date-time']),
                $rule === 'ip', $rule === 'ipv4' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'ipv4']),
                $rule === 'ipv6' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'ipv6']),
                $rule === 'json' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'json']),
                $rule === 'nullable' => $schema['nullable'] = true,
                $rule === 'file', $rule === 'image' => $schema = array_merge($schema, ['type' => 'string', 'format' => 'binary']),
                str_starts_with($rule, 'max:') => $this->applyMax($schema, $rule),
                str_starts_with($rule, 'min:') => $this->applyMin($schema, $rule),
                str_starts_with($rule, 'between:') => $this->applyBetween($schema, $rule),
                str_starts_with($rule, 'in:') => $schema['enum'] = explode(',', substr($rule, 3)),
                str_starts_with($rule, 'regex:') => $schema['pattern'] = trim(substr($rule, 6), '/'),
                default => null,
            };
        }

        if (! isset($schema['type'])) {
            $schema['type'] = 'string';
        }

        return $schema;
    }

    private function applyMax(array &$schema, string $rule): void
    {
        $value = (int) substr($rule, 4);
        $type = $schema['type'] ?? 'string';

        if ($type === 'string') {
            $schema['maxLength'] = $value;
        } elseif (in_array($type, ['integer', 'number'], true)) {
            $schema['maximum'] = $value;
        } elseif ($type === 'array') {
            $schema['maxItems'] = $value;
        }
    }

    private function applyMin(array &$schema, string $rule): void
    {
        $value = (int) substr($rule, 4);
        $type = $schema['type'] ?? 'string';

        if ($type === 'string') {
            $schema['minLength'] = $value;
        } elseif (in_array($type, ['integer', 'number'], true)) {
            $schema['minimum'] = $value;
        } elseif ($type === 'array') {
            $schema['minItems'] = $value;
        }
    }

    private function applyBetween(array &$schema, string $rule): void
    {
        $parts = explode(',', substr($rule, 8));
        if (count($parts) !== 2) {
            return;
        }

        $min = (int) $parts[0];
        $max = (int) $parts[1];
        $type = $schema['type'] ?? 'string';

        if ($type === 'string') {
            $schema['minLength'] = $min;
            $schema['maxLength'] = $max;
        } elseif (in_array($type, ['integer', 'number'], true)) {
            $schema['minimum'] = $min;
            $schema['maximum'] = $max;
        }
    }
}
