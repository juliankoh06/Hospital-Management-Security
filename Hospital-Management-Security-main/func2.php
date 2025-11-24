<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. Include Security Headers first to set session cookie parameters
// This must happen BEFORE session_start() is called.
require_once(__DIR__ . '/include/security_headers.php');
require_once('csrf_helper.php');

// 2. Now, start the session
session_start();

try {
    $con = mysqli_connect("localhost", "root", "", "myhmsdb"); 
} catch (mysqli_sql_exception $e) {
    error_log("Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

if(isset($_POST['patsub1'])){
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('<script>alert("CSRF token validation failed!"); window.location.href = "index.php";</script>');
    }

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        echo("<script>alert('Password should be at least 8 characters long and should include at least one upper case letter, one number, and one special character.');
              window.location.href = 'index.php';</script>");
    } 
    elseif($password == $cpassword) {
        try {
            // Both password fields hashed before storing in database
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $hashed_cpassword = password_hash($cpassword, PASSWORD_BCRYPT);
            
            $query = "insert into patreg(fname,lname,gender,email,contact,password,cpassword,login_attempts,lockout_time) values (?,?,?,?,?,?,?,0,NULL);";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssiss", $fname, $lname, $gender, $email, $contact, $hashed_password, $hashed_cpassword);
            $result = mysqli_stmt_execute($stmt);
            
            if($result){
                $pid = mysqli_insert_id($con); 
                
                $_SESSION['pid'] = $pid;
                $_SESSION['username'] = $fname . " " . $lname;
                $_SESSION['fname'] = $fname;
                $_SESSION['lname'] = $lname;
                $_SESSION['gender'] = $gender;
                $_SESSION['contact'] = $contact;
                $_SESSION['email'] = $email;
                
                header("Location:admin-panel.php");
            } 
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            echo "<script>alert('System Error: Registration failed. Email might already exist.'); window.location.href = 'index.php';</script>";
        }
    } 
    else {
        header("Location:error1.php");
    }
}

if(isset($_POST['update_data']))
{
    $contact=$_POST['contact'];
    $status=$_POST['status'];
    try {
        $query="update appointmenttb set payment=? where contact=?;";
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

function display_docs()
{
    global $con;
    try {
        $query="select * from doctb";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row=mysqli_fetch_array($result))
        {
            $name = htmlspecialchars($row['username']);
            $price = htmlspecialchars($row['docFees']);
            $spec = htmlspecialchars($row['spec']);
            echo '<option value="'.$name.'">'.$name.'</option>';
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }
}

if(isset($_POST['doc_sub']))
{
    $name=$_POST['name'];
    try {
        $query="insert into doctb(username)values(?)";
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

function display_admin_panel(){
    echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
      <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
  </nav>
  </head>
  <style type="text/css">
    button:hover{cursor:pointer;}
    #inputbtn:hover{cursor:pointer;}
  </style>
  <body style="padding-top:50px;">
 <div class="jumbotron" id="ab1"></div>
   <div class="container-fluid" style="margin-top:50px;">
    <div class="row">
  <div class="col-md-4">
    <div class="list-group" id="list-tab" role="tablist">
      <a class="list-group-item list-group-item-action active" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Appointment</a>
      <a class="list-group-item list-group-item-action" href="patientdetails.php" role="tab" aria-controls="home">Patient List</a>
      <a class="list-group-item list-group-item-action" id="list-profile-list" data-toggle="list" href="#list-profile" role="tab" aria-controls="profile">Payment Status</a>
      <a class="list-group-item list-group-item-action" id="list-messages-list" data-toggle="list" href="#list-messages" role="tab" aria-controls="messages">Prescription</a>
      <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Doctors Section</a>
       <a class="list-group-item list-group-item-action" id="list-attend-list" data-toggle="list" href="#list-attend" role="tab" aria-controls="settings">Attendance</a>
    </div><br>
  </div>

  <div class="col-md-8">
    <div class="tab-content" id="nav-tabContent">
      <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <center><h4>Create an appointment</h4></center><br>
              <form class="form-group" method="post" action="appointment.php">
                <div class="row">
                  <div class="col-md-4"><label>First Name:</label></div>
                  <div class="col-md-8"><input type="text" class="form-control" name="fname"></div><br><br>
                  <div class="col-md-4"><label>Last Name:</label></div>
                  <div class="col-md-8"><input type="text" class="form-control"  name="lname"></div><br><br>
                  <div class="col-md-4"><label>Email id:</label></div>
                  <div class="col-md-8"><input type="text"  class="form-control" name="email"></div><br><br>
                  <div class="col-md-4"><label>Contact Number:</label></div>
                  <div class="col-md-8"><input type="text" class="form-control"  name="contact"></div><br><br>
                  <div class="col-md-4"><label>Doctor:</label></div>
                  <div class="col-md-8">
                   <select name="doctor" class="form-control">';
                   
    display_docs(); 
    
    echo '         </select>
                  </div><br><br>
                  <div class="col-md-4"><label>Payment:</label></div>
                  <div class="col-md-8">
                    <select name="payment" class="form-control" >
                      <option value="" disabled selected>Select Payment Status</option>
                      <option value="Paid">Paid</option>
                      <option value="Pay later">Pay later</option>
                    </select>
                  </div><br><br><br>
                  <div class="col-md-4">
                    <input type="submit" name="entry_submit" value="Create new entry" class="btn btn-primary" id="inputbtn">
                  </div>
                  <div class="col-md-8"></div>                  
                </div>
              </form>
            </div>
          </div>
        </div><br>
      </div>
      </div>
  </div>
</div>
   </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>
   <script type="text/javascript">
   $(document).ready(function(){
    swal({
      title: "Welcome!",
      text: "Have a nice day!",
      imageUrl: "images/sweet.jpg",
      animation: false
    }); 
   }); 
   </script>
  </body>
</html>';
}
?>
