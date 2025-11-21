<?php
session_start();
$con=mysqli_connect("localhost","root","","myhmsdb");
if(isset($_POST['adsub'])){
    $username = $_POST['username1'];
    $password = $_POST['password2'];
    $username = mysqli_real_escape_string($con, $username);

    $query = "SELECT * FROM admintb WHERE username='$username'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        if ($row['lockout_time'] && strtotime($row['lockout_time']) > time()) {
            echo("<script>alert('Your account is locked. Please try again after 5 minutes.');
                  window.location.href = 'index.php';</script>");
        } elseif ($row['password'] == $password) {
            // Successful login
            mysqli_query($con, "UPDATE admintb SET login_attempts = 0, lockout_time = NULL WHERE username = '$username'");

            $_SESSION['username'] = $row['username'];
            header("Location:admin-panel1.php");
        } else {
            // Incorrect password
            $login_attempts = $row['login_attempts'] + 1;
            if ($login_attempts >= 5) {
                $lockout_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                mysqli_query($con, "UPDATE admintb SET login_attempts = $login_attempts, lockout_time = '$lockout_time' WHERE username = '$username'");
                echo("<script>alert('You have exceeded the maximum number of login attempts. Your account is locked for 5 minutes.');
                      window.location.href = 'index.php';</script>");
            } else {
                mysqli_query($con, "UPDATE admintb SET login_attempts = $login_attempts WHERE username = '$username'");
                echo("<script>alert('Invalid Username or Password. Try Again!');
                      window.location.href = 'index.php';</script>");
            }
        }
    } else {
        // User not found
        echo("<script>alert('Invalid Username or Password. Try Again!');
              window.location.href = 'index.php';</script>");
    }
}
?>