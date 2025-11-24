<?php
session_start();
// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // CONNECT TO DATABASE
    // Note: If your XAMPP uses port 3307, change "localhost" to "localhost:3307"
    $con = mysqli_connect("localhost", "root", "", "myhmsdb");
} catch (mysqli_sql_exception $e) {
    error_log("Connection Error: " . $e->getMessage());
    die("System Error: Database connection failed.");
}

if(isset($_POST['search_submit'])){
    $contact = $_POST['contact'];
    
    // Ensure the doctor is searching only their own patients
    $docname = $_SESSION['dname']; 

    try {
        // 1. Use Prepared Statement (Prevents SQL Injection)
        $query = "SELECT * FROM appointmenttb WHERE contact=? AND doctor=?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ss", $contact, $docname);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // START HTML OUTPUT
        echo '<!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <title>Search Results</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
          </head>
          <body style="background-color:#342ac1;color:white;text-align:center;padding-top:50px;">
          <div class="container" style="text-align:left;">
          <center><h3>Search Results</h3></center><br>
          <table class="table table-hover">
          <thead>
            <tr>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Appointment Date</th>
              <th>Appointment Time</th>
            </tr>
          </thead>
          <tbody>';

        // 2. Loop through results
        if ($result->num_rows > 0) {
            while($row = mysqli_fetch_array($result)){
                // 3. Sanitize Output (Prevents XSS Attacks)
                $fname = htmlspecialchars($row['fname'], ENT_QUOTES, 'UTF-8');
                $lname = htmlspecialchars($row['lname'], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                $contact_out = htmlspecialchars($row['contact'], ENT_QUOTES, 'UTF-8');
                $appdate = htmlspecialchars($row['appdate'], ENT_QUOTES, 'UTF-8');
                $apptime = htmlspecialchars($row['apptime'], ENT_QUOTES, 'UTF-8');

                echo '<tr>
                  <td>'.$fname.'</td>
                  <td>'.$lname.'</td>
                  <td>'.$email.'</td>
                  <td>'.$contact_out.'</td>
                  <td>'.$appdate.'</td>
                  <td>'.$apptime.'</td>
                </tr>';
            }
        } else {
            echo '<tr><td colspan="6" style="text-align:center;">No results found for this contact number.</td></tr>';
        }

        echo '</tbody></table></div> 
        <div><a href="doctor-panel.php" class="btn btn-light">Go Back</a></div>
        
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
          </body>
        </html>';

    } catch (mysqli_sql_exception $e) {
        error_log("Search Error: " . $e->getMessage());
        echo "<script>alert('Error: Unable to search appointments.'); window.location.href = 'doctor-panel.php';</script>";
    }
}
?>
