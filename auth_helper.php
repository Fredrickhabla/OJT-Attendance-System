<?php
function generateRandomToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

function setRememberMeCookie($identifier, $token) {
    $cookieValue = $identifier . ':' . $token;
    setcookie("rememberme", $cookieValue, time() + (30 * 24 * 60 * 60), "/", "", false, true); // HttpOnly
}

function parseRememberMeCookie($cookie) {
    $parts = explode(':', $cookie);
    return count($parts) === 2 ? $parts : [null, null];
}
