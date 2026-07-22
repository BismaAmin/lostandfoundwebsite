<?php
// ============================================================
// CORS HEADERS
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// ✅ DATABASE CONNECTION
// ============================================================
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email)) {
    echo json_encode([
        'exists' => false,
        'message' => 'Email is required'
    ]);
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode([
            'exists' => true,
            'message' => 'Email already registered'
        ]);
    } else {
        echo json_encode([
            'exists' => false,
            'message' => 'Email available'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'exists' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>