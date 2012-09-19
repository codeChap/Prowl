<?php

	use Zebra\Prowl\Prowl;

	class ProwlTest extends PHPUnit_Framework_TestCase {

		function test_a()
		{
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
		}

		function test_b()
		{
			$conf = array(
				'application' => 'application',
				'key' => '1234567890123456789012345678901234567890',
				'failOnNotAuthorized' => false,
				'subject' => 'subject'
			);

			$p = new Prowl($conf);
			$p->push('test b');
		}

		function test_c()
		{
			$p = new Zebra\Prowl();
			$p->setApplication('application');
			$p->setKey('1234567890123456789012345678901234567890');
			$p->setFailOnNotAuthorized(false);
			$p->setSubject('subject');
			$p->setMessage('test c');
			$p->push();
		}

		function test_d()
		{
			$p = new Prowl();
			$p->setApplication('application');
			$p->setKey('1234567890123456789012345678901234567890');
			$p->setFailOnNotAuthorized(false);
			$p->setSubject('subject');
			$p->setMessage('test c');
			$p->push();
		}
	}
