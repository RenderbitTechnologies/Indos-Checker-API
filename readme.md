# Indos Checker API

An API to check for the validity of INDOS numbers as issued by the Directorate General of Shipping, Mumbai, India.

## Usage

```shell
$ composer require renderbit/indos-checker-api
```

```php
$checker = new \Renderbit\IndosCheckerApi\IndosChecker();

$checker->checkValid('05LL0262', '14/08/1963');
// true
```

```php
$checker->getData('05LL0262', '14/08/1963');
// [
//     "Name"              => "YADAV SANJEEV",
//     "Date of Birth"     => "14-AUG-1963",
//     "INDoS No."         => "05LL0262",
//     "Passport No."      => "M2069200",
//     "Passport Issue Date" => "15-SEP-2014",
//     "Passport Valid To" => "14-SEP-2024",
//     "CDC No."           => "MUM 133201",
//     "CDC Issue Date"    => "22-MAY-2015",
//     "CDC Valid To"      => "21-MAY-2025",
//     "CDC Issue Place"   => "Mumbai",
// ]
```

### Exception handling

```php
use Renderbit\IndosCheckerApi\IndosCheckerException;

try {
    $data = $checker->getData($no, $dob);
} catch (IndosCheckerException $e) {
    // Network or server error — inspect $e->getPrevious() for the Guzzle cause
} catch (\InvalidArgumentException $e) {
    // Bad input: empty INDOS number or wrong date format (must be DD/MM/YYYY)
}
```

### Custom HTTP client

Pass a configured `GuzzleHttp\Client` to override timeouts, proxies, etc.:

```php
$client  = new \GuzzleHttp\Client(['timeout' => 5.0]);
$checker = new \Renderbit\IndosCheckerApi\IndosChecker($client);
```

&copy; Renderbit Technologies 2022. All rights reserved.
