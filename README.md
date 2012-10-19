Prowl-PSR
=========

[![Build Status](https://secure.travis-ci.org/egersdorfer/Prowl-PSR.png)](http://travis-ci.org/egersdorfer/Prowl-PSR)

Simple Independent PHP Prowl package

Version: 1.0.0

Prowl is the Growl client for iOS. Push to your iPhone, iPod touch, or iPad notifications from a Mac or Windows computer, or from a multitude of apps and services. Easily integrate the Prowl API into your applications. Visit Prowl app for more information.

Requirements
------------

php >= 5.3.0 and curl

Installation
------------

### Via Composer

To install Prowl-PRS with composer you create a composer.json in your project root and add:

```php
{
    "require": {
        "Zebra/Prowl":"dev-master"
    }
}
```

then run

```
$ wget -nc http://getcomposer.org/composer.phar
$ php composer.phar install
```

You should now have Prowl installed in vendor/Zebra/Prowl

Include the autoload file in your project. (vendor/autoload.php)

More info can be found at http://getcomposer.org

Usage
------------

### Example A:

```php

	require "vendor/autoload.php";

	use Zebra\Prowl\Prowl;

	$conf = array(
		'application' => 'testApp',
		'key' => '1234567890123456789012345678901234567890', // Enter your key from prowlApp here.
		'failOnNotAuthorized' => false,
		'subject' => 'testing a',
		'message' => 'testing one two three',
		'action' => 'http://example.com',
		'priority' => 2
	);

	$p = new Prowl($conf);
	$p->push();

```

### Example B:

```php

	require "vendor/autoload.php";

	use Zebra\Prowl\Prowl;

	$p = new Prowl();
	$p->setApplication('testApp');
	$p->setKey('1234567890123456789012345678901234567890');
	$p->setFailOnNotAuthorized(false);
	$p->setSubject('testing b');
	$p->setMessage('test b');
	$p->push();

```

You can also set the message to the following type of symbols.
Hello<br/>"Derrick" &lt; @ åäöâ ©


Functions
---------
####$setAction()
Sets an action url that can be called from prowl on your phone.

####setApplication()
Sets the application name.

####setDebug()
Turns curl verbose mode on.

####setFailOnNotAuthorized()
If the api key used is not authorised throw an error.

####setKey()
Sets the api key to use, can also take an array or keys eg: array(key1, key2, key3).

####setMessage()
Sets the message.

####setPriority()
Set the message priority.

####setUrl()
Sets the url, should never this this.

####setSubject()
Sets the message subject.

####push()
Pushes the message to your device, you can enter the message here as well: $p->push('a message');
