# Cookie Session Handler for PHP

This library adds HMAC-Based Cookie sessions to PHP 5.4+

`SessionHandlerCookie` is a short, but useful piece of code that I've decided to open source from my book, [Scaling PHP Applications](http://scalingphpbook.com).

PHP 5.4 gives us the [`SessionHandlerInterface`](http://php.net/manual/en/class.sessionhandlerinterface.php) which allows for custom session handlers to be added very easily. Out of the box, PHP's filesystem-based sessions don't scale horizontally without using a networked file system or switching to Memcached/Redis backed sessions, both of which are slightly complex for the average developer.

As an alternative, I've created `SessionHandlerCookie`. It's easy to use and plug-and-play, as it works transparently with the native PHP session interface, through the `$_SESSION` global variable.

`SessionHandlerCookie` works by storing the session data inside of a cookie in the users web-browser. To prevent tampering, the data is stored with an HMAC to verify it's integrity. 

The default HMAC algorithm used is `sha512`, but since this code uses PHP's [Hash Extension](http://php.net/manual/en/book.hash.php), you can use any hashing algorithm supported.

Additionally, you must change `"secret"` to be your own secret when deploying this code.

## Example Usage

	<?php

	$handler = new SessionHandler\Cookie();
	session_set_save_handler($handler, true);
	session_start();
	
	$_SESSION["foo"] = "bar";

## License

    The MIT License (MIT)

    Copyright (c) 2014 Steve Corona Inc.

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
