Prowl-PSR
=========

[![Build Status](https://secure.travis-ci.org/egersdorfer/Prowl-PSR.png)](http://travis-ci.org/egersdorfer/Prowl-PSR)

Independent PHP Prowl package

Version: 1.0

Installation
------------

@todo


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
