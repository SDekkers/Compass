# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [v0.1.0] - 2025-02-20

### Added
- Zero-config OpenAPI 3.0 spec generation from Laravel routes
- Automatic schema extraction from FormRequest validation rules
- Response schema extraction from JsonResource classes
- Authentication detection from middleware (Passport, Sanctum, Bearer)
- Module-based route grouping via controller namespaces
- Built-in Swagger UI at `/docs`
- YAML and JSON output formats
- `php artisan compass:generate` command
- Configurable route filtering and exclusion patterns
- Server configuration support

[v0.1.0]: https://github.com/SDekkers/Compass/releases/tag/v0.1.0
