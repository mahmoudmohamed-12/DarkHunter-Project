<?php
// ctf_session.php

if (session_status() === PHP_SESSION_NONE) {

  session_name("DARKHUNTER_CTF");

  session_set_cookie_params([
    'path' => '/DarkHunter/CTF/'
  ]);

  session_start();
}