<?php
// auth-check.php - Authentication helper functions

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

require_once __DIR__ . '/../config/database.php';

 //Check if user is logged in (any role)
 //Use this for worker pages where any authenticated user is allowed
 
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in');
    }
    
    // Get user from database
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Add initials
        $user['initials'] = strtoupper(
            substr($user['first_name'] ?? 'G', 0, 1) . 
            substr($user['last_name'] ?? 'U', 0, 1)
        );
        
        return $user;
        
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}


//Check if user is farmer (or admin)
//Use this for farmer-only pages
function checkFarmer() {
    checkAuth();
    
    if ($_SESSION['role'] !== 'farmer' && $_SESSION['role'] !== 'admin') {
        throw new Exception('Farmer access required');
    }
    
    return checkAuth();
}

//Check if user is admin only
//Use this for admin-only pages

function checkAdmin() {
    checkAuth();
    
    if ($_SESSION['role'] !== 'admin') {
        throw new Exception('Admin access required');
    }
    
    return checkAuth();
}

//Check if user is worker 
//Use this for worker pages

function checkWorker() {
    checkAuth();
    if ($_SESSION['role'] !== 'worker') {
        throw new Exception('Worker access required');
    }
    return checkAuth();
}
?>