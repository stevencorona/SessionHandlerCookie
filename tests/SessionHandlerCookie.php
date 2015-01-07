<?php

require 'vendor/autoload.php';

class SessionHandlerCookieTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {
		$sh = new SessionHandler\Cookie;
		$this->assertInstanceOf('SessionHandler\Cookie', $sh);
	}
	
}