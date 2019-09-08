
# BLM file format reader

[![Build Status](https://travis-ci.org/stilliard/blmreader.svg)](https://travis-ci.org/stilliard/blmreader)

This php composer package provides a simple way to read BLM files for use with rightmove files

## Install
```bash
composer require stilliard/blmreader 1.0.1
```

## Example usage
```php
$blm = new \BLM\Reader(dirname(__FILE__)  . '/test.blm');
var_dump($blm->toArray());
```
