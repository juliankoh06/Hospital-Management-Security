<?php
//  all existing plain text passwords hashed
// script deleted after successful execution

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

echo "<h2>Password Migration - Converting Plain Text to Bcrypt Hashes</h2>";
echo "<hr>";

// Migrate all doctor passwords
echo "<h3>Doctor Accounts:</h3>";
$query = "SELECT username, password FROM doctb";
$result = mysqli_query($con, $query);

$doctor_count = 0;
$doctor_migrated = 0;

while($row = mysqli_fetch_array($result)) {
    $doctor_count++;
    $username = $row['username'];
    $old_password = $row['password'];
    
    // Check password is NOT already hashed (bcrypt hashes start with $2y$)
    if(substr($old_password, 0, 4) !== '$2y$') {
        // Hash plain text password
        $hashed = password_hash($old_password, PASSWORD_BCRYPT);
        
        // Update database with hashed password using prepared statement
        $stmt = $con->prepare("UPDATE doctb SET password=? WHERE username=?");
        $stmt->bind_param("ss", $hashed, $username);
        $stmt->execute();
        
        echo "Successfully hashed password for doctor: <strong>$username</strong><br>";
        $doctor_migrated++;
    } else {
        echo "Skipped doctor: <strong>$username</strong> (password already hashed)<br>";
    }
}

echo "<p><strong>Doctor Summary:</strong> Migrated $doctor_migrated out of $doctor_count accounts.</p>";
echo "<hr>";

// Migrate all patient passwords
echo "<h3>Patient Accounts:</h3>";
$query = "SELECT pid, fname, lname, email, password, cpassword FROM patreg";
$result = mysqli_query($con, $query);

$patient_count = 0;
$patient_migrated = 0;

while($row = mysqli_fetch_array($result)) {
    $patient_count++;
    $pid = $row['pid'];
    $fname = $row['fname'];
    $lname = $row['lname'];
    $email = $row['email'];
    $old_password = $row['password'];
    $old_cpassword = $row['cpassword'];
    
    // Check password is NOT already hashed
    if(substr($old_password, 0, 4) !== '$2y$') {
        // Hash both password fields
        $hashed_password = password_hash($old_password, PASSWORD_BCRYPT);
        $hashed_cpassword = password_hash($old_cpassword, PASSWORD_BCRYPT);
        
        // Update database using prepared statement
        $stmt = $con->prepare("UPDATE patreg SET password=?, cpassword=? WHERE pid=?");
        $stmt->bind_param("ssi", $hashed_password, $hashed_cpassword, $pid);
        $stmt->execute();
        
        echo "Successfully hashed password for patient: <strong>$fname $lname</strong> ($email)<br>";
        $patient_migrated++;
    } else {
        echo "Skipped patient: <strong>$fname $lname</strong> (password already hashed)<br>";
    }
}

echo "<p><strong>Patient Summary:</strong> Migrated $patient_migrated out of $patient_count accounts.</p>";
echo "<hr>";

echo "<h2>Migration Complete!</h2>";
echo "<p><strong>Total Accounts Processed:</strong> " . ($doctor_count + $patient_count) . "</p>";
echo "<p><strong>Total Passwords Migrated:</strong> " . ($doctor_migrated + $patient_migrated) . "</p>";
echo "<br>";
echo "<div style='background-color: #ff0000; color: white; padding: 15px; border-radius: 5px;'>";
echo "<h3> CRITICAL SECURITY WARNING </h3>";
echo "<p><strong>DELETE THIS FILE IMMEDIATELY!</strong></p>";
echo "<p>This migration script contains sensitive database operations and must be removed from the server to prevent unauthorized access.</p>";
echo "</div>";

mysqli_close($con);
?>
