# Contributing to Indos Checker API

Thank you for your interest in contributing! This document provides guidelines and information for contributors.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Project Structure](#project-structure)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Pull Request Process](#pull-request-process)
- [Reporting Issues](#reporting-issues)
- [Contributing to Documentation](#contributing-to-documentation)

## Code of Conduct

We expect all contributors to:
- Be respectful and inclusive
- Focus on constructive feedback
- Use welcoming and inclusive language
- Accept responsibility for mistakes

## Getting Started

1. **Fork the repository**
   ```bash
   # Fork via GitHub UI, then clone
   git clone https://github.com/YOUR_USERNAME/Indos-Checker-API.git
   cd Indos-Checker-API
   ```

2. **Add upstream remote**
   ```bash
   git remote add upstream https://github.com/RenderbitTechnologies/Indos-Checker-API.git
   ```

3. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

## Development Setup

### Prerequisites

- **PHP 8.1 or higher**
- **Composer** (latest version)
- **PHPUnit 10.0+** (installed via Composer)

### Installation

```bash
# Clone the repository
git clone https://github.com/RenderbitTechnologies/Indos-Checker-API.git
cd Indos-Checker-API

# Install dependencies
composer install

# Verify installation
composer validate --strict
```

### Useful Commands

```bash
# Run all tests
vendor/bin/phpunit

# Run tests with verbose output
vendor/bin/phpunit --verbose

# Run specific test class
vendor/bin/phpunit --filter IndosCheckerTest

# Run specific test method
vendor/bin/phpunit --filter testGetDataReturnsAllFieldsFromValidHtml

# Check code coverage (requires Xdebug)
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage/
```

## Project Structure

```
Indos-Checker-API/
├── src/
│   ├── IndosChecker.php          # Core class: validation, HTTP, parsing
│   └── IndosCheckerException.php # Custom exception for API errors
├── tests/
│   └── IndosCheckerTest.php      # PHPUnit test suite
├── .github/
│   ├── ISSUE_TEMPLATE/           # Issue templates
│   ├── pull_request_template.md  # PR template
│   ├── workflows/
│   │   └── tests.yml             # CI configuration
│   └── dependabot.yml            # Dependency updates
├── composer.json                 # Dependencies and autoloading
├── phpunit.xml                   # PHPUnit configuration
├── .editorconfig                 # Coding style settings
├── LICENSE                       # MIT License
└── readme.md                     # Documentation
```

## Coding Standards

### PHP Style

- **PHP 8.1+** features encouraged (typed properties, enums, match expressions, etc.)
- **PSR-12** coding style
- **4 spaces** for indentation (not tabs)
- **UTF-8** charset
- **CRLF** line endings

### Naming Conventions

```php
// Classes: PascalCase
class IndosChecker {}

// Methods: camelCase
public function getData(string $no, string $dob): array {}

// Properties: camelCase with visibility
private string $endpoint;

// Constants: UPPER_SNAKE_CASE
private const DEFAULT_ENDPOINT = '...';

// Variables: camelCase
$validData = $checker->getData($no, $dob);
```

### Type Hints

Always use strict types and type declarations:

```php
declare(strict_types=1);

class IndosChecker
{
    public function getData(string $no, string $dob): array
    {
        // Implementation
    }

    private function validate(string $no, string $dob): void
    {
        // Validation logic
    }
}
```

### Error Handling

- Use specific exception types (e.g., `InvalidArgumentException`, custom `IndosCheckerException`)
- Chain exceptions when wrapping: `new IndosCheckerException($message, 0, $previous)`
- Validate inputs early and fail fast
- Return empty arrays for "not found" states (don't throw)

### Documentation

- Add PHPDoc blocks for public methods
- Comment complex logic
- Keep README up-to-date with examples

## Testing

### Test Structure

Tests are in `tests/IndosCheckerTest.php` and follow this organization:

1. **Input validation tests** - Ensure proper argument validation
2. **HTML parsing tests** - Verify response parsing logic
3. **Exception handling tests** - Confirm proper error wrapping
4. **Constructor/DI tests** - Test dependency injection

### Writing Tests

```php
<?php

namespace RenderbitTechnologies\IndosCheckerApi\Tests;

use PHPUnit\Framework\TestCase;
use RenderbitTechnologies\IndosCheckerApi\IndosChecker;

class IndosCheckerTest extends TestCase
{
    public function testYourNewFeature(): void
    {
        // Arrange
        $checker = new IndosChecker();

        // Act
        $result = $checker->yourMethod();

        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Test Guidelines

- **One assertion per test** when possible
- **Descriptive test names** explaining the scenario
- **Arrange-Act-Assert** pattern
- **Mock external dependencies** (Guzzle, DGS API)
- **Test edge cases** (empty inputs, invalid formats, network errors)
- **Cover both success and failure paths**

### Mocking

Use Guzzle's `MockHandler` for API responses:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

$mock = new MockHandler([
    new Response(200, [], '<html>...</html>'),
]);
$handler = HandlerStack::create($mock);
$client = new Client(['handler' => $handler]);
$checker = new IndosChecker($client);
```

### Running Tests Locally

```bash
# Full test suite
vendor/bin/phpunit

# With coverage (requires Xdebug)
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage/

# Stop on first failure
vendor/bin/phpunit --stop-on-failure

# Run only unit tests
vendor/bin/phpunit --testsuite IndosChecker
```

## Submitting Changes

### Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/) format:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation only
- `style:` Formatting (no logic change)
- `refactor:` Code restructure (no feature/fix)
- `perf:` Performance improvement
- `test:` Add/update tests
- `build:` Build system/dependencies
- `ci:` CI/CD changes
- `chore:` Maintenance tasks

**Examples:**
```bash
# Feature
git commit -m "feat: add batch validation method for multiple INDOS numbers"

# Bug fix
git commit -m "fix: handle empty response from DGS server gracefully"

# Documentation
git commit -m "docs: add custom endpoint usage example"

# Test
git commit -m "test: add coverage for network timeout scenarios"
```

### Branch Naming

Use descriptive kebab-case branch names:

```bash
feature/add-batch-validation
fix/handle-empty-response
docs/update-readme-examples
test/add-network-timeout-coverage
refactor/extract-parsing-logic
```

## Pull Request Process

### Before Submitting

1. **Ensure tests pass**
   ```bash
   vendor/bin/phpunit
   ```

2. **Validate composer.json**
   ```bash
   composer validate --strict
   ```

3. **Check code style** (follow PSR-12)
   - Use an IDE with PHP linting
   - Ensure 4-space indentation
   - No trailing whitespace

4. **Update documentation** (if needed)
   - Update README if adding features
   - Add PHPDoc blocks for new methods
   - Update CHANGELOG if significant

### PR Guidelines

1. **Fill out the PR template completely**
   - Description of changes
   - Type of change
   - Related issues
   - Testing details
   - PHP version compatibility

2. **Keep PRs focused**
   - One logical change per PR
   - Avoid mixing unrelated changes
   - Small, reviewable chunks preferred

3. **Add tests for new functionality**
   - Unit tests for new methods
   - Edge case coverage
   - Error handling scenarios

4. **Respond to review feedback**
   - Address comments promptly
   - Ask clarifying questions
   - Make requested changes in new commits

### Review Process

- All PRs require at least one approval
- CI must pass (PHP 8.1, 8.2, 8.3, 8.4)
- Address all review comments
- Squash merge preferred for clean history

## Reporting Issues

### Bug Reports

Use the [Bug Report template](https://github.com/RenderbitTechnologies/Indos-Checker-API/issues/new?template=bug_report.yml) and include:

- **PHP version** and **OS**
- **Steps to reproduce**
- **Expected vs actual behavior**
- **Error messages/stack traces**
- **INDOS number tested** (if shareable)
- **Custom configuration** details

### Feature Requests

Use the [Feature Request template](https://github.com/RenderbitTechnologies/Indos-Checker-API/issues/new?template=feature_request.yml) and include:

- **Problem statement**
- **Proposed solution**
- **Use case**
- **Alternatives considered**

### Security Vulnerabilities

**Do NOT open public issues for security vulnerabilities.**

Instead, email: security@renderbit.com

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

## Contributing to Documentation

### README Updates

- Keep examples clear and working
- Test all code snippets before committing
- Follow existing formatting style
- Update table of contents if adding sections

### PHPDoc Blocks

```php
/**
 * Retrieve seafarer details from INDOS number.
 *
 * @param string $no    INDOS number (e.g., "05LL0262")
 * @param string $dob   Date of birth in DD/MM/YYYY format (e.g., "14/08/1963")
 * @return array Associative array of seafarer details, empty if not found
 * @throws \InvalidArgumentException For invalid input
 * @throws IndosCheckerException For network/API errors
 */
public function getData(string $no, string $dob): array
{
    // Implementation
}
```

## Getting Help

- **Questions:** Open a [Discussion](https://github.com/RenderbitTechnologies/Indos-Checker-API/discussions)
- **Issues:** Use appropriate issue template
- **Email:** soham@renderbit.com

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).

---

Thank you for contributing! 🎉
