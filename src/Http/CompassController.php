<?php

declare(strict_types=1);

namespace Compass\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class CompassController extends Controller
{
    public function __invoke(): Response
    {
        $specUrl = route('compass.spec');

        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>API Documentation</title>
            <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
        </head>
        <body>
            <div id="swagger-ui"></div>
            <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
            <script>
                SwaggerUIBundle({
                    url: "{$specUrl}",
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
                    layout: "BaseLayout"
                });
            </script>
        </body>
        </html>
        HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function spec(): JsonResponse
    {
        $path = config('compass.output.path', storage_path('app/compass')) . '/openapi.json';

        if (! file_exists($path)) {
            return new JsonResponse(['error' => 'Run php artisan compass:generate first'], 404);
        }

        $content = json_decode(file_get_contents($path), true);

        return new JsonResponse($content);
    }
}
