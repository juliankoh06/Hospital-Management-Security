<?php 
require_once('csrf_helper.php');
session_start();

$con=mysqli_connect("localhost:3307","root","","myhmsdb");
if(isset($_POST['btnSubmit']))
{
	// Validate CSRF token
	if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
		die('<script>alert("CSRF token validation failed!"); window.location.href = "contact.html";</script>');
	}
	
	// Sanitize and validate inputs
	$name = htmlspecialchars(trim($_POST['txtName']), ENT_QUOTES, 'UTF-8');
	$email = filter_var($_POST['txtEmail'], FILTER_SANITIZE_EMAIL);
	$contact = htmlspecialchars(trim($_POST['txtPhone']), ENT_QUOTES, 'UTF-8');
	$message = htmlspecialchars(trim($_POST['txtMsg']), ENT_QUOTES, 'UTF-8');

	// Use prepared statement to prevent SQL injection
	$stmt = $con->prepare("INSERT INTO contact(name,email,contact,message) VALUES(?,?,?,?)");
	$stmt->bind_param("ssss", $name, $email, $contact, $message);
	$result = $stmt->execute();
	
	if($result)
    {
    	echo '<script type="text/javascript">'; 
		echo 'alert("Message sent successfully!");'; 
		echo 'window.location.href = "contact.html";';
		echo '</script>';
    }
}