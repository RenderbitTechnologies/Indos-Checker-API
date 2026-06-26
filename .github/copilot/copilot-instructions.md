# GitHub Copilot Instructions

## Priority Guidelines

When generating code for this repository:

1. **Version Compatibility**: Always detect and respect the exact versions of languages, frameworks, and libraries used in this project
2. **Context Files**: Prioritize patterns and standards defined in the `.github/copilot` directory
3. **Codebase Patterns**: When context files don't provide specific guidance, scan the codebase for established patterns
4. **Architectural Consistency**: Maintain our monolithic, single-class library architectural style and established boundaries
5. **Code Quality**: Prioritize maintainability, testability, and security in all generated code

## Technology Version Detection

Before generating code, scan the codebase to identify:

1. **Language Versions**: Detect the exact versions of programming languages in use
   - PHP ^8.1 (required by composer.json)
   - Features: typed properties, constructor property promotion, enums, match expressions, union types, named arguments, null safe operator
   - Never use language features beyond PHP 8.1

2. **Framework Versions**: Identify the exact versions of all frameworks
   - `guzzlehttp/guzzle` ^7.4 — HTTP client for API requests
   - `symfony/dom-crawler` ^6.0 — HTML response parsing
   - `symfony/css-selector` ^6.0 — CSS selector support for DomCrawler
   - Respect version constraints when generating code

3. **Library Versions**: Note the exact versions of key libraries and dependencies
   - PHPUnit ^10.0 (dev dependency for testing)
   - Generate code compatible with these specific versions
   - Never use APIs or features not available in the detected versions

## Context Files

Prioritize the following files in `.github/copilot` directory (if they exist):

- **architecture.md**: System architecture guidelines
- **tech-stack.md**: Technology versions and framework details
- **coding-standards.md**: Code style and formatting standards
- **folder-structure.md**: Project organization guidelines
- **exemplars.md**: Exemplary code patterns to follow

## Codebase Scanning Instructions

When context files don't provide specific guidance:

1. Identify similar files to the one being modified or created
2. Analyze patterns for:
   - Naming conventions (PascalCase for classes, camelCase for methods/properties, UPPER_SNAKE_CASE for constants)
   - Code organization (single class per file, PSR-4 autoloading under `RenderbitTechnologies\IndosCheckerApi` namespace)
   - Error handling (use `\InvalidArgumentException` for input validation, custom `IndosCheckerException` for API/network errors)
   - Documentation style (PHPDoc blocks for public methods, inline comments for complex logic)
   - Testing patterns (Guzzle MockHandler, Arrange-Act-Assert, grouped test sections with comments)
   
3. Follow the most consistent patterns found in the codebase
4. When conflicting patterns exist, prioritize patterns in newer files or files with higher test coverage
5. Never introduce patterns not found in the existing codebase

## Code Quality Standards

### Maintainability
- Write self-documenting code with clear naming
- Follow the naming and organization conventions evident in the codebase (PascalCase classes, camelCase methods, UPPER_SNAKE_CASE constants)
- Follow established patterns for consistency
- Keep functions focused on single responsibilities
- Limit function complexity and length to match existing patterns

### Security
- Follow existing patterns for input validation (early validation before network calls)
- Apply the same sanitization techniques used in the codebase
- Use parameterized queries matching existing patterns
- Follow established authentication and authorization patterns
- Handle sensitive data according to existing patterns

### Testability
- Follow established patterns for testable code (dependency injection via constructor)
- Match dependency injection approaches used in the codebase (constructor with nullable defaults)
- Apply the same patterns for managing dependencies
- Follow established mocking and test double patterns (Guzzle MockHandler for HTTP)
- Match the testing style used in existing tests (grouped test sections with descriptive comments)

## Documentation Requirements

- Follow the exact documentation format found in the codebase
- Match the PHPDoc style and completeness of existing comments
- Document parameters, returns, and exceptions in the same style
- Follow existing patterns for usage examples
- Match class-level documentation style and content

## Testing Approach

### Unit Testing
- Match the exact structure and style of existing unit tests
- Follow the same naming conventions for test classes and methods (`test` prefix, camelCase method names)
- Use the same assertion patterns found in existing tests (`assertSame`, `assertArrayHasKey`, `assertCount`, `assertIsArray`)
- Apply the same mocking approach used in the codebase (Guzzle MockHandler + HandlerStack)
- Follow existing patterns for test isolation

### Integration Testing
- Follow the same integration test patterns found in the codebase
- Match existing patterns for test data setup and teardown
- Use the same approach for testing component interactions
- Follow existing patterns for verifying system behavior

### Test-Driven Development
- Follow TDD patterns evident in the codebase
- Match the progression of test cases seen in existing code
- Apply the same refactoring patterns after tests pass

## Technology-Specific Guidelines

### PHP Guidelines
- Detect and adhere to the specific PHP version in use (minimum 8.1)
- Use PHP 8.1+ features: typed properties, nullable types, constructor property promotion
- Follow strict typing patterns (`declare(strict_types=1)` where applicable)
- Match the exact coding style (PSR-12, 4-space indentation)
- Use the same error handling patterns (specific exception types, exception chaining)
- Follow the same namespace patterns (`RenderbitTechnologies\IndosCheckerApi`)
- Use the same autoloading approach (PSR-4)

### Guzzle HTTP Guidelines
- Follow the exact Guzzle patterns found in the codebase
- Match constructor injection patterns for `GuzzleHttp\Client`
- Use the same error handling for `GuzzleException`
- Follow the same request/response handling patterns
- Apply the same mocking approach with `MockHandler` and `HandlerStack`

### Symfony DomCrawler Guidelines
- Follow the exact DomCrawler usage patterns found in the codebase
- Match the CSS selector patterns used for HTML parsing
- Use the same approach for iterating and filtering DOM elements
- Follow the same text extraction and cleaning patterns

## Version Control Guidelines

- Follow Conventional Commits format as applied in the codebase
- Match existing patterns for commit message types (feat, fix, docs, style, refactor, perf, test, build, ci, chore)
- Follow the same approach for documenting breaking changes
- Use the same branch naming conventions (kebab-case: feature/, fix/, docs/, test/, refactor/)

## General Best Practices

- Follow naming conventions exactly as they appear in existing code
- Match code organization patterns from similar files
- Apply error handling consistent with existing patterns
- Follow the same approach to testing as seen in the codebase
- Match documentation patterns from existing code
- Use the same approach to configuration as seen in the codebase

## Project-Specific Guidance

- Scan the codebase thoroughly before generating any code
- Respect existing architectural boundaries without exception
- Match the style and patterns of surrounding code
- When in doubt, prioritize consistency with existing code over external best practices
- This is a PHP library — maintain the simple, focused design
- Keep the library lightweight and dependency-minimal
- Follow the established pattern of constructor injection for testability
- Maintain backward compatibility unless explicitly told otherwise
