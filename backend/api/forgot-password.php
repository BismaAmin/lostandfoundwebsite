<?php
// ============================================================
// NO SPACES OR HTML BEFORE THIS LINE!
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

// Check if input is valid
if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit();
}

$email = isset($input['email']) ? trim($input['email']) : '';

// ============================================================
// VALIDATE EMAIL
// ============================================================
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$username = 'root';
$db_password = '';  // XAMPP = empty
$database = 'lost_found_db';

try {
    $conn = new mysqli($host, $username, $db_password, $database);
    
    if ($conn->connect_error) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit();
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit();
}

// ============================================================
// CHECK IF EMAIL EXISTS
// ============================================================
$stmt = $conn->prepare("SELECT id, name FROM Users WHERE email = ? AND status = 'Active'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Security: Don't reveal if email exists
    echo json_encode([
        'success' => true,
        'message' => 'If this email is registered, you will receive a reset link.'
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// ============================================================
// ✅ GENERATE RESET TOKEN
// ============================================================
$token = bin2hex(random_bytes(32));
$hashedToken = password_hash($token, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ============================================================
// ✅ DELETE OLD TOKENS
// ============================================================
$deleteStmt = $conn->prepare("DELETE FROM password_reset WHERE user_id = ?");
$deleteStmt->bind_param("i", $user['id']);
$deleteStmt->execute();
$deleteStmt->close();

// ============================================================
// ✅ STORE NEW TOKEN
// ============================================================
$insertStmt = $conn->prepare("INSERT INTO password_reset (user_id, reset_token, expires_at, used) VALUES (?, ?, ?, 0)");
$insertStmt->bind_param("iss", $user['id'], $hashedToken, $expiresAt);

if (!$insertStmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate reset link'
    ]);
    $insertStmt->close();
    $conn->close();
    exit();
}
$insertStmt->close();
$conn->close();

// ============================================================
// ✅ RESPONSE - NO EMAIL (Link is in the response)
// ============================================================
echo json_encode([
    'success' => true,
    'message' => '✅ Reset link generated! (Check console)',
    'debug_token' => $token,
    'reset_link' => 'http://localhost/lostandfoundt/auth/reset-password.html?token=' . $token
]);
?>