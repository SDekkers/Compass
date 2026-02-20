<?php

declare(strict_types=1);

namespace Compass\Schema;

final class OpenApiBuilder
{
    private array $spec = [];

    private array $tags = [];

    public function init(string $title, string $version, string $description, array $servers): void
    {
        $this->spec = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $title,
                'version' => $version,
            ],
            'paths' => [],
        ];

        if ($description !== '') {
            $this->spec['info']['description'] = $description;
        }

        if ($servers !== []) {
            $this->spec['servers'] = $servers;
        }

        $this->tags = [];
    }

    public function addSecuritySchemes(array $schemes): void
    {
        $this->spec['components']['securitySchemes'] = $schemes;
    }

    public function addPath(
        string $path,
        string $method,
        string $summary,
        string $group,
        array $parameters = [],
        ?array $requestBody = null,
        array $responses = [],
        array $security = [],
    ): void {
        // Convert Laravel route params to OpenAPI path params
        $openApiPath = preg_replace('/\{(\w+)\??}/', '{$1}', $path);

        // Extract path parameters
        $pathParams = [];
        if (preg_match_all('/\{(\w+)\??}/', $path, $matches)) {
            foreach ($matches[1] as $i => $param) {
                $optional = str_contains($matches[0][$i], '?');
                $pathParams[] = [
                    'name' => $param,
                    'in' => 'path',
                    'required' => ! $optional,
                    'schema' => ['type' => 'string'],
                ];
            }
        }

        $allParameters = array_merge($pathParams, $parameters);

        if (! in_array($group, $this->tags, true)) {
            $this->tags[] = $group;
        }

        // Ensure response status codes are strings (OpenAPI 3.1 requirement)
        // and add content type to responses
        $stringResponses = [];
        foreach ($responses as $code => $response) {
            if (! isset($response['content'])) {
                $response['content'] = [
                    'application/json' => [
                        'schema' => ['type' => 'object'],
                    ],
                ];
            }
            $stringResponses[(string) $code] = $response;
        }

        $operation = [
            'tags' => [$group],
            'responses' => $stringResponses,
        ];

        if ($summary !== '') {
            $operation['summary'] = $summary;
            $operation['operationId'] = $method . '.' . $summary;
        }

        if ($allParameters !== []) {
            $operation['parameters'] = $allParameters;
        }

        if ($requestBody !== null) {
            $operation['requestBody'] = $requestBody;
        }

        if ($security !== []) {
            $operation['security'] = $security;
        }

        $this->spec['paths'][$openApiPath][$method] = $operation;
    }

    public function build(): array
    {
        if ($this->tags !== []) {
            $this->spec['tags'] = array_map(
                fn (string $tag): array => ['name' => $tag],
                $this->tags,
            );
        }

        return $this->spec;
    }
}
