<?php
// auth/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_path' => '/FellahDz/'
    ]);
}

header('Content-Type: application/json');

session_unset();
session_destroy();

echo json_encode(["success" => true]);
?>