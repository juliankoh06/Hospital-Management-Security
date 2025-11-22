<?php
// 1. Start Session (Must be first for headers and CSRF)
session_start();

// 2. Include Security Headers (From 'security-fixes')
// Uses __DIR__ for a robust absolute path.
require_once(__DIR__ . '/include/security_headers.php');

// 3. Include CSRF Helper (From 'HEAD')
require_once('csrf_helper.php');

$con = mysqli_connect("localhost:3307", "root", "steven1234", "myhmsdb");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if(isset($_POST['adsub'])){
    // 5. Validate CSRF token (From 'HEAD')
    // Protects the admin submission form from forgery.
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('<script>alert("CSRF token validation failed!"); window.location.href = "index.php";</script>');
    }
	$username=$_POST['username1'];
	$password=$_POST['password2'];
	$query="select * from admintb where username='$username' and password='$password';";
	$result=mysqli_query($con,$query);
	if(mysqli_num_rows($result)==1)
	{
		$_SESSION['username']=$username;
		header("Location:admin-panel1.php");
	}
	else
		// header("Location:error2.php");
		echo("<script>alert('Invalid Username or Password. Try Again!');
          window.location.href = 'index.php';</script>");
}
if(isset($_POST['update_data']))
{
	$contact=$_POST['contact'];
	$status=$_POST['status'];
	$query="update appointmenttb set payment='$status' where contact='$contact';";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:updated.php");
}




function display_docs()
{
	global $con;
	$query="select * from doctb";
	$result=mysqli_query($con,$query);
	while($row=mysqli_fetch_array($result))
	{
		$name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
		# echo'<option value="" disabled selected>Select Doctor</option>';
		echo '<option value="'.$name.'">'.$name.'</option>';
	}
}

if(isset($_POST['doc_sub']))
{
	$name=$_POST['name'];
	$query="insert into doctb(name)values('$name')";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:adddoc.php");
}
