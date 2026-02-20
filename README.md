# Compass 🧭

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sdekkers/compass.svg?style=flat-square)](https://packagist.org/packages/sdekkers/compass)
[![PHP Version](https://img.shields.io/packagist/php-v/sdekkers/compass.svg?style=flat-square)](https://packagist.org/packages/sdekkers/compass)
[![License](https://img.shields.io/packagist/l/sdekkers/compass.svg?style=flat-square)](LICENSE)

**Zero-config OpenAPI documentation for Laravel.** No annotations. No docblocks. Just reads your code.

## Features

- 🔍 Automatic OpenAPI 3.0 spec generation from Laravel routes
- 📋 Schema extraction from FormRequest validation rules
- 📦 Response schemas from JsonResource classes
- 🔐 Auth detection from middleware (Passport, Sanctum, Bearer)
- 🏷️ Module-based route grouping via controller namespaces
- 🖥️ Built-in Swagger UI at `/docs`
- 📄 YAML and JSON output

## Requirements

- PHP 8.4+
- Laravel 11 or 12

## Installation

```bash
composer require sdekkers/compass
```

## Quick Start

```bash
php artisan compass:generate
```

Your `openapi.yaml` and `openapi.json` are now in `storage/app/compass/`. Visit `/docs` to see the Swagger UI.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=compass-config
```

Key options in `config/compass.php`:

| Option | Description |
|--------|-------------|
| `title` | API documentation title |
| `version` | API version string |
| `servers` | Server URLs for the spec |
| `routes.prefixes` | Route prefixes to include (default: `['api']`) |
| `routes.exclude_patterns` | Glob patterns to exclude |
| `grouping.enabled` | Auto-group by module namespace |
| `ui.enabled` | Enable/disable Swagger UI |
| `ui.path` | URL path for Swagger UI (default: `docs`) |

## How It Works

Compass inspects your Laravel application and extracts:

- **Routes** — HTTP method, URI, parameters, and middleware from the router
- **Request validation** — FormRequest `rules()` are mapped to OpenAPI request body schemas
- **Response schemas** — JsonResource `toArray()` keys become response schemas
- **Authentication** — Middleware like `auth:api` and `auth:sanctum` map to security schemes
- **Grouping** — Controllers in `App\Modules\{Name}\Controllers\` are automatically tagged

### Validation Rule Mapping

| Laravel Rule | OpenAPI |
|-------------|---------|
| `string` | `{type: "string"}` |
| `integer` | `{type: "integer"}` |
| `boolean` | `{type: "boolean"}` |
| `email` | `{type: "string", format: "email"}` |
| `uuid` | `{type: "string", format: "uuid"}` |
| `date` | `{type: "string", format: "date"}` |
| `max:255` | `{maxLength: 255}` |
| `min:1` | `{minimum: 1}` |
| `in:a,b,c` | `{enum: ["a","b","c"]}` |
| `nullable` | `{nullable: true}` |

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
