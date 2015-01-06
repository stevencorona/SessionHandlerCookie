<?php

namespace SessionHandler;

/*
 * Allow session data to be stored in the cookie securely, which scales
 * nicely. 
 *
 *
 * http://php.net/manual/en/class.sessionhandlerinterface.php
 */

class Cookie implements SessionHandlerInterface {

  private $data      = [];
  private $save_path = null;

  private $hash_len;
  private $hash_algo;
  private $hash_secret;

  /**
   * Initializes a new Cookie session handler and configures the hashing algorithm.
   * @param string $hash_secret secret to sign the cookie with
   * @param string $hash_len the length of the hash
   * @param string $hash_algo the algorithm to pass to hash_hmac
   * @return Cookie
   */
  public function __construct($hash_secret=null, $hash_len=128, $hash_algo="sha512") {

    $this->hash_len  = $hash_len;
    $this->hash_algo = $hash_algo;

    // If hash secret is empty, we need to set a default one
    if (empty($hash_secret)) {
      $hash_secret = $this->default_hash_secret();
    }

    $this->hash_secret = $hash_secret;
  }

  /**
   * Calculates the default_hash_secret to use as a fallback if no secret is passed. It's
   * a bad idea to rely on this in production.
   */
  protected function default_hash_secret() {
    // This is not perfect, it's easily leakable to the outside world,
    // but it's predictable and doesn't require much server state. It's a bad
    // idea to depend on this and probably won't work with multiple servers or
    // with multiple PHP-FPM/Apache processes.
    return md5(php_uname() . getmypid());
  }

  /**
   * Called by PHP to initialize the session. This is not needed for cookies, but we
   * store the data anyways.
   * @param string $save_path
   * @param string $name
   */
  public function open($save_path, $name) {
    $this->save_path = $save_path;
    return true;
  }

  /**
   * Called by PHP to read session data.
   * @param string $session_id
   * @return string serialized session data
   */
  public function read($session_id) {

    // Check for the existance of a cookie with the name of the session id
    // Make sure that the cookie is atleast the size of our hash, otherwise it's invalid
    // Return an empty string if it's invalid.
    if (! isset($_COOKIE[$session_id])) return '';

    // We expect the cookie to be base64 encoded, so let's decode it and make sure
    // that the cookie, at a minimum, is longer than our expact hash length. 
    $raw = base64_decode($_COOKIE[$session_id]);
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

    // Return the data, now that it's been verified.
    return (string)$data;

  }

  /**
   * Called by PHP to write out session data
   * @param string $session_id
   * @param string $data
   * @return bool write succeeded
   */
  public function write($id, $data) {

    // Calculate a hash for the data and append it to the end of the data string
    $hash = hash_hmac($this->hash_algo, $data, $this->hash_secret);
    $data .= $hash;

    // Set a cookie with the data
    setcookie($id, base64_encode($data), time()+3600);
  }

  public function destroy($id) {
    setcookie($id, '', time());
  }

  // In the context of cookies, these two methods are unneccessary, but must
  // be implemented as part of the SessionHandlerInterface.
  public function gc($maxlifetime) {}
  public function close() {}

}
