<?php
/**
 * Database Connection - TheFresh.Corner
 *
 * This file establishes a PDO connection to the MySQL database for all application data operations.
 *
 * Key Features:
 * - Uses PDO for secure and flexible database access.
 * - Sets error mode to exception for robust error handling.
 * - Centralizes connection logic for easy maintenance and reuse.
 *
 * Maintenance Notes:
 * - Update credentials and host if deploying to a different environment.
 * - Consider using environment variables for sensitive credentials in production.
 * - Extend PDO options for advanced features (e.g., persistent connections, charset).
 *
 * @author  TheFresh.Corner Dev Team
 * @version 1.0
 * @since   2025-01-01
 */

$host = 'localhost';
$dbname = 'yogofura';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}