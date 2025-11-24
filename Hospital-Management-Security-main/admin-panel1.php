<?php 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
require_once(__DIR__ . '/include/security_headers.php');
require_once('csrf_helper.php');

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
  // Validate CSRF token
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    die('<script>alert("CSRF token validation failed!"); window.location.href = "admin-panel1.php";</script>');
  }
  $doctor = $_POST['doctor'];
  $dpassword = $_POST['dpassword'];
  $demail = $_POST['demail'];
  $spec = $_POST['special'];
  $docFees = $_POST['docFees'];

  $uppercase = preg_match('@[A-Z]@', $dpassword);
  $lowercase = preg_match('@[a-z]@', $dpassword);
  $number    = preg_match('@[0-9]@', $dpassword);
  $specialChars = preg_match('@[^\\w]@', $dpassword);

  if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($dpassword) < 8) {
    echo("<script>alert('Password should be at least 8 characters long and should include at least one upper case letter, one number, and one special character.');
      window.location.href = 'admin-panel1.php#list-settings';</script>");
  } 
  else {
    try {
      //Password hashed using bcrypt before storing in database
      $hashed_password = password_hash($dpassword, PASSWORD_BCRYPT);
          
      $query="insert into doctb(username,password,email,spec,docFees,login_attempts,lockout_time) values(?,?,?,?,?,0,NULL)";
      $stmt = mysqli_prepare($con, $query);
      //Using hashed password instead of plain text
      mysqli_stmt_bind_param($stmt, "sssss", $doctor,  $hashed_password, $demail, $spec, $docFees);
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
  // Validate CSRF token
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
      die('<script>alert("CSRF token validation failed!"); window.location.href = "admin-panel1.php";</script>');
  }
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
          <a class="list-group-item list-group-item-action active" id="list-doc-list" data-toggle="list" href="#list-doc" role="tab" aria-controls="home">Doctors</a>
          <a class="list-group-item list-group-item-action" id="list-pat-list" data-toggle="list" href="#list-pat" role="tab" aria-controls="patient">Patients</a>
          <a class="list-group-item list-group-item-action" id="list-app-list" data-toggle="list" href="#list-app" role="tab" aria-controls="appointments">Appointments</a>
          <a class="list-group-item list-group-item-action" id="list-pres-list" data-toggle="list" href="#list-pres" role="tab" aria-controls="prescriptions">Prescriptions</a>
          <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Add Doctor</a>
          <a class="list-group-item list-group-item-action" id="list-settings1-list" data-toggle="list" href="#list-settings1" role="tab" aria-controls="settings1">Delete Doctor</a>
          <a class="list-group-item list-group-item-action" id="list-mes-list" data-toggle="list" href="#list-mes" role="tab" aria-controls="messages">Queries</a>
        </div><br>
      </div>

      <div class="col-md-8">
        <div class="tab-content" id="nav-tabContent">
          
          <div class="tab-pane fade show active" id="list-doc" role="tabpanel" aria-labelledby="list-home-list">
              <table class="table table-hover">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Doctor Name</th>
                    <th scope="col">Specialization</th>
                    <th scope="col">Email</th>
                    <!-- Doctor password column header removed -->
                    <th scope="col">Fees</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    try {
                        // Explicitly select only needed columns (no password)
                        $query = "select username, spec, email, docFees from doctb";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        while ($row = mysqli_fetch_array($result)){
                          $username = htmlspecialchars($row['username']);
                          $spec = htmlspecialchars($row['spec']);
                          $email = htmlspecialchars($row['email']);
                           // password variable removed - no longer fetching password
                          $docFees = htmlspecialchars($row['docFees']);
                          
                          echo "<tr>
                            <td>$username</td>
                            <td>$spec</td>
                            <td>$email</td>
                            <!-- Doctor password column removed -->
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

          <!-- PATIENT LIST -->
          <div class="tab-pane fade" id="list-pat" role="tabpanel" aria-labelledby="list-pat-list">
            <div class="col-md-8">
              <form class="form-group" action="patientsearch.php" method="post">
                <div class="row">
                  <div class="col-md-10"><input type="text" name="patient_contact" placeholder="Enter Contact" class="form-control"></div>
                  <div class="col-md-2"><input type="submit" name="patient_search_submit" class="btn btn-primary" value="Search"></div>
                </div>
              </form>
            </div>
            
            <table class="table table-hover">
              <thead class="thead-dark">
                <tr>
                  <th scope="col">Patient ID</th>
                  <th scope="col">First Name</th>
                  <th scope="col">Last Name</th>
                  <th scope="col">Gender</th>
                  <th scope="col">Email</th>
                  <th scope="col">Contact</th>
                  <!--Patient password column header removed  -->
                </tr>
              </thead>
              <tbody>
                <?php 
                  try {
                      // BEFORE: SELECT * FROM patreg (selected all columns including password and cpassword)
                      // AFTER: Explicitly select only needed columns (no password)
                      $query = "SELECT pid, fname, lname, gender, email, contact FROM patreg";
                    
                      $stmt = mysqli_prepare($con, $query);
                      mysqli_stmt_execute($stmt);
                      $result = mysqli_stmt_get_result($stmt);
                      while ($row = mysqli_fetch_array($result)){
                        $pid = htmlspecialchars($row['pid']);
                        $fname = htmlspecialchars($row['fname']);
                        $lname = htmlspecialchars($row['lname']);
                        $gender = htmlspecialchars($row['gender']);
                        $email = htmlspecialchars($row['email']);
                        $contact = htmlspecialchars($row['contact']);
                        // Patient password variable removed - no longer fetching password

                        echo "<tr>
                          <td>$pid</td>
                          <td>$fname</td>
                          <td>$lname</td>
                          <td>$gender</td>
                          <td>$email</td>
                          <td>$contact</td>
                          <!-- YOUR WORK: Patient password column removed -->
                        </tr>";
                      }
                  } catch (mysqli_sql_exception $e) {
                      error_log("Patient List Error: " . $e->getMessage());
                      echo "<tr><td colspan='6' style='color:red; text-align:center;'>Unable to load patient list</td></tr>";
                  }
                ?>
              </tbody>
            </table>
            <br>
          </div>

          <!-- PRESCRIPTION LIST -->
          <div class="tab-pane fade" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
            <table class="table table-hover">
              <thead class="thead-dark">
                <tr>
                  <th scope="col">Doctor</th>
                  <th scope="col">Patient ID</th>
                  <th scope="col">Appointment ID</th>
                  <th scope="col">First Name</th>
                  <th scope="col">Last Name</th>
                  <th scope="col">Appointment Date</th>
                  <th scope="col">Appointment Time</th>
                  <th scope="col">Disease</th>
                  <th scope="col">Allergy</th>
                  <th scope="col">Prescription</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  try {
                      $query = "select * from prestb";
                      $stmt = mysqli_prepare($con, $query);
                      mysqli_stmt_execute($stmt);
                      $result = mysqli_stmt_get_result($stmt);
                      while ($row = mysqli_fetch_array($result)){
                        echo "<tr>
                          <td>".htmlspecialchars($row['doctor'])."</td>
                          <td>".htmlspecialchars($row['pid'])."</td>
                          <td>".htmlspecialchars($row['ID'])."</td>
                          <td>".htmlspecialchars($row['fname'])."</td>
                          <td>".htmlspecialchars($row['lname'])."</td>
                          <td>".htmlspecialchars($row['appdate'])."</td>
                          <td>".htmlspecialchars($row['apptime'])."</td>
                          <td>".htmlspecialchars($row['disease'])."</td>
                          <td>".htmlspecialchars($row['allergy'])."</td>
                          <td>".htmlspecialchars($row['prescription'])."</td>
                        </tr>";
                      }
                  } catch (mysqli_sql_exception $e) {
                      error_log("Prescription List Error: " . $e->getMessage());
                      echo "<tr><td colspan='10' style='color:red; text-align:center;'>Unable to load prescription list</td></tr>";
                  }
                ?>
              </tbody>
            </table>
            <br>
          </div>

          <!-- APPOINTMENT DETAILS -->
          <div class="tab-pane fade" id="list-app" role="tabpanel" aria-labelledby="list-app-list">
            <div class="col-md-8">
              <form class="form-group" action="appsearch.php" method="post">
                <div class="row">
                  <div class="col-md-10"><input type="text" name="app_contact" placeholder="Enter Contact" class="form-control"></div>
                  <div class="col-md-2"><input type="submit" name="app_search_submit" class="btn btn-primary" value="Search"></div>
                </div>
              </form>
            </div>
            
            <table class="table table-hover">
              <thead class="thead-dark">
                <tr>
                  <th scope="col">Appointment ID</th>
                  <th scope="col">Patient ID</th>
                  <th scope="col">First Name</th>
                  <th scope="col">Last Name</th>
                  <th scope="col">Gender</th>
                  <th scope="col">Email</th>
                  <th scope="col">Contact</th>
                  <th scope="col">Doctor Name</th>
                  <th scope="col">Consultancy Fees</th>
                  <th scope="col">Appointment Date</th>
                  <th scope="col">Appointment Time</th>
                  <th scope="col">Appointment Status</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  try {
                      $query = "select * from appointmenttb;";
                      $stmt = mysqli_prepare($con, $query);
                      mysqli_stmt_execute($stmt);
                      $result = mysqli_stmt_get_result($stmt);
                      while ($row = mysqli_fetch_array($result)){
                        echo "<tr>
                          <td>".htmlspecialchars($row['ID'])."</td>
                          <td>".htmlspecialchars($row['pid'])."</td>
                          <td>".htmlspecialchars($row['fname'])."</td>
                          <td>".htmlspecialchars($row['lname'])."</td>
                          <td>".htmlspecialchars($row['gender'])."</td>
                          <td>".htmlspecialchars($row['email'])."</td>
                          <td>".htmlspecialchars($row['contact'])."</td>
                          <td>".htmlspecialchars($row['doctor'])."</td>
                          <td>".htmlspecialchars($row['docFees'])."</td>
                          <td>".htmlspecialchars($row['appdate'])."</td>
                          <td>".htmlspecialchars($row['apptime'])."</td>
                          <td>";
                        
                        if(($row['userStatus']==1) && ($row['doctorStatus']==1)) {
                          echo "Active";
                        }
                        if(($row['userStatus']==0) && ($row['doctorStatus']==1)) {
                          echo "Cancelled by Patient";
                        }
                        if(($row['userStatus']==1) && ($row['doctorStatus']==0)) {
                          echo "Cancelled by Doctor";
                        }
                        
                        echo "</td></tr>";
                      }
                  } catch (mysqli_sql_exception $e) {
                      error_log("Appointment List Error: " . $e->getMessage());
                      echo "<tr><td colspan='12' style='color:red; text-align:center;'>Unable to load appointment list</td></tr>";
                  }
                ?>
              </tbody>
            </table>
            <br>
          </div>
            
          <!--ori ctn -->
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
                  <?php echo csrfTokenField(); ?>
                  <input type="submit" name="docsub" value="Add Doctor" class="btn btn-primary">
                </form>
              </div>
            </div>
          </div>

          <!-- DELETE DOCTOR -->
          <div class="tab-pane fade" id="list-settings1" role="tabpanel" aria-labelledby="list-settings1-list">
            <form class="form-group" method="post" action="admin-panel1.php">
              <div class="row">
                <div class="col-md-4"><label>Email ID:</label></div>
                <div class="col-md-8"><input type="email" class="form-control" name="demail" required></div><br><br>
              </div>
              <?php echo csrfTokenField(); ?>
              <input type="submit" name="docsub1" value="Delete Doctor" class="btn btn-primary" onclick="return confirm('Do you really want to delete?')">
            </form>
          </div>

          <!-- QUERIES/MESSAGES -->
          <div class="tab-pane fade" id="list-mes" role="tabpanel" aria-labelledby="list-mes-list">
            <div class="col-md-8">
              <form class="form-group" action="messearch.php" method="post">
                <div class="row">
                  <div class="col-md-10"><input type="text" name="mes_contact" placeholder="Enter Contact" class="form-control"></div>
                  <div class="col-md-2"><input type="submit" name="mes_search_submit" class="btn btn-primary" value="Search"></div>
                </div>
              </form>
            </div>
            
            <table class="table table-hover">
              <thead class="thead-dark">
                <tr>
                  <th scope="col">User Name</th>
                  <th scope="col">Email</th>
                  <th scope="col">Contact</th>
                  <th scope="col">Message</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  try {
                      $query = "select * from contact;";
                      $stmt = mysqli_prepare($con, $query);
                      mysqli_stmt_execute($stmt);
                      $result = mysqli_stmt_get_result($stmt);
                      while ($row = mysqli_fetch_array($result)){
                        echo "<tr>
                          <td>".htmlspecialchars($row['name'])."</td>
                          <td>".htmlspecialchars($row['email'])."</td>
                          <td>".htmlspecialchars($row['contact'])."</td>
                          <td>".htmlspecialchars($row['message'])."</td>
                        </tr>";
                      }
                  } catch (mysqli_sql_exception $e) {
                      error_log("Contact List Error: " . $e->getMessage());
                      echo "<tr><td colspan='4' style='color:red; text-align:center;'>Unable to load messages</td></tr>";
                  }
                ?>
              </tbody>
            </table>
            <br>
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