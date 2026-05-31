<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth-check.php';

header('Content-Type: application/json');

try {
    $user = checkFarmer();
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => (int)$user['id'],
            'name' => trim($user['first_name'] . ' ' . $user['last_name']),
            'email' => $user['email'],
            'role' => $user['role'],
            'initials' => $user['initials']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>