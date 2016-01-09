<?php

namespace SessionHandler;

/*
 * Allow session data to be stored in the cookie securely, which scales
 * nicely. Cookie storage is abstracted out to SecureCookie.php
 *
 *
 * http://php.net/manual/en/class.sessionhandlerinterface.php
 */

use SessionHandler\Storage\HashMismatchException;

class Cookie implements \SessionHandlerInterface {

  private $storage = null;

  /**
   * Initializes a new Cookie session handler and configures the hashing algorithm.
   * @param string $hash_secret secret to sign the cookie with
   * @param string $hash_len the length of the hash
   * @param string $hash_algo the algorithm to pass to hash_hmac
   * @return Cookie
   */
  public function __construct($secret=null) {
    $this->storage = new Storage\SecureCookie($secret);
  }
  
  /**
   * Set a custom storage handler
   * @param Storage\SecureCookie $storage storage handler
   */
  public function setStorage($storage) {
    $this->storage = $storage;
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
    if (! $this->storage->has($session_id)) return '';

    try {
      $data = $this->storage->get($session_id);
    } catch (HashMismatchException $ex) {
      $data = '';
    } catch (\Exception $e) {
      // not expected, but we need to make sure we return a string value
      // in every case...
      $data = '';
    }

    // Return the data, now that it's been verified.
    return $data;
  }

  /**
   * Called by PHP to write out session data
   * @param string $session_id
   * @param string $data
   * @return bool write succeeded
   */
  public function write($session_id, $data) {
    return $this->storage->make($session_id, $data);
  }

  /**
   * Called by PHP to destroy the session
   * @param string $session_id
   * @return bool true success
   */
  public function destroy($session_id) {
    $resPhpSess = $this->storage->forget(session_name());
    $resData = $this->storage->forget($session_id);
    return $resPhpSess && $resData;
  }

  // In the context of cookies, these three methods are unneccessary, but must
  // be implemented as part of the SessionHandlerInterface.
  public function open($save_path, $name) { return true; }
  public function gc($maxlifetime) { return true; }
  public function close() { return true; }

}
