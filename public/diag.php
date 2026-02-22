<?php
/**
 * Ultimate Diagnostic Script for 500 Errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><body style='font-family: monospace; padding: 20px; line-height: 1.5; background: #1a1a1a; color: #00ff00;'>";
echo "<h1>INFRAMATRIX ULTIMATE DIAGNOSTICS</h1>";

echo "<h2>1. Environment Checks</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo ".env exists: " . (file_exists('../.env') ? 'YES' : 'NO') . "<br>";
echo "vendor exists: " . (is_dir('../vendor') ? 'YES' : 'NO') . "<br>";
echo "storage writable: " . (is_writable('../storage') ? 'YES' : 'NO') . "<br>";

echo "<h2>2. Header Inspection</h2>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'N/A') . "<br>";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "<br>";

echo "<h2>3. Fatal Error Log (Last 20 lines)</h2>";
$logPath = '../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -20);
    echo "<div style='background: #000; padding: 10px; border: 1px solid #333;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
    echo "</div>";
} else {
    echo "Log file not found at $logPath<br>";
    // Check directory permissions
    echo "Log directory writable: " . (is_writable('../storage/logs') ? 'YES' : 'NO') . "<br>";
}

echo "<h2>4. Bootstrap Test</h2>";
try {
    require __DIR__.'/../vendor/autoload.php';
    echo "[PASS] Autoload loaded.<br>";
    
    // Explicitly load Dotenv to see what's in .env
    if (file_exists('../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
        $dotenv->load();
        echo "[PASS] Dotenv loaded. APP_ENV is: " . $_ENV['APP_ENV'] . "<br>";
    }

    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "[PASS] bootstrap/app.php loaded.<br>";
    
    // Try to resolve the Kernel but don't handle request yet
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "[PASS] Kernel resolved.<br>";

} catch (\Throwable $e) {
    echo "<h3 style='color: #ff0000;'>[FATAL] " . $e->getMessage() . "</h3>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Trace snippet:<br><small>" . nl2br(htmlspecialchars(substr($e->getTraceAsString(), 0, 500))) . "...</small>";
}

echo "</body></html>";
