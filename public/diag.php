<?php
/**
 * Advanced Diagnostic Script for 500 Errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "========================================\n";
echo " INFRAMATRIX DEEP DIAGNOSTICS          \n";
echo "========================================\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Active ENV: " . getenv('APP_ENV') . "\n";
echo "Config ENV: " . (file_exists('../.env') ? 'Found' : 'Missing') . "\n\n";

echo "--- CORE HEADERS ---\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "\n";
echo "HTTPS (Server Var): " . ($_SERVER['HTTPS'] ?? 'N/A') . "\n";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n\n";

echo "--- LARAVEL BOOT CHECK ---\n";
try {
    require __DIR__.'/../vendor/autoload.php';
    echo "[PASS] Autoload found.\n";
    
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "[PASS] bootstrap/app.php loaded.\n";

    echo "--- CLASS SEARCH ---\n";
    if (class_exists(\Illuminate\Http\Request::class)) {
        echo "[INFO] Illuminate\Http\Request exists.\n";
    }
    
    // Check if the constant exists on the request object in this environment
    try {
        if (defined('\Illuminate\Http\Request::HEADER_X_FORWARDED_ALL')) {
             echo "[INFO] Constant HEADER_X_FORWARDED_ALL is defined in Illuminate\Http\Request\n";
        } else {
             echo "[WARN] Constant HEADER_X_FORWARDED_ALL is NOT defined in Illuminate\Http\Request\n";
        }
    } catch (\Throwable $e) {
        echo "[ERROR] Error checking constant: " . $e->getMessage() . "\n";
    }

} catch (\Throwable $e) {
    echo "[FATAL ERROR] " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: \n" . $e->getTraceAsString() . "\n";
}
echo "</pre>";
