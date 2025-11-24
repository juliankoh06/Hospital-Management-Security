<!DOCTYPE html>
<?php 
// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
<html>
<head>
	<title>Patient Details</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
<?php
include("newfunc.php");

if(isset($_POST['patient_search_submit']))
{
    $contact = $_POST['patient_contact'];

    try {
        // 1. Prepare Query (Security: Prevents SQL Injection)
        // We use a specific flow: Prepare -> Bind -> Execute to separate code from data.
        // 
        $stmt = $con->prepare("SELECT * FROM patreg WHERE contact = ?");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();

        // 2. Check if results exist
        if($result->num_rows === 0){
            echo "<script> alert('No entries found! Please enter valid details'); 
                   window.location.href = 'admin-panel1.php#list-doc';</script>";
        }
        else {
            // 3. Output Table Header
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

            // 4. Loop through results (Handles duplicate contacts if they exist)
            while ($row = $result->fetch_assoc()) {
                // Security: Sanitize output to prevent XSS
                $fname = htmlspecialchars($row['fname'], ENT_QUOTES, 'UTF-8');
                $lname = htmlspecialchars($row['lname'], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                $contact_out = htmlspecialchars($row['contact'], ENT_QUOTES, 'UTF-8');
                $password = htmlspecialchars($row['password'], ENT_QUOTES, 'UTF-8');

                echo "<tr>
                    <td>$fname</td>
                    <td>$lname</td>
                    <td>$email</td>
                    <td>$contact_out</td>
                    <td>$password</td>
                </tr>";
            }
            
            echo "</tbody></table><center><a href='admin-panel1.php' class='btn btn-light'>Back to dashboard</a></div></center></div></div></div>";
        }

    } catch (mysqli_sql_exception $e) {
        // 5. Error Handling
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
