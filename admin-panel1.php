<?php 
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();

require_once(__DIR__ . '/include/security_headers.php');

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

try {
    $con = mysqli_connect("localhost","root","","myhmsdb");
} catch (mysqli_sql_exception $e) {
    error_log("Connection error: " . $e->getMessage());
    die("System Error: Database connection failed.");
}

if(isset($_POST['docsub']))
{
  $doctor = $_POST['doctor'];
  $dpassword = $_POST['dpassword'];
  $demail = $_POST['demail'];
  $spec = $_POST['special'];
  $docFees = $_POST['docFees'];

  $uppercase = preg_match('@[A-Z]@', $dpassword);
  $lowercase = preg_match('@[a-z]@', $dpassword);
  $number    = preg_match('@[0-9]@', $dpassword);
  $specialChars = preg_match('@[^\w]@', $dpassword);

  if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($dpassword) < 8) {
      echo("<script>alert('Password should be at least 8 characters long and should include at least one upper case letter, one number, and one special character.');
            window.location.href = 'admin-panel1.php#list-settings';</script>");
  } 
  else {
      try {
          $query="insert into doctb(username,password,email,spec,docFees,login_attempts,lockout_time) values(?,?,?,?,?,0,NULL)";
          $stmt = mysqli_prepare($con, $query);
          mysqli_stmt_bind_param($stmt, "sssss", $doctor, $dpassword, $demail, $spec, $docFees);
          $result=mysqli_stmt_execute($stmt);
          if($result) {
              echo "<script>alert('Doctor added successfully!');</script>";
          }
      } catch (mysqli_sql_exception $e) {
          error_log("Add Doctor Error: " . $e->getMessage());
          echo "<script>alert('Error: Unable to add doctor.');</script>";
      }
  }
}

if(isset($_POST['docsub1']))
{
  try {
      $demail=$_POST['demail'];
      $query="delete from doctb where email=?;";
      $stmt = mysqli_prepare($con, $query);
      mysqli_stmt_bind_param($stmt, "s", $demail);
      $result=mysqli_stmt_execute($stmt);
      if($result) {
          echo "<script>alert('Doctor removed successfully!');</script>";
      } else {
        echo "<script>alert('Unable to delete!');</script>";
      }
  } catch (mysqli_sql_exception $e) {
      error_log("Delete Doctor Error: " . $e->getMessage());
      echo "<script>alert('Error: Unable to delete doctor.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body style="padding-top:50px;">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
       <ul class="navbar-nav mr-auto">
         <li class="nav-item">
          <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container-fluid" style="margin-top:50px;">
    <div class="row">
      <div class="col-md-4">
        <div class="list-group" id="list-tab" role="tablist">
          <a class="list-group-item list-group-item-action active" id="list-doc-list" data-toggle="list" href="#list-doc" role="tab" aria-controls="home">Doctor List</a>
          <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Add Doctor</a>
        </div><br>
      </div>

      <div class="col-md-8">
        <div class="tab-content" id="nav-tabContent">
          
          <div class="tab-pane fade show active" id="list-doc" role="tabpanel" aria-labelledby="list-home-list">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Doctor Name</th>
                    <th scope="col">Specialization</th>
                    <th scope="col">Email</th>
                    <th scope="col">Password</th>
                    <th scope="col">Fees</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    try {
                        $query = "select * from doctb";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        while ($row = mysqli_fetch_array($result)){
                          $username = htmlspecialchars($row['username']);
                          $spec = htmlspecialchars($row['spec']);
                          $email = htmlspecialchars($row['email']);
                          $password = htmlspecialchars($row['password']);
                          $docFees = htmlspecialchars($row['docFees']);
                          
                          echo "<tr>
                            <td>$username</td>
                            <td>$spec</td>
                            <td>$email</td>
                            <td>$password</td>
                            <td>$docFees</td>
                          </tr>";
                        }
                    } catch (mysqli_sql_exception $e) {
                        error_log("Doctor List Error: " . $e->getMessage());
                        echo "<tr><td colspan='5' style='color:red; text-align:center;'>Unable to load doctor list</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
          </div>

          <div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">
            <div class="card">
              <div class="card-body">
                <center><h4>Add Doctor</h4></center><br>
                <form class="form-group" method="post" action="admin-panel1.php">
                  <div class="row">
                    <div class="col-md-4"><label>Doctor Name:</label></div>
                    <div class="col-md-8"><input type="text" class="form-control" name="doctor" required></div><br><br>
                    
                    <div class="col-md-4"><label>Specialization:</label></div>
                    <div class="col-md-8">
                      <select name="special" class="form-control" required>
                        <option value="General" selected disabled>Select Specialization</option>
                        <option value="General">General</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                      </select>
                    </div><br><br>

                    <div class="col-md-4"><label>Email ID:</label></div>
                    <div class="col-md-8"><input type="email" class="form-control" name="demail" required></div><br><br>

                    <div class="col-md-4"><label>Password:</label></div>
                    <div class="col-md-8"><input type="password" class="form-control" name="dpassword" required></div><br><br>

                    <div class="col-md-4"><label>Consultancy Fees:</label></div>
                    <div class="col-md-8"><input type="text" class="form-control" name="docFees" required></div><br><br>
                  </div>
                  <input type="submit" name="docsub" value="Add Doctor" class="btn btn-primary">
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>
