<?php
// 2. Enable Error Reporting (Log errors, don't show to user)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 3. Include Security Headers & CSRF Helper
require_once(__DIR__ . '/include/security_headers.php');
require_once('csrf_helper.php');

// 1. Start Session (Must be AFTER security headers)
session_start();

// 4. Database Connection
try {
    // Check your port. If using XAMPP default, remove ":3307".
    $con = mysqli_connect("localhost", "root", "", "myhmsdb"); 
} catch (mysqli_sql_exception $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("System Error: Database connection failed. Please try again later.");
}

// ADMIN LOGIN LOGIC
if(isset($_POST['adsub'])){
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('<script>alert("CSRF token validation failed!"); window.location.href = "index.php";</script>');
    }

    $username = $_POST['username1'];
    $password = $_POST['password2'];

    try {
        // Prepare query to fetch user details
        $query = "SELECT * FROM admintb WHERE username=?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            // Check if account is locked
            if ($row['lockout_time'] && strtotime($row['lockout_time']) > time()) {
                echo("<script>alert('Your account is locked. Please try again after 5 minutes.');
                      window.location.href = 'index.php';</script>");
            } 
            // Check Password (In a real app, use password_verify())
           elseif (password_verify($password, $row['password'])) {
                // Reset login attempts on success
                $reset_stmt = mysqli_prepare($con, "UPDATE admintb SET login_attempts = 0, lockout_time = NULL WHERE username = ?");
                mysqli_stmt_bind_param($reset_stmt, "s", $username);
                mysqli_stmt_execute($reset_stmt);

                $_SESSION['username'] = $username;
                header("Location:admin-panel1.php");
                exit();
            } 
            // Wrong Password Logic
            else {
                $login_attempts = $row['login_attempts'] + 1;

                if ($login_attempts >= 5) {
                    $lockout_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                    
                    $update_stmt = mysqli_prepare($con, "UPDATE admintb SET login_attempts = ?, lockout_time = ? WHERE username = ?");
                    mysqli_stmt_bind_param($update_stmt, "iss", $login_attempts, $lockout_time, $username);
                    mysqli_stmt_execute($update_stmt);

                    echo("<script>alert('You have exceeded the maximum number of login attempts. Your account is locked for 5 minutes.');
                          window.location.href = 'index.php';</script>");
                } else {
                    $update_stmt = mysqli_prepare($con, "UPDATE admintb SET login_attempts = ? WHERE username = ?");
                    mysqli_stmt_bind_param($update_stmt, "is", $login_attempts, $username);
                    mysqli_stmt_execute($update_stmt);

                    echo("<script>alert('Invalid Username or Password. Try Again! (Attempt $login_attempts/5)');
                          window.location.href = 'index.php';</script>");
                }
            }
        } else {
            echo("<script>alert('Invalid Username or Password. Try Again!');
              window.location.href = 'index.php';</script>");
        }

    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        echo "<script>alert('System Error: Login failed.'); window.location.href = 'index.php';</script>";
    }
}

// UPDATE PAYMENT STATUS
if(isset($_POST['update_data']))
{
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('<script>alert("CSRF token validation failed!"); window.location.href = "index.php";</script>');
    }
    $contact = $_POST['contact'];
    $status = $_POST['status'];
    try {
        $query = "update appointmenttb set payment=? where contact=?;";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ss", $status, $contact);
        $result = mysqli_stmt_execute($stmt);
        if($result)
            header("Location:updated.php");
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        echo "<script>alert('System Error: Update failed.');</script>";
    }
}


// DISPLAY DOCTORS FUNCTION
function display_docs()
{
    global $con;
    try {
        $query = "select username from doctb";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_array($result))
        {
            $name = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            echo '<option value="'.$name.'">'.$name.'</option>';
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        echo '<option disabled>Unable to load doctors</option>';
    }
}


// ADD DOCTOR 
if(isset($_POST['doc_sub']))
{
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('<script>alert("CSRF token validation failed!"); window.location.href = "index.php";</script>');
    }
    $name = $_POST['name'];
    try {
        $query = "insert into doctb(username) values(?)"; // Changed 'name' to 'username' to match schema
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $name);
        $result = mysqli_stmt_execute($stmt);
        if($result)
            header("Location:adddoc.php");
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        echo "<script>alert('System Error: Unable to add doctor.');</script>";
    }
}
?>
