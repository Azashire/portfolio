<?php
session_start();
$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]
    );
}

session_destroy();

header('Location: index.php');
exit;
