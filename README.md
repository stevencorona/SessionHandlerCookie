SessionHandlerCookie
====================

`SessionHandlerCookie` is a short, but useful piece of code that I've decided to open source from my book, [Scaling PHP Applications](http://scalingphpbook.com).

PHP 5.4 gives us the [`SessionHandlerInterface`](http://php.net/manual/en/class.sessionhandlerinterface.php) which allows for custom session handlers to be added very easily. Out of the box, PHP's filesystem-based sessions don't scale horizontally without using a networked file system or switching to Memcached/Redis backed sessions, both of which are slightly complex for the average developer.

As an alternative, I've created `SessionHandlerCookie`. It's easy to use and plug-and-play, as it works transparently with the native PHP session interface, through the `$_SESSION` global variable.

`SessionHandlerCookie` works by storing the session data inside of a cookie in the users web-browser. To prevent tampering, the data is stored with an HMAC to verify it's integrity. The default HMAC algorithm used is `sha512`, but since this code uses PHP's [Hash Extension](http://php.net/manual/en/book.hash.php), you can configure it to use any hashing algorithm you'd like by modifying `HASH_ALGO` and `HASH_LEN`.

Additionally, you must change `HASH_SECRET` to be your own secret when deploying this code.