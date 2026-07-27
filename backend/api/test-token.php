<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$username = 'root';
$db_password = '';
$database = 'lost_found_db';

$conn = new mysqli($host, $username, $db_password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Get the token from URL parameter
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    echo json_encode(['error' => 'No token provided']);
    exit;
}

// Check all tokens in database
$stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.reset_token, pr.expires_at, pr.used, u.name, u.email 
                        FROM password_reset pr 
                        JOIN Users u ON pr.user_id = u.id 
                        WHERE pr.used = 0 AND pr.expires_at > NOW()");
$stmt->execute();
$result = $stmt->get_result();

$found = false;
$debug = [];

while ($row = $result->fetch_assoc()) {
    $debug[] = [
        'id' => $row['id'],
        'user' => $row['name'],
        'email' => $row['email'],
        'expires_at' => $row['expires_at'],
        'used' => $row['used'],
        'token_hash' => substr($row['reset_token'], 0, 30) . '...',
        'token_verify' => password_verify($token, $row['reset_token']) ? '✅ MATCHES' : '❌ No match'
    ];
    
    if (password_verify($token, $row['reset_token'])) {
        $found = true;
        $resetRecord = $row;
    }
}
$stmt->close();
$conn->close();

echo json_encode([
    'your_token' => $token,
    'found' => $found,
    'debug_info' => $debug
]);
?>