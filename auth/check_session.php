<?php
// auth/check_session.php
// Returns the currently logged-in user from the PHP session.
// Includes phone so settings pages can populate the phone field on load.

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_path'     => '/FellahDz/'
    ]);
}

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['authenticated' => false]);
    exit;
}

// Fetch the full row from DB so we always have the latest phone/name
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=fellah_dz;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $stmt = $pdo->prepare(
        'SELECT id, email, first_name, last_name, phone, role, status FROM users WHERE id = ?'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        echo json_encode(['authenticated' => false]);
        exit;
    }

    // Keep session in sync with DB
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];

    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id'         => $user['id'],
            'email'      => $user['email'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'phone'      => $user['phone'] ?? '',
            'role'       => $user['role'],
            'status'     => $user['status'],
            'initials'   => strtoupper(
                substr($user['first_name'] ?? 'G', 0, 1) .
                substr($user['last_name']  ?? 'U', 0, 1)
            )
        ]
    ]);

} catch (PDOException $e) {
    // Fall back to session data if DB is unavailable
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id'         => $_SESSION['user_id'],
            'email'      => $_SESSION['email']      ?? '',
            'first_name' => $_SESSION['first_name'] ?? '',
            'last_name'  => $_SESSION['last_name']  ?? '',
            'phone'      => '',   // not in session — will be empty until page refreshes
            'role'       => $_SESSION['role']       ?? '',
            'status'     => 'active',
            'initials'   => strtoupper(
                substr($_SESSION['first_name'] ?? 'G', 0, 1) .
                substr($_SESSION['last_name']  ?? 'U', 0, 1)
            )
        ]
    ]);
}
?>