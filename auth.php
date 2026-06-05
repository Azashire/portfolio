<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_connecte'])) {
    $retour = urlencode($_SERVER['REQUEST_URI']);
    header('Location: login.php?retour=' . $retour);
    exit;
}
