<?php 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // DATABASE CONNECTION
    $con = mysqli_connect("localhost", "root", "", "myhmsdb");

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    echo '<script>alert("System Error: Database connection failed."); window.location.href = "contact.html";</script>';
    exit();
}

if(isset($_POST['btnSubmit']))
{
    
    // 1. Sanitize and validate inputs
    $name = htmlspecialchars(trim($_POST['txtName']), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['txtEmail'], FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['txtPhone']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['txtMsg']), ENT_QUOTES, 'UTF-8');

    try {
        // 2. Use prepared statement to prevent SQL injection
        $stmt = $con->prepare("INSERT INTO contact(name,email,contact,message) VALUES(?,?,?,?)");
        $stmt->bind_param("ssss", $name, $email, $contact, $message);
        $result = $stmt->execute();
        
        // 3. Success Feedback
        if($result)
        {
            echo '<script type="text/javascript">'; 
            echo 'alert("Message sent successfully!");'; 
            echo 'window.location.href = "contact.html";';
            echo '</script>';
        }

    } catch (mysqli_sql_exception $e) {
        // 4. Error Handling
        error_log($e->getMessage()); // Log the technical error
        echo '<script type="text/javascript">'; 
        echo 'alert("Error: Unable to send message.");'; 
        echo 'window.location.href = "contact.html";';
        echo '</script>';
    }
}
?>
