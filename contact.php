<?php 
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con=mysqli_connect("localhost","root","","myhmsdb");
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    echo '<script>alert("System Error: Database connection failed."); window.location.href = "contact.html";</script>';
    exit();
}

if(isset($_POST['btnSubmit']))
{
    try {
        $name = $_POST['txtName'];
        $email = $_POST['txtEmail'];
        $contact = $_POST['txtPhone'];
        $message = $_POST['txtMsg'];

        $query="insert into contact(name,email,contact,message) values(?,?,?,?);";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $contact, $message);
        $result = mysqli_stmt_execute($stmt);
        
        if($result)
        {
            echo '<script type="text/javascript">'; 
            echo 'alert("Message sent successfully!");'; 
            echo 'window.location.href = "contact.html";';
            echo '</script>';
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        echo '<script type="text/javascript">'; 
        echo 'alert("Error: Unable to send message.");'; 
        echo 'window.location.href = "contact.html";';
        echo '</script>';
    }
}
?>