<?php
// Environment detection
function is_localhost() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    $localhost_patterns = [
        'localhost',
        '127.0.0.1',
        '::1',
        '192.168.',
        '10.0.',
        '172.16.', '172.17.', '172.18.', '172.19.', '172.20.',
        '172.21.', '172.22.', '172.23.', '172.24.', '172.25.',
        '172.26.', '172.27.', '172.28.', '172.29.', '172.30.', '172.31.'
    ];
    
    foreach ($localhost_patterns as $pattern) {
        if (strpos($host, $pattern) !== false || strpos($server_name, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

// Define environment
define('IS_LOCALHOST', is_localhost());
define('IS_PRODUCTION', !IS_LOCALHOST);

// Database configuration
define('DB_SERVER','localhost');
define('DB_USER','root');
define('DB_PASS','steven1234');
define('DB_NAME', 'myhmsdb');

// Database connection
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);

// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>