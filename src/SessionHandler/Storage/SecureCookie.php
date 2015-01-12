<?php

namespace SessionHandler\Storage;

class SecureCookie {

	private $hash_len;
	private $hash_algo;
	private $hash_secret;

	public function __construct($hash_secret=null, $hash_len=128, $hash_algo="sha512") {
		$this->hash_len  = $hash_len;
    	$this->hash_algo = $hash_algo;

    	// If hash secret is empty, we need to set a default one
    	if (empty($hash_secret)) {
      	$hash_secret = $this->default_hash_secret();
    	}

    	$this->hash_secret = $hash_secret;
	}

	public function has($key) {

	}

	public function get($name, $default=null) {

	$raw = base64_decode($this->storage->get($session_id));
    if (strlen($raw) < $this->hash_len) return '';

    // The cookie data contains the actual data w/ the hash concatonated to the end,
    // since the hash is a fixed length, we can extract the last HMAC_LENGTH chars
    // to get the hash.
    $hash = substr($raw, strlen($raw)-$this->hash_len, $this->hash_len);
    $data = substr($raw, 0, -($this->hash_len));

    // Calculate what the hash should be, based on the data. If the data has not been
    // tampered with, $hash and $hash_calculated will be the same
    $hash_calculated = hash_hmac($this->hash_algo, $data, $this->hash_secret);

    // If we calculate a different hash, we can't trust the data. Return an empty string.
    if ($hash_calculated !== $hash) return '';

	}

	public function make($name, $value, $minutes=0, $path=null, $domain=null, $secure=false, $httponly=true) {
    // Calculate a hash for the data and append it to the end of the data string
    $hash = hash_hmac($this->hash_algo, $data, $this->hash_secret);
    $data .= $hash;

    // Set a cookie with the data
    setcookie($session_id, base64_encode($data), time()+3600);
	}

	public function forget($name) {
	setcookie($session_id, '', time());
	}

	/**
	* Calculates the default_hash_secret to use as a fallback if no secret is passed. It's
	* a bad idea to rely on this in production.
	*/
	protected function default_hash_secret() {
	// This is not good, it's easily leakable to the outside world,
	// but it's predictable and doesn't require much server state. It's a bad
	// idea to depend on this and probably won't work with multiple servers or
	// with multiple PHP-FPM/Apache processes.
	return md5(php_uname() . getmypid());
	}

}