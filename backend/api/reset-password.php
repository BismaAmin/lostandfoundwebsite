<?php
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

$conn = new mysqli($host, $username, $db_password, $database);

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
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
$newPassword = isset($input['password']) ? $input['password'] : '';

// ============================================================
// VALIDATE INPUT
// ============================================================
if (empty($token)) {
    echo json_encode([
        'success' => false,
        'message' => 'Reset token is required'
    ]);
    exit();
}

if (empty($newPassword) || strlen($newPassword) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit();
}

// ============================================================
// ✅ CHECK TOKEN - Get ALL tokens and verify
// ============================================================
$stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.reset_token, pr.expires_at, pr.used, u.id as user_id, u.name, u.email 
                        FROM password_reset pr 
                        JOIN Users u ON pr.user_id = u.id 
                        WHERE pr.used = 0");
$stmt->execute();
$result = $stmt->get_result();

$found = false;
$resetRecord = null;

while ($row = $result->fetch_assoc()) {
    // Check if token hasn't expired
    $expiresAt = strtotime($row['expires_at']);
    $now = time();
    
    if ($expiresAt < $now) {
        continue; // Token expired, skip
    }
    
    // Verify the token
    if (password_verify($token, $row['reset_token'])) {
        $found = true;
        $resetRecord = $row;
        break;
    }
}
$stmt->close();

if (!$found) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired reset token'
    ]);
    $conn->close();
    exit();
}

// ============================================================
// ✅ UPDATE PASSWORD
// ============================================================
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE Users SET password_hash = ? WHERE id = ?");
$updateStmt->bind_param("si", $hashedPassword, $resetRecord['user_id']);

if (!$updateStmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update password'
    ]);
    $updateStmt->close();
    $conn->close();
    exit();
}
$updateStmt->close();

// ============================================================
// ✅ MARK TOKEN AS USED
// ============================================================
$markStmt = $conn->prepare("UPDATE password_reset SET used = 1 WHERE id = ?");
$markStmt->bind_param("i", $resetRecord['id']);
$markStmt->execute();
$markStmt->close();

// ============================================================
// ✅ RESPONSE
// ============================================================
echo json_encode([
    'success' => true,
    'message' => 'Password reset successful! You can now login with your new password.'
]);

$conn->close();
?>