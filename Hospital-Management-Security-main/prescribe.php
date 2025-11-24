<!DOCTYPE html>
<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once('csrf_helper.php');
include('func1.php');

if (!isset($_SESSION['dname'])) {
  header("Location: index.php");
  exit();
}

$pid='';
$ID='';
$appdate='';
$apptime='';
$fname = '';
$lname= '';
$doctor = $_SESSION['dname'];

if(isset($_GET['pid']) && isset($_GET['ID']) && ($_GET['appdate']) && isset($_GET['apptime']) && isset($_GET['fname']) && isset($_GET['lname'])) {
  
  $pid = filter_var($_GET['pid'], FILTER_VALIDATE_INT);
  $ID = filter_var($_GET['ID'], FILTER_VALIDATE_INT);
  $fname = htmlspecialchars($_GET['fname'], ENT_QUOTES, 'UTF-8');
  $lname = htmlspecialchars($_GET['lname'], ENT_QUOTES, 'UTF-8');
  $appdate = htmlspecialchars($_GET['appdate'], ENT_QUOTES, 'UTF-8');
  $apptime = htmlspecialchars($_GET['apptime'], ENT_QUOTES, 'UTF-8');

  $stmt = $con->prepare("SELECT ID FROM appointmenttb WHERE ID=? AND doctor=?");
  $stmt->bind_param("is", $ID, $doctor);
  $stmt->execute();
  $stmt->store_result();

  if($stmt->num_rows == 0){
      echo "<script>alert('ERROR: You are not authorized to prescribe for this appointment.'); window.location.href = 'doctor-panel.php';</script>";
      exit();
  }
  $stmt->close();
}

if(isset($_POST['prescribe'])){ 
  
  // Validate CSRF token
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
      die('<script>alert("CSRF token validation failed!"); window.location.href = "doctor-panel.php";</script>');
  }
  
  $appdate = htmlspecialchars($_POST['appdate'], ENT_QUOTES, 'UTF-8');
  $apptime = htmlspecialchars($_POST['apptime'], ENT_QUOTES, 'UTF-8');
  $disease = htmlspecialchars($_POST['disease'], ENT_QUOTES, 'UTF-8');
  $allergy = htmlspecialchars($_POST['allergy'], ENT_QUOTES, 'UTF-8');
  $fname = htmlspecialchars($_POST['fname'], ENT_QUOTES, 'UTF-8');
  $lname = htmlspecialchars($_POST['lname'], ENT_QUOTES, 'UTF-8');
  $prescription = htmlspecialchars($_POST['prescription'], ENT_QUOTES, 'UTF-8');
  
  $pid = filter_var($_POST['pid'], FILTER_VALIDATE_INT);
  $ID = filter_var($_POST['ID'], FILTER_VALIDATE_INT);

  $checkStmt = $con->prepare("SELECT ID FROM appointmenttb WHERE ID=? AND doctor=?");
  $checkStmt->bind_param("is", $ID, $doctor);
  $checkStmt->execute();
  $checkStmt->store_result();

  if($checkStmt->num_rows == 0){
      echo "<script>alert('ERROR: Authorization failed during submission.'); window.location.href = 'doctor-panel.php';</script>";
      exit();
  }
  $checkStmt->close();

  try {
      $stmt = $con->prepare("INSERT INTO prestb(doctor,pid,ID,fname,lname,appdate,apptime,disease,allergy,prescription) VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stmt->bind_param("siisssssss", $doctor, $pid, $ID, $fname, $lname, $appdate, $apptime, $disease, $allergy, $prescription);
      $result = $stmt->execute();

      if($result) {
        echo "<script>alert('Prescribed successfully!'); window.location.href = 'doctor-panel.php';</script>";
      } else {
        echo "<script>alert('Unable to process your request. Try again!');</script>";
      }
      $stmt->close();
  } catch (mysqli_sql_exception $e) {
      error_log($e->getMessage());
      echo "<script>alert('System Error: Unable to save prescription.');</script>";
  }
}
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <style>
      .bg-primary { background: -webkit-linear-gradient(left, #3931af, #00c6ff); }
      .list-group-item.active { z-index: 2; color: #fff; background-color: #342ac1; border-color: #007bff; }
      .text-primary { color: #342ac1!important; }
      .btn-primary{ background-color: #3c50c1; border-color: #3c50c1; }
      button:hover{cursor:pointer;}
      #inputbtn:hover{cursor:pointer;}
    </style>
  </head>

  <body style="padding-top:50px;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
      <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="logout1.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="doctor-panel.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Back</a>
          </li>
        </ul>
      </div>
    </nav>

    <div class="container-fluid" style="margin-top:50px;">
      <h3 style = "margin-left: 40%;  padding-bottom: 20px; font-family: 'IBM Plex Sans', sans-serif;"> 
        Welcome &nbsp<?php echo htmlspecialchars($doctor, ENT_QUOTES, 'UTF-8'); ?> 
      </h3>

      <div class="tab-pane" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
        <form class="form-group" name="prescribeform" method="post" action="prescribe.php">
        
          <div class="row">
            <div class="col-md-4"><label>Disease:</label></div>
            <div class="col-md-8">
              <textarea id="disease" cols="86" rows ="5" name="disease" required></textarea>
            </div><br><br><br>
            
            <div class="col-md-4"><label>Allergies:</label></div>
            <div class="col-md-8">
              <textarea id="allergy" cols="86" rows ="5" name="allergy" required></textarea>
            </div><br><br><br>
            
            <div class="col-md-4"><label>Prescription:</label></div>
            <div class="col-md-8">
              <textarea id="prescription" cols="86" rows ="10" name="prescription" required></textarea>
            </div><br><br><br>
          </div>

          <input type="hidden" name="fname" value="<?php echo htmlspecialchars($fname, ENT_QUOTES, 'UTF-8'); ?>" />
          <input type="hidden" name="lname" value="<?php echo htmlspecialchars($lname, ENT_QUOTES, 'UTF-8'); ?>" />
          <input type="hidden" name="appdate" value="<?php echo htmlspecialchars($appdate, ENT_QUOTES, 'UTF-8'); ?>" />
          <input type="hidden" name="apptime" value="<?php echo htmlspecialchars($apptime, ENT_QUOTES, 'UTF-8'); ?>" />
          <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid, ENT_QUOTES, 'UTF-8'); ?>" />
          <input type="hidden" name="ID" value="<?php echo htmlspecialchars($ID, ENT_QUOTES, 'UTF-8'); ?>" />
          <?php echo csrfTokenField(); ?>
          
          <br><br><br><br>
          <input type="submit" name="prescribe" value="Prescribe" class="btn btn-primary" style="margin-left: 40pc;">
        </form>
      </div>
    </div>
  </body>
</html>
