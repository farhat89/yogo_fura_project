<?php
/**
 * Delete User API Endpoint - TheFresh.Corner Admin
 *
 * This file handles AJAX POST requests to delete a user from the system.
 *
 * Key Features:
 * - Only accessible by authenticated admin users (session check).
 * - Validates user ID and prevents deletion of own account or last admin.
 * - Deletes related orders and (optionally) products for vendors.
 * - Uses database transactions for safe multi-step deletion.
 * - Returns JSON response indicating success or error for frontend handling.
 *
 * Maintenance Notes:
 * - Consider handling related data (orders/products) more granularly for business needs.
 * - Extend protection logic as needed for new roles or relationships.
 * - Ensure error messages do not leak sensitive database details in production.
 * - Audit deletion actions for accountability.
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
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = intval($input['id'] ?? 0);

    // Validate user ID
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        exit;
    }

    // Check if it's the last admin (optional protection)
    if ($user['role'] == 'admin') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetch()['admin_count'];
        
        if ($admin_count <= 1) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete the last admin account']);
            exit;
        }
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Delete user's orders (if any) - you might want to handle this differently
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$user_id]);

        // Delete user's products (if vendor)
        // $stmt = $pdo->prepare("DELETE FROM products WHERE vendor_id = ?");
        // $stmt->execute([$user_id]);

        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>