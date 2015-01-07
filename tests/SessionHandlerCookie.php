<?php

require 'vendor/autoload.php';

class SessionHandlerCookieTest extends PHPUnit_Framework_TestCase {

	function testCanConstruct() {
		$sh = new SessionHandler\Cookie;
		$this->assertInstanceOf('SessionHandler\Cookie', $sh);
	}
	
}