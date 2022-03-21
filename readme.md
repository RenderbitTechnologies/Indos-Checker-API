# Indos Checker API

An API to check for the validity of INDOS numbers as issued by the Directorate General of Shipping, Mumbai, India.

## Usage

```shell
$ composer require renderbit/indos-checker-api
```

```php
IndosChecker::checkValid('05LL0262', '14/08/1963');
true
```

```php
IndosChecker::getData('05LL0262', '14/08/1963');
[
    "Name" => "YADAV SANJEEV",
    "Date of Birth" => "14-AUG-1963",
    "INDoS No." => "05LL0262",
    "Passport No." => "M2069200",
    "Passport Issue Date" => "15-SEP-2014",
    "Passport Valid To" => "14-SEP-2024",
    "CDC No." => "MUM 133201",
    "CDC Issue Date" => "22-MAY-2015",
    "CDC Valid To" => "21-MAY-2025",
    "CDC Issue Place" => "Mumbai",
]
```

&copy; Renderbit Technologies 2022. All rights reserved.
