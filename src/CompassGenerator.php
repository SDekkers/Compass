<?php

declare(strict_types=1);

namespace Compass;

use Compass\Extractors\MiddlewareExtractor;
use Compass\Extractors\RequestExtractor;
use Compass\Extractors\ResponseExtractor;
use Compass\Extractors\RouteExtractor;
use Compass\Schema\OpenApiBuilder;
use Compass\Writers\JsonWriter;
use Compass\Writers\YamlWriter;

final class CompassGenerator
{
    public function __construct(
        private readonly RouteExtractor $routeExtractor,
        private readonly RequestExtractor $requestExtractor,
        private readonly ResponseExtractor $responseExtractor,
        private readonly MiddlewareExtractor $middlewareExtractor,
        private readonly OpenApiBuilder $builder,
    ) {}

    public function generate(): array
    {
        $routes = $this->routeExtractor->extract();

        $this->builder->init(
            title: config('compass.title', 'API Documentation'),
            version: config('compass.version', '1.0.0'),
            description: config('compass.description', ''),
            servers: config('compass.servers', []),
        );

        $securitySchemes = $this->middlewareExtractor->extractSecuritySchemes($routes);
        if ($securitySchemes !== []) {
            $this->builder->addSecuritySchemes($securitySchemes);
        }

        foreach ($routes as $route) {
            $parameters = [];
            $requestBody = null;
            $responses = ['200' => ['description' => 'Successful response']];

            $requestData = $this->requestExtractor->extract($route);
            if ($requestData !== null) {
                if (in_array($route['method'], ['GET', 'HEAD', 'DELETE'], true)) {
                    $parameters = $requestData['parameters'] ?? [];
                } else {
                    $requestBody = $requestData['body'] ?? null;
                }
            }

            $responseData = $this->responseExtractor->extract($route);
            if ($responseData !== null) {
                $responses = $responseData;
            }

            $security = $this->middlewareExtractor->extractRouteSecurity($route);

            $this->builder->addPath(
                path: $route['uri'],
                method: strtolower($route['method']),
                summary: $route['name'] ?? '',
                group: $route['group'] ?? 'General',
                parameters: $parameters,
                requestBody: $requestBody,
                responses: $responses,
                security: $security,
            );
        }

        return $this->builder->build();
    }

    public function writeFiles(): array
    {
        $spec = $this->generate();
        $outputPath = config('compass.output.path', storage_path('app/compass'));
        $files = [];

        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        if (config('compass.output.yaml', true)) {
            $file = $outputPath . '/openapi.yaml';
            (new YamlWriter())->write($spec, $file);
            $files[] = $file;
        }

        if (config('compass.output.json', true)) {
            $file = $outputPath . '/openapi.json';
            (new JsonWriter())->write($spec, $file);
            $files[] = $file;
        }

        return $files;
    }
}
