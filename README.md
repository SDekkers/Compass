# Compass 🧭

Zero-config OpenAPI documentation generator for Laravel modular applications.

No annotations. No docblocks. Just reads your code.

## Install

```bash
composer require sdekkers/compass
```

## Generate

```bash
php artisan compass:generate
```

That's it. Your `openapi.yaml` and `openapi.json` are in `storage/app/compass/`.

## Swagger UI

Visit `/docs` in your browser. Enabled by default.

## What It Reads

- **Routes** — method, URI, middleware, controller from Laravel's router
- **Request validation** — FormRequest `rules()` → OpenAPI schemas
- **Response schemas** — JsonResource `toArray()` keys
- **Auth middleware** — Passport, Sanctum, Bearer → security schemes
- **Module grouping** — `App\Modules\{Name}\Controllers\{Sub}\Controller` → tagged groups

## Laravel Validation → OpenAPI

| Rule | OpenAPI |
|------|---------|
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

## Config

```bash
php artisan vendor:publish --tag=compass-config
```

See `config/compass.php` for all options: title, version, servers, route filtering, grouping overrides, Swagger UI settings.

## Requirements

- PHP 8.4+
- Laravel 11 or 12

## License

MIT
