<?php
// ============================================================
// CORS HEADERS
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
// GET TOKEN FROM HEADER
// ============================================================
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (empty($authHeader)) {
    echo json_encode([
        'success' => false,
        'message' => 'No token provided'
    ]);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);

// ============================================================
// VERIFY TOKEN (Simple verification)
// ============================================================
$jwtSecret = 'your-super-secret-jwt-key-change-this-in-production';

try {
    // Split token
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        throw new Exception('Invalid token format');
    }
    
    list($header, $payload, $signature) = $parts;
    
    // Verify signature
    $expectedSignature = base64_encode(hash_hmac('sha256', "$header.$payload", $jwtSecret, true));
    
    if ($signature !== $expectedSignature) {
        throw new Exception('Invalid token signature');
    }
    
    // Decode payload
    $payloadData = json_decode(base64_decode($payload), true);
    
    // Check expiry
    if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
        throw new Exception('Token expired');
    }
    
    // ✅ Token valid hai - user data bhejo
    echo json_encode([
        'success' => true,
        'message' => 'Token is valid',
        'user' => [
            'email' => $payloadData['email'] ?? 'N/A',
            'role' => $payloadData['role'] ?? 'Student',
            'userId' => $payloadData['userId'] ?? 'N/A'
        ],
        'tokenPayload' => $payloadData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid token: ' . $e->getMessage()
    ]);
}
?>