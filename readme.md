# Indos Checker API

[![Tests](https://github.com/RenderbitTechnologies/Indos-Checker-API/actions/workflows/tests.yml/badge.svg)](https://github.com/RenderbitTechnologies/Indos-Checker-API/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/renderbit-technologies/indos-checker-api)](https://packagist.org/packages/renderbit-technologies/indos-checker-api)
[![PHP Version](https://img.shields.io/packagist/php-v/renderbit-technologies/indos-checker-api)](https://packagist.org/packages/renderbit-technologies/indos-checker-api)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A PHP library to check seafarer details from INDOS numbers as issued by the Directorate General of Shipping, Mumbai, India. Queries the DGS eSamudra server and returns validated INDOS records.

## Requirements

- PHP >= 8.1
- [Composer](https://getcomposer.org/)

### Dependencies

| Package | Purpose |
|---------|---------|
| `guzzlehttp/guzzle` ^7.4 | HTTP client for API requests |
| `symfony/dom-crawler` ^6.0 | HTML response parsing |
| `symfony/css-selector` ^6.0 | CSS selector support for DomCrawler |

## Installation

```shell
composer require renderbit-technologies/indos-checker-api
```

## Usage

### Check if an INDOS number is valid

```php
use RenderbitTechnologies\IndosCheckerApi\IndosChecker;

$checker = new IndosChecker();
$checker->checkValid('05LL0262', '14/08/1963');
// true
```

### Retrieve seafarer details

```php
$data = $checker->getData('05LL0262', '14/08/1963');
// [
//     "Name"                => "YADAV SANJEEV",
//     "Date of Birth"       => "14-AUG-1963",
//     "INDoS No."           => "05LL0262",
//     "Passport No."        => "M2069200",
//     "Passport Issue Date" => "15-SEP-2014",
//     "Passport Valid To"   => "14-SEP-2024",
//     "CDC No."             => "MUM 133201",
//     "CDC Issue Date"      => "22-MAY-2015",
//     "CDC Valid To"        => "21-MAY-2025",
//     "CDC Issue Place"     => "Mumbai",
// ]
```

The returned array contains up to 10 fields: Name, Date of Birth, INDoS No., Passport No., Passport Issue Date, Passport Valid To, CDC No., CDC Issue Date, CDC Valid To, and CDC Issue Place. An empty array is returned when no record is found.

### Exception handling

```php
use RenderbitTechnologies\IndosCheckerApi\IndosCheckerException;

try {
    $data = $checker->getData($no, $dob);
} catch (IndosCheckerException $e) {
    // Network or server error — inspect $e->getPrevious() for the Guzzle cause
} catch (\InvalidArgumentException $e) {
    // Bad input: empty INDOS number or wrong date format (must be DD/MM/YYYY)
}
```

- `IndosCheckerException` wraps all Guzzle/HTTP errors (timeouts, connection failures, server errors).
- `\InvalidArgumentException` is thrown for invalid input: empty or whitespace-only INDOS numbers, or DOB not in `DD/MM/YYYY` format.

### Custom HTTP client

Pass a configured `GuzzleHttp\Client` to override timeouts, proxies, etc.:

```php
$client  = new \GuzzleHttp\Client(['timeout' => 5.0]);
$checker = new IndosChecker($client);
```

### Custom endpoint

Override the default DGS eSamudra endpoint (useful for testing):

```php
$checker = new IndosChecker(null, 'http://staging.example.com/indos');
```

## Project Structure

```
Indos-Checker-API/
├── src/
│   ├── IndosChecker.php          # Core class: validation, HTTP request, HTML parsing
│   └── IndosCheckerException.php # Custom exception for API/network errors
├── tests/
│   └── IndosCheckerTest.php      # PHPUnit tests (input validation, parsing, exceptions)
├── .github/
│   ├── workflows/tests.yml       # CI: tests on PHP 8.1–8.4
│   └── dependabot.yml            # Automated dependency updates
├── composer.json
├── phpunit.xml
└── LICENSE                       # MIT
```

## Testing

```shell
composer install
vendor/bin/phpunit
```

## CI

GitHub Actions runs the PHPUnit suite on every push and pull request to `master`, across PHP 8.1, 8.2, 8.3, and 8.4.

## License

MIT &copy; Renderbit Technologies
