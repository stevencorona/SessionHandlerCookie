# Cookie Session Handler for PHP

This library adds HMAC-Based Cookie sessions to PHP 5.4+

Cookie Session Handler is a short, but useful piece of code that I've decided to open source from my book, [Scaling PHP Applications](http://scalingphpbook.com).

Sessions are a major source of scaling pains in PHP. By default, session data is stored on the filesystem in PHP, which doesn't scale horizontally as you add more servers (without sticky sessions or NFS). Typically, the way we solve this is by moving sessions to the database or memcached/redis. This punts the problem, but can cause high database load.

## Session Data in the Cookie

What if we could store the session data in the cookie? It'd -easily- solve the scaling problem, but you'd have to worry about data tampering— remember, cookie data is not sercure and can be modified by the user.

We solve the data integrity problem the same way as many other popular frameworks (i.e, Rails) by storing the cookie data with an HMAC token.

### How does it work?

PHP 5.4 adds the [`SessionHandlerInterface`](http://php.net/manual/en/class.sessionhandlerinterface.php) which allows for custom PHP session handlers.

It's easy to use and plug-and-play and it works transparently with the native session interface, through the `$_SESSION` global variable.

## HMAC

This library uses [PHP's Hash Extension](http://php.net/manual/en/book.hash.php) (bundled with PHP as of 5.1.2). By default, it uses `sha512`, but you can change it to any [hashing alogrithm supported](http://php.net/manual/en/function.hash-algos.php).

To make this all work, you need to provide a secret that's used for the HMAC. By default, a very weak and predictable secret is used, and you should change it to your own secret.

## Example Usage

	<?php

	$secret = "deadc0de";
	
	$handler = new SessionHandler\Cookie($secret);
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
