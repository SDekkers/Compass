# Contributing to Compass

Thanks for your interest in contributing! Here's how to get started.

## Development Setup

```bash
git clone git@github.com:SDekkers/Compass.git
cd Compass
composer install
```

## Running Tests

```bash
vendor/bin/pest
```

## Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Write tests for your changes
4. Make sure all tests pass (`vendor/bin/pest`)
5. Commit with a clear message
6. Push and open a pull request

## Coding Standards

- Follow PSR-12
- Use `declare(strict_types=1)` in all PHP files
- Add return types to all methods
- Write tests for new features and bug fixes

## Bug Reports

Please include:
- PHP and Laravel version
- Steps to reproduce
- Expected vs actual behavior

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
