<!DOCTYPE html>
<html>
<head>
  <title>Patient Details</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if(isset($_POST['patient_search_submit']))
{
  try {
      $contact=$_POST['patient_contact'];
      $query = "select * from patreg where contact= ?";
      $stmt = mysqli_prepare($con, $query);
      mysqli_stmt_bind_param($stmt, "s", $contact);
      mysqli_stmt_execute($stmt);
      $result=mysqli_stmt_get_result($stmt);
      $row=mysqli_fetch_array($result);
      
      if(!$row || ($row['lname']=="" & $row['email']=="" & $row['contact']=="" & $row['password']=="")){
        echo "<script> alert('No entries found! Please enter valid details'); 
              window.location.href = 'admin-panel1.php#list-doc';</script>";
      }
      else {
        echo "<div class='container-fluid' style='margin-top:50px;'>
      <div class='card'>
      <div class='card-body' style='background-color:#342ac1;color:#ffffff;'>
    <table class='table table-hover'>
      <thead>
        <tr>
          <th scope='col'>First Name</th>
          <th scope='col'>Last Name</th>
          <th scope='col'>Email</th>
          <th scope='col'>Contact</th>
          <th scope='col'>Password</th>
        </tr>
      </thead>
      <tbody>";

      
            $fname = htmlspecialchars($row['fname']);
            $lname = htmlspecialchars($row['lname']);
            $email = htmlspecialchars($row['email']);
            $contact = htmlspecialchars($row['contact']);
            $password = htmlspecialchars($row['password']);
            echo "<tr>
              <td>$fname</td>
              <td>$lname</td>
              <td>$email</td>
              <td>$contact</td>
              <td>$password</td>
            </tr>";
        
      echo "</tbody></table><center><a href='admin-panel1.php' class='btn btn-light'>Back to dashboard</a></div></center></div></div></div>";
    }
  } catch (mysqli_sql_exception $e) {
      error_log($e->getMessage());
      echo "<script>alert('Error: Unable to search patient.'); window.location.href = 'admin-panel1.php';</script>";
  }
}
  
?>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script> 
</body>
</html>