<?php
/**
 * Security Headers Configuration
 * Enforces HTTPS in production while allowing HTTP on localhost for development
 * 
 * LOCALHOST OPTIONS:
 * 1. HTTP (Default - No Setup Required):
 *    - Use: http://localhost/...
 *    - Works immediately, no certificate warnings
 *    - Security headers still work
 * 
 * 2. HTTPS with Trusted Certificate (Optional - For Testing):
 *    - Use: https://localhost/... (after setup)
 *    - Requires SSL certificate setup (see SETUP_SSL_LOCALHOST.md)
 *    - No certificate warnings (green padlock)
 *    - Full HTTPS testing capabilities
 *    - HSTS and secure cookies enabled
 */

// Detect if running on localhost
function is_localhost() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check for localhost, 127.0.0.1, or local IP addresses
    $localhost_patterns = [
        'localhost',
        '127.0.0.1',
        '::1',
        '192.168.',
        '10.0.',
        '172.16.',
        '172.17.',
        '172.18.',
        '172.19.',
        '172.20.',
        '172.21.',
        '172.22.',
        '172.23.',
        '172.24.',
        '172.25.',
        '172.26.',
        '172.27.',
        '172.28.',
        '172.29.',
        '172.30.',
        '172.31.'
    ];
    
    foreach ($localhost_patterns as $pattern) {
        if (strpos($host, $pattern) !== false || strpos($server_name, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

// Check if HTTPS is being used
function is_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// Force HTTPS redirect in production (not on localhost)
function enforce_https() {
    if (!is_localhost() && !is_https()) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url", true, 301);
        exit();
    }
}

// Set security headers
function set_security_headers() {
    // Enforce HTTPS in production
    enforce_https();
    
    // X-Frame-Options: Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // X-Content-Type-Options: Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // X-XSS-Protection: Enable XSS filter (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer-Policy: Control referrer information
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content-Security-Policy: Basic CSP (can be customized)
    // Note: This is a basic CSP. You may need to adjust based on your application's needs
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdnjs.cloudflare.com https://maxcdn.bootstrapcdn.com https://stackpath.bootstrapcdn.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://maxcdn.bootstrapcdn.com https://stackpath.bootstrapcdn.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: https:; connect-src 'self';");
    
    // Permissions-Policy: Control browser features
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // HSTS (HTTP Strict Transport Security)
    if (is_https()) {
        if (is_localhost()) {
            // On localhost: Shorter max-age for testing (1 hour = 3600 seconds)
            // No preload on localhost
            header('Strict-Transport-Security: max-age=3600');
        } else {
            // In production: Long max-age (1 year = 31536000 seconds)
            // includeSubDomains: Apply to all subdomains
            // preload: Allow inclusion in HSTS preload lists
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
    
    // Remove server information
    header_remove('X-Powered-By');
    
    // Set secure session cookie parameters if session is started
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure flag when HTTPS is used
        $secure = is_https();
        
        ini_set('session.cookie_httponly', '1');
        if ($secure) {
            ini_set('session.cookie_secure', '1');
        }
        ini_set('session.cookie_samesite', 'Strict');
    }
}

// Auto-set headers when this file is included
set_security_headers();

