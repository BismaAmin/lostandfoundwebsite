<?php
// ============================================================
// NO SPACES OR HTML BEFORE THIS LINE!
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$username = 'root';
$db_password = '';
$database = 'lost_found_db';

try {
    $conn = new mysqli($host, $username, $db_password, $database);
    
    if ($conn->connect_error) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit();
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
    exit();
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit();
}

$token = isset($input['token']) ? trim($input['token']) : '';

if (empty($token)) {
    echo json_encode([
        'success' => false,
        'message' => 'Token is required'
    ]);
    exit();
}

// ============================================================
// ✅ VALIDATE TOKEN
// ============================================================
$stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.reset_token, pr.expires_at, pr.used, u.name 
                        FROM password_reset pr 
                        JOIN Users u ON pr.user_id = u.id 
                        WHERE pr.used = 0");
$stmt->execute();
$result = $stmt->get_result();

$valid = false;
$userName = '';

while ($row = $result->fetch_assoc()) {
    // Check if token hasn't expired
    $expiresAt = strtotime($row['expires_at']);
    $now = time();
    
    if ($expiresAt < $now) {
        continue; // Token expired, skip
    }
    
    // Verify the token
    if (password_verify($token, $row['reset_token'])) {
        $valid = true;
        $userName = $row['name'];
        break;
    }
}
$stmt->close();
$conn->close();

// ============================================================
// ✅ RESPONSE
// ============================================================
if ($valid) {
    echo json_encode([
        'success' => true,
        'message' => 'Token is valid',
        'user' => [
            'name' => $userName
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired token'
    ]);
}
?>