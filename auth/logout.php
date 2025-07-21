<?php
/**
 * Logout Handler - TheFresh.Corner
 *
 * This file securely logs out the current user by destroying the session and redirecting to the login page.
 *
 * Key Features:
 * - Destroys all session data to log out the user.
 * - Sets a success message for user feedback.
 * - Redirects to the login page after logout.
 *
 * Maintenance Notes:
 * - Ensure session destruction is complete for all authentication flows.
 * - Extend logic if supporting multi-session or token-based authentication.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07
 */
require_once '../includes/config.php';
session_destroy();
$_SESSION['message'] = "Logged out successfully.";
$_SESSION['message_type'] = "success";
header("Location: login.php");
exit;