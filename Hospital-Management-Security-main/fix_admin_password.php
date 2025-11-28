<?php
// fix_admin_password.php

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Fixing Admin Password</h2>";

// Get current admin password
$query = "SELECT username, password FROM admintb WHERE username='admin'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_array($result);

echo "<p>Current password in DB: " . htmlspecialchars($row['password']) . "</p>";
echo "<p>Password length: " . strlen($row['password']) . "</p>";

// Check if already hashed
if (strlen($row['password']) == 60 && substr($row['password'], 0, 4) == '$2y$') {
    echo "<p style='color:green;'>✓ Admin password is already hashed!</p>";
    echo "<p>You can login with password: <strong>admin123</strong></p>";
} else {
    echo "<p style='color:red;'>✗ Admin password is PLAINTEXT!</p>";
    echo "<p>Original password: <strong>" . htmlspecialchars($row['password']) . "</strong></p>";
    
    // Hash it
    $hashed = password_hash($row['password'], PASSWORD_BCRYPT);
    
    $update_query = "UPDATE admintb SET password=? WHERE username='admin'";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, "s", $hashed);
    mysqli_stmt_execute($stmt);
    
    echo "<p style='color:green;'>✓ Password has been hashed!</p>";
    echo "<p>You can now login with password: <strong>" . htmlspecialchars($row['password']) . "</strong></p>";
}

mysqli_close($con);

echo "<hr>";
echo "<p style='color:red;font-weight:bold;'>DELETE THIS FILE NOW!</p>";
?>
