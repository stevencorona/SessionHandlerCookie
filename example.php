<?php

$handler = new SessionHandler\Cookie;
session_set_save_handler($handler, true);
session_start();