<?php
// No spaces or HTML before this line!

// Force JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the input
$input = json_decode(file_get_contents('php://input'), true);

// Check if input is valid
if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit();
}

// Extract data
$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

// ============================================================
// ✅ SECURITY FIX: ALWAYS set role to 'Student'
// NEVER trust the role sent from the frontend!
// ============================================================
$role = 'Student'; // ✅ FORCED - Users can ONLY register as Student

// Validate
if (strlen($name) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Name must be at least 2 characters'
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

if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$username = 'root';
$db_password = '';  // XAMPP = empty, MAMP = 'root'
$database = 'lost_found_db';

try {
    // Connect to database
    $conn = new mysqli($host, $username, $db_password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit();
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered. Please login.'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // ============================================================
    // ✅ INSERT with role = 'Student' (SAFE)
    // The role from frontend is IGNORED!
    // ============================================================
    $stmt = $conn->prepare("INSERT INTO Users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'Active')");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please login to continue.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => $role // ✅ Always returns 'Student'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>