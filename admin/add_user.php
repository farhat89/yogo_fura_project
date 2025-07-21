<?php
/**
 * Add User API Endpoint - TheFresh.Corner Admin
 *
 * This file handles AJAX POST requests to create new users in the system.
 * 
 * Key Features:
 * - Only accessible by authenticated admin users (session check).
 * - Validates all required fields, email format, role, and status.
 * - Checks for duplicate email before insertion.
 * - Hashes user password securely using PHP's password_hash.
 * - Inserts new user record into the database with current timestamp.
 * - Returns JSON response indicating success or error for frontend handling.
 * 
 * Maintenance Notes:
 * - Extend validation as needed for new user fields.
 * - Ensure error messages do not leak sensitive database details in production.
 * - Keep role and status lists in sync with business logic.
 * - Consider logging failed attempts for audit purposes.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 */

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['full_name'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Validate inputs
    if (empty($email) || empty($name) || empty($password) || empty($role) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Validate role
    $valid_roles = ['customer', 'vendor', 'admin'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        exit;
    }

    // Validate status
    $valid_statuses = ['approved', 'pending', 'rejected'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Check if username already exists
    // $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    // $stmt->execute([$username]);
    // if ($stmt->fetch()) {
    //     echo json_encode(['success' => false, 'message' => 'Username already exists']);
    //     exit;
    // }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (email, name, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$email, $name, $hashed_password, $role, $status]);

        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>