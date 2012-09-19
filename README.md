Prowl-PSR
=========

[![Build Status](https://secure.travis-ci.org/egersdorfer/Prowl-PSR.png)](http://travis-ci.org/egersdorfer/Prowl-PSR)

Independent PHP Prowl package

Version: 1.0

Requirements
------------

php >= 5.3.0
curl

Installation
------------

### Via Composer

To install Prowl-PRS with composer you create a composer.json in your project root and add:

```php
{
    "require": {
        "Zebra/ProwlPHP": ">=1.0.2"
    }
}
```

then run

```php
$ wget -nc http://getcomposer.org/composer.phar
$ php composer.phar install
```

You should now have ProwlPHP installed in vendor/Zebra/Prowl

Include the autoload file in your project. (vendor/composer/autoload.php)


Usage
------------

```php

$conf = array(
	'application' => 'application',
	'key' => '1234567890123456789012345678901234567890',
	'failOnNotAuthorized' => false,
	'subject' => 'subject',
	'message' => 'test a',
	'action' => 'http://example.com',
	'priority' => 2,
	'url' => "https://api.prowlapp.com/publicapi/add"
);
$p = new Prowl($conf);
$p->push();

```
