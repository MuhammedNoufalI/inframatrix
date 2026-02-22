<?php
/**
 * InfraMatrix Deployment Diagnostic Script
 * Use this to verify headers passed by CloudPanel/Nginx/Varnish
 */

header('Content-Type: text/plain');

echo "========================================\n";
echo " INFRAMATRIX DEPLOYMENT DIAGNOSTICS      \n";
echo "========================================\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Interface: " . php_sapi_name() . "\n\n";

echo "--- CORE HEADERS ---\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "\n";
echo "HTTP_X_FORWARDED_FOR: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A') . "\n";
echo "HTTP_X_REAL_IP: " . ($_SERVER['HTTP_X_REAL_IP'] ?? 'N/A') . "\n";
echo "HTTPS (Server Var): " . ($_SERVER['HTTPS'] ?? 'N/A') . "\n";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n";
echo "REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'N/A') . "\n\n";

echo "--- REQUEST INFO ---\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n\n";

echo "--- SUGGESTED FIXES ---\n";
if (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    echo "[PASS] Secure connection detected.\n";
} else {
    echo "[FAIL] Secure connection NOT detected by PHP. Check Proxy headers.\n";
}

if (str_contains($_SERVER['DOCUMENT_ROOT'] ?? '', '/public')) {
    echo "[PASS] Document Root correctly ends in /public.\n";
} else {
    echo "[FAIL] Document Root should point to the /public directory.\n";
}
