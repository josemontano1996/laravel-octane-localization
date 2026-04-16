# Contributing to Laravel Octane Localization

Thank you for considering contributing to Laravel Octane Localization! This document outlines the process for contributing to this project.

## Code of Conduct

Be respectful and constructive. We're all here to build better software together.

## How Can I Contribute?

### Reporting Bugs

Before creating a bug report, please check existing issues to avoid duplicates.

When creating a bug report, include:
- A clear and descriptive title
- Steps to reproduce the issue
- Expected behavior vs actual behavior
- Your environment (PHP version, Laravel version, Octane/Swoole version if applicable)
- Code samples or test cases that demonstrate the issue

### Suggesting Enhancements

Enhancement suggestions are welcome! Please provide:
- A clear description of the enhancement
- Use cases and examples
- Why this would be useful for other users
- Any potential drawbacks or concerns

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Follow the development setup** below
3. **Make your changes** following our coding standards
4. **Add tests** for any new functionality
5. **Ensure all tests pass**. Run the full test suite using `composer test`.
6. **Update documentation** if needed (README, docblocks, etc.)
7. **Clearly state breaking changes** in the PR description if your changes affect existing behavior
8. **Submit your pull request** - the [PR template](.github/PULL_REQUEST_TEMPLATE.md) will guide you through the required information

## Development Setup

### Prerequisites

- PHP 8.4 or higher
- Composer
- Git

### Setup

1. Clone your fork:
   ```bash
   git clone https://github.com/YOUR-USERNAME/laravel-octane-localization.git
   cd laravel-octane-localization
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. You're ready to develop!

## Development Workflow

### Running Tests

Run the full test suite:
```bash
composer test
```

Run tests with coverage:
```bash
./vendor/bin/pest --coverage
```

Run specific test file:
```bash
./vendor/bin/pest tests/Unit/Services/LocalizationManagerTest.php
```

### Code Style

We use Laravel Pint for code formatting. Run it before committing:
```bash
composer lint
```

Pint will automatically fix code style issues.

### Static Analysis

We use PHPStan for static analysis:
```bash
composer analyse
```

Your code must pass PHPStan analysis at the configured level with zero errors.

### Quality Checklist

Before submitting a PR, ensure:
- [ ] All tests pass: `composer test`
- [ ] Code style is clean: `composer lint`
- [ ] Static analysis passes: `composer analyse`
- [ ] New features have tests
- [ ] Documentation is updated if needed
- [ ] **No breaking changes introduced (or documented if intentional)**
- [ ] Commit messages are clear and descriptive

## Testing Guidelines

### Test Structure

- **Unit tests**: Test individual classes/methods in isolation
- **Feature tests**: Test integration between components
- **Use descriptive test names**: `it('accepts any non-empty locale when supported_locales is empty (open mode)')`

### Test Requirements

- All new features must include tests
- Bug fixes should include regression tests
- Tests should be clear and focused on a single behavior
- Use Pest's `describe()` and nested `it()` for organization

## Coding Standards

### General Principles

- Follow PSR-12 coding standards (enforced by Pint)
- Write clear, self-documenting code
- Add docblocks for public methods with `@param` and `@return` tags
- Use type hints everywhere (parameters, return types, properties)
- Keep methods small and focused on a single responsibility

## Documentation

### README Updates

Update README.md if your changes:
- Add new features or APIs
- Change existing behavior
- Require new configuration

### Docblocks

All public methods require docblocks with:
```php
/**
 * Brief description of what the method does.
 *
 * Longer explanation if needed, including concurrency notes.
 *
 * @param  string  $param  Parameter description
 * @return string  Return value description
 */
```

- Modifying configuration options

## Commit Messages

Write clear, descriptive commit messages:

```
Add support for dynamic locale sources

- Allow getSupportedLocales() to return empty array for open mode
- Update validation logic to handle both whitelist and open modes
- Add tests for unconfigured supported_locales
```

- Use present tense ("Add feature" not "Added feature")
- First line should be a clear summary (50 chars or less)
- Add details in the body if needed
- Reference issue numbers if applicable (`Fixes #123`)
- **If introducing breaking changes**, prefix the commit with `BREAKING:` and explain the impact:
  ```
  BREAKING: Change setLocale validation behavior
  
  - Now requires supported_locales config when using whitelist mode
  - Empty/unconfigured supported_locales now accepts any locale (open mode)
  - This may affect existing applications that relied on previous behavior
  
  Migration: Set supported_locales in config/app.php to maintain strict validation
  ```

## Questions?

Feel free to open an issue for questions or clarifications about contributing!

## License

By contributing to Laravel Octane Localization, you agree that your contributions will be licensed under the same license as the project.
