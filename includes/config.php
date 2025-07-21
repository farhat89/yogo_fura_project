<?php
/**
 * Global Configuration - TheFresh.Corner
 *
 * This file sets up global configuration constants and session management for the application.
 *
 * Key Features:
 * - Starts PHP session for authentication and user state.
 * - Defines BASE_URL for consistent URL generation across the app.
 * - Defines UPLOADS_DIR for file upload and storage management.
 *
 * Maintenance Notes:
 * - Update BASE_URL if deploying to a different domain or environment.
 * - Ensure UPLOADS_DIR is secure and writable by the web server.
 * - Add additional configuration constants here as the project grows.
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-07
 */

session_start();
define('BASE_URL', 'http://localhost/TheFresh_Corner/');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');