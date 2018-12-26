reversegeocode
================

This is a very small library to find your address by geo latitude / longitude.

## Usage

```php
<?php

namespace Fieldhousen\ReverseGeocode;

require_once 'vendor/autoload.php';

$geocoder = new ReverseGeocode(':key');

echo '<pre>';
print_r($geocoder->geocode(':lat', ':lng'));
```

It returns:

```php
Array
(
    [street] => *
    [city] => *
    [country] => *
)
```
