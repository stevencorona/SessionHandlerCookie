<?php

namespace SessionHandler\Storage;

class SecureCookie
{
    private $hash_len;
    private $hash_algo;
    private $hash_secret;

    /**
     * Initializes a new Secure Cookie.
     *
     * @param string $secret secret to sign the cookie with
     * @param string $len the length of the hash
     * @param string $algo the algorithm to pass to hash_hmac
     *
     * @return SecureCookie
     */
    public function __construct($secret = null, $len = 128, $algo = "sha512")
    {
        $this->hash_len = $len;
        $this->hash_algo = $algo;

        // If hash secret is empty, we need to set a default one
        if (empty($secret)) {
            $secret = $this->default_hash_secret();
        }

        $this->hash_secret = $secret;
    }

    /**
     * Calculates the default_hash_secret to use as a fallback if no secret is passed. It's
     * a bad idea to rely on this in production.
     */
    protected function default_hash_secret()
    {
        // This is not good, it's easily leakable to the outside world,
        // but it's predictable and doesn't require much server state. It's a bad
        // idea to depend on this and probably won't work with multiple servers or
        // with multiple PHP-FPM/Apache processes.
        return md5(php_uname() . getmypid());
    }

    public function get($name, $default = null)
    {
        if (!$this->has($name)) {
            return $default;
        }

        $raw = base64_decode($_COOKIE[$name]);

        // Cookie should be at least the size of the hash length.
        // If it's not, we can just bail out
        if (strlen($raw) < $this->hash_len) {
            return $default;
        }

        // The cookie data contains the actual data w/ the hash concatonated to the end,
        // since the hash is a fixed length, we can extract the last hash_length chars
        // to get the hash.
        $hash = substr($raw, strlen($raw) - $this->hash_len, $this->hash_len);
        $data = substr($raw, 0, -($this->hash_len));

        // Calculate what the hash should be, based on the data. If the data has not been
        // tampered with, $hash and $hash_calculated will be the same
        $hash_calculated = hash_hmac($this->hash_algo, $data, $this->hash_secret);

        // If we calculate a different hash, we can't trust the data.
        if ($hash_calculated !== $hash) {
            throw new HashMismatchException();
        }

        return $data;
    }

    /**
     * Checks if a cookie exists or not.
     *
     * @param string $name name of the cookie to check for
     *
     * @return true if the cookie exists, false otherwise
     */
    public function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Set cookie.
     *
     * @param string $name
     * @param string $value
     * @param int|null $minutes If null, we set the value to `0` (which means the cookie lives
     *                          within this client session), otherwise the timestamp is calculated
     *                          as `current time + $minutes * 60`
     * @return bool
     */
    public function make($name, $value, $minutes = null)
    {
        // Calculate a hash for the data and append it to the end of the data string
        $hash = hash_hmac($this->hash_algo, $value, $this->hash_secret);
        $value .= $hash;

        // Set a cookie with the data
        $ttl = $minutes === null ? 0 : time() + ($minutes * 60);
        return setcookie($name, base64_encode($value), $ttl, '/', null, null, true);
    }

    public function forget($name)
    {
        return setcookie($name, '', 1, '/');
    }

}

class HashMismatchException extends \Exception
{

}
