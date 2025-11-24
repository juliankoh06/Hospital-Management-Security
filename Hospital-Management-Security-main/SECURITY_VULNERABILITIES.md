# Security Vulnerabilities Report
## Hospital Management System

**Date:** Generated Report  
**Severity Levels:** Critical, High, Medium, Low

---

## 🔴 CRITICAL VULNERABILITIES

### 1. SQL Injection (Critical)
**Severity:** CRITICAL  
**Location:** Multiple files throughout the application  
**Description:** All database queries use direct string interpolation without prepared statements or input sanitization.

**Vulnerable Code Examples:**
```php
// func.php:7
$query="select * from patreg where email='$email' and password='$password';";

// func1.php:7
$query="select * from doctb where username='$dname' and password='$dpass';";

// func3.php:7
$query="select * from admintb where username='$username' and password='$password';";

// admin-panel.php:42
$check_query = mysqli_query($con,"select apptime from appointmenttb where doctor='$doctor' and appdate='$appdate' and apptime='$apptime'");

// admin-panel.php:45
$query=mysqli_query($con,"insert into appointmenttb(pid,fname,lname,gender,email,contact,doctor,docFees,appdate,apptime,userStatus,doctorStatus) values($pid,'$fname','$lname','$gender','$email','$contact','$doctor','$docFees','$appdate','$apptime','1','1')");

// doctor-panel.php:8
$query=mysqli_query($con,"update appointmenttb set doctorStatus='0' where ID = '".$_GET['ID']."'");

// prescribe.php:33
$query=mysqli_query($con,"insert into prestb(doctor,pid,ID,fname,lname,appdate,apptime,disease,allergy,prescription) values ('$doctor','$pid','$ID','$fname','$lname','$appdate','$apptime','$disease','$allergy','$prescription')");

// search.php:7
$query="select * from appointmenttb where contact='$contact' and doctor='$docname';";

// contact.php:10
$query="insert into contact(name,email,contact,message) values('$name','$email','$contact','$message');";
```

**Impact:** 
- Complete database compromise
- Data theft/modification/deletion
- Authentication bypass
- Privilege escalation

**Fix:** Use prepared statements with mysqli_prepare() or PDO.

---

### 2. Plain Text Password Storage (Critical)
**Severity:** CRITICAL  
**Location:** All authentication files (func.php, func1.php, func2.php, func3.php)  
**Description:** Passwords are stored in plain text in the database and compared directly.

**Vulnerable Code:**
```php
// func2.php:13
$query="insert into patreg(fname,lname,gender,email,contact,password,cpassword) values ('$fname','$lname','$gender','$email','$contact','$password','$cpassword');";

// func.php:7
$query="select * from patreg where email='$email' and password='$password';";
```

**Impact:**
- If database is compromised, all passwords are exposed
- No protection against rainbow table attacks
- Passwords visible in admin panel

**Fix:** Use password_hash() and password_verify() functions.

---

### 3. Missing Authentication/Authorization Checks (Critical)
**Severity:** CRITICAL  
**Location:** admin-panel.php, doctor-panel.php, admin-panel1.php  
**Description:** Protected pages don't verify if user is logged in before displaying sensitive data.

**Vulnerable Code:**
```php
// admin-panel.php:8-14
$pid = $_SESSION['pid'];
$username = $_SESSION['username'];
// No check if session exists or is valid
```

**Impact:**
- Unauthorized access to patient/doctor/admin panels
- Direct URL access without authentication
- Session hijacking

**Fix:** Add session validation checks on every protected page.

---

## 🟠 HIGH SEVERITY VULNERABILITIES

### 4. Cross-Site Scripting (XSS) - Reflected & Stored
**Severity:** HIGH  
**Location:** Multiple files with echo statements  
**Description:** User input is directly output to HTML without escaping.

**Vulnerable Code:**
```php
// search.php:36-48
$fname=$row['fname'];
echo '<td>'.$fname.'</td>'; // No htmlspecialchars()

// admin-panel.php:225
echo $username; // Direct output from session

// admin-panel1.php:462-472
echo $row['fname'];
echo $row['email'];
echo $row['message']; // Stored XSS from contact form
```

**Impact:**
- Cookie theft
- Session hijacking
- Defacement
- Malicious script execution

**Fix:** Use htmlspecialchars() or htmlentities() for all output.

---

### 5. Insecure Direct Object References (IDOR)
**Severity:** HIGH  
**Location:** admin-panel.php, doctor-panel.php  
**Description:** Users can access/modify other users' data by manipulating URL parameters.

**Vulnerable Code:**
```php
// admin-panel.php:71
$query=mysqli_query($con,"update appointmenttb set userStatus='0' where ID = '".$_GET['ID']."'");
// No check if user owns this appointment

// doctor-panel.php:8
$query=mysqli_query($con,"update appointmenttb set doctorStatus='0' where ID = '".$_GET['ID']."'");
// No check if doctor owns this appointment
```

**Impact:**
- Users can cancel/modify other users' appointments
- Unauthorized data access
- Data manipulation

**Fix:** Verify ownership before allowing operations.

**Vulnerability Details (doctor-panel.php):**

A specific instance of this vulnerability was found in `doctor-panel.php` where a logged-in doctor could cancel any appointment in the system by manipulating the `ID` parameter in the URL. The original code did not verify if the appointment belonged to the doctor requesting the cancellation.

**Original Vulnerable Code:**
```php
// doctor-panel.php:7
if(isset($_GET['cancel']))
  {
    $query=mysqli_query($con,"update appointmenttb set doctorStatus='0' where ID = '".$_GET['ID']."'");
    if($query)
    {
      echo "<script>alert('Your appointment successfully cancelled');</script>";
    }
  }
```

**Fix Applied:**

The vulnerability was remediated by modifying the `UPDATE` query to include a check for the doctor's name, ensuring that a doctor can only cancel their own appointments.

**Remediated Code:**
```php
// doctor-panel.php:8
if(isset($_GET['cancel']))
  {
    $id = $_GET['ID'];
    $query=mysqli_query($con,"update appointmenttb set doctorStatus='0' where ID = '$id' AND doctor='$doctor'");
    if($query)
    {
      echo "<script>alert('Your appointment successfully cancelled');</script>";
    }
  }
```
This ensures that the `doctor` column in the `appointmenttb` table must match the logged-in doctor's name from the session.

**Vulnerability Details (admin-panel.php):**

A similar IDOR vulnerability was present in `admin-panel.php`. A logged-in patient could cancel any appointment in the system by manipulating the `ID` parameter in the URL. The application did not verify that the appointment being cancelled belonged to the patient making the request.

**Original Vulnerable Code:**
```php
// admin-panel.php:71
if(isset($_GET['cancel']))
  {
    $query=mysqli_query($con,"update appointmenttb set userStatus='0' where ID = '".$_GET['ID']."'");
    if($query)
    {
      echo "<script>alert('Your appointment successfully cancelled');</script>";
    }
  }
```

**Fix Applied:**

The vulnerability was remediated by adding a condition to the `UPDATE` query. The query now checks that the `pid` (patient ID) of the appointment matches the `pid` of the logged-in user.

**Remediated Code:**
```php
// admin-panel.php:71
if(isset($_GET['cancel']))
  {
    $id = $_GET['ID'];
    $query=mysqli_query($con,"update appointmenttb set userStatus='0' where ID = '$id' AND pid = '$pid'");
    if($query)
    {
      echo "<script>alert('Your appointment successfully cancelled');</script>";
    }
  }
```
This ensures that patients can only cancel their own appointments.

**Vulnerability Details (prescribe.php):**

A critical IDOR vulnerability was discovered in `prescribe.php`. A logged-in doctor could create a prescription for any appointment in the system, even those belonging to other doctors, by manipulating the `ID` and other parameters in the URL. The application did not validate that the appointment belonged to the doctor creating the prescription. This could lead to unauthorized medical record creation and serious patient safety risks.

**Original Vulnerable Code:**
```php
// prescribe.php (before fix)
if(isset($_GET['pid']) && isset($_GET['ID']) ... ) {
    $pid = $_GET['pid'];
    $ID = $_GET['ID'];
    // ... No ownership check
}

if(isset($_POST['prescribe']) && ...){
    // ...
    $ID = $_POST['ID'];
    // ...
    $query=mysqli_query($con,"insert into prestb(...) values (...)"); // No ownership check
}
```

**Fix Applied:**

The vulnerability was fixed by adding a server-side authorization check. Before loading the prescription page or processing the prescription form, the code now verifies that the appointment `ID` belongs to the currently logged-in doctor. If the check fails, the operation is aborted, and an error message is shown.

**Remediated Code:**
```php
// prescribe.php (after fix)
if(isset($_GET['ID'])) {
    $ID = $_GET['ID'];
    // IDOR check
    $query = mysqli_query($con, "SELECT * FROM appointmenttb WHERE ID='$ID' AND doctor='$doctor'");
    if(mysqli_num_rows($query) == 0){
        echo "<script>alert('ERROR: You are not authorized to prescribe for this appointment.'); window.location.href = 'doctor-panel.php';</script>";
        exit();
    }
}

if(isset($_POST['prescribe'])){
    $ID = $_POST['ID'];
    // IDOR check
    $query = mysqli_query($con, "SELECT * FROM appointmenttb WHERE ID='$ID' AND doctor='$doctor'");
    if(mysqli_num_rows($query) == 0){
        echo "<script>alert('ERROR: You are not authorized to prescribe for this appointment.'); window.location.href = 'doctor-panel.php';</script>";
        exit();
    }
    // ... proceed with insertion
}
```
This ensures that doctors can only create prescriptions for their own appointments.

---

### 6. Missing CSRF Protection
**Severity:** HIGH  
**Location:** All forms (appointment booking, prescription, doctor addition, etc.)  
**Description:** No CSRF tokens on any forms.

**Vulnerable Code:**
```php
// admin-panel.php:307
<form class="form-group" method="post" action="admin-panel.php">
// No CSRF token

// prescribe.php:119
<form class="form-group" name="prescribeform" method="post" action="prescribe.php">
// No CSRF token
```

**Impact:**
- Forced actions on authenticated users
- Unauthorized state changes
- Account takeover

**Fix:** Implement CSRF tokens for all state-changing operations.

---

### 7. Session Management Issues
**Severity:** HIGH  
**Location:** All authentication files  
**Description:** 
- No session timeout
- No session regeneration after login
- Session fixation vulnerability
- No secure session cookie flags

**Vulnerable Code:**
```php
// func.php:2
session_start(); // No configuration
// Missing: session_regenerate_id(), session_set_cookie_params()
```

**Impact:**
- Session hijacking
- Session fixation attacks
- Long-lived sessions

**Fix:** 
- Set secure session cookie parameters
- Regenerate session ID after login
- Implement session timeout

---

## 🟡 MEDIUM SEVERITY VULNERABILITIES

### 8. Information Disclosure
**Severity:** MEDIUM  
**Location:** admin-panel1.php, patientsearch.php, doctorsearch.php  
**Description:** Passwords and sensitive data displayed in admin panels.

**Vulnerable Code:**
```php
// admin-panel1.php:283, 337
$password = $row['password'];
// Passwords displayed in admin panel

// patientsearch.php:42
$password = $row['password'];
// Passwords visible in search results
```

**Impact:**
- Password exposure
- Privacy violation
- Compliance issues (HIPAA, GDPR)

**Fix:** Never display passwords, even to admins.

---

### 9. Weak Input Validation
**Severity:** MEDIUM  
**Location:** Registration and form submission files  
**Description:** Limited or no server-side validation.

**Issues:**
- Email format not validated server-side
- Contact number length not enforced
- No validation for appointment dates/times
- Special characters not properly handled

**Vulnerable Code:**
```php
// func2.php:8-9
$email=$_POST['email'];
$contact=$_POST['contact'];
// No validation before insertion
```

**Impact:**
- Data integrity issues
- Application errors
- Potential injection vectors

**Fix:** Implement comprehensive server-side validation.

---

### 10. Insecure Error Handling
**Severity:** MEDIUM  
**Location:** Multiple files  
**Description:** Error messages may reveal sensitive information.

**Vulnerable Code:**
```php
// admin-panel.php:552
echo mysqli_error($con); // Database errors exposed

// include/config.php:10
echo "Failed to connect to MySQL: " . mysqli_connect_error();
```

**Impact:**
- Database structure disclosure
- System information leakage
- Attack surface expansion

**Fix:** Log errors server-side, show generic messages to users.

---

### 11. Missing Rate Limiting
**Severity:** MEDIUM  
**Location:** Login pages, registration forms  
**Description:** No protection against brute force attacks.

**Impact:**
- Account enumeration
- Brute force password attacks
- DoS on authentication system

**Fix:** Implement rate limiting and account lockout mechanisms.

---

### 12. Insecure File Operations
**Severity:** MEDIUM  
**Location:** TCPDF usage  
**Description:** PDF generation may be vulnerable if file operations are not properly secured.

**Impact:**
- Path traversal attacks
- File inclusion vulnerabilities

**Fix:** Validate and sanitize all file paths.

---

## 🔵 LOW SEVERITY VULNERABILITIES

### 13. Hardcoded Database Credentials
**Severity:** LOW  
**Location:** All PHP files  
**Description:** Database credentials hardcoded in multiple files.

**Vulnerable Code:**
```php
// Multiple files
$con=mysqli_connect("localhost","root","","myhmsdb");
```

**Impact:**
- Difficult to change credentials
- Version control exposure if committed

**Fix:** Use configuration files outside web root.

---

### 14. Missing HTTPS Enforcement
**Severity:** LOW (HIGH in production)  
**Location:** Entire application  
**Description:** No HTTPS enforcement for sensitive operations.

**Impact:**
- Credential interception
- Man-in-the-middle attacks
- Session hijacking

**Fix:** Enforce HTTPS, use HSTS headers.

---

### 15. Weak Password Policy
**Severity:** LOW  
**Location:** Registration forms  
**Description:** Only minimum 6 characters required, no complexity requirements.

**Vulnerable Code:**
```php
// index.php:39-44
if(pass1.value.length<6){
    alert("Password must be at least 6 characters long. Try again!");
    return false;
}
```

**Impact:**
- Weak passwords easily cracked
- Account compromise

**Fix:** Enforce strong password policy (length, complexity, etc.).

---

### 16. Missing Security Headers
**Severity:** LOW  
**Location:** All pages  
**Description:** No security headers set (X-Frame-Options, X-Content-Type-Options, etc.)

**Impact:**
- Clickjacking attacks
- MIME type sniffing attacks

**Fix:** Implement security headers.

---

### 17. Insecure Logout
**Severity:** LOW  
**Location:** logout.php, logout1.php  
**Description:** Logout doesn't properly clear all session data.

**Vulnerable Code:**
```php
// logout.php:3
session_destroy(); // Should also unset $_SESSION array
```

**Impact:**
- Session data may persist
- Incomplete logout

**Fix:** Unset $_SESSION array before session_destroy().

---

## 📊 SUMMARY

**Total Vulnerabilities Found:** 17

- **Critical:** 3
- **High:** 4
- **Medium:** 6
- **Low:** 4

## 🎯 PRIORITY FIXES

1. **Immediate (Critical):**
   - Implement prepared statements for ALL database queries
   - Hash all passwords using password_hash()
   - Add authentication checks to all protected pages

2. **Short-term (High):**
   - Implement CSRF protection
   - Fix XSS vulnerabilities
   - Add proper session management
   - Fix IDOR vulnerabilities

3. **Medium-term (Medium):**
   - Add input validation
   - Remove password display
   - Implement rate limiting
   - Improve error handling

4. **Long-term (Low):**
   - Move credentials to config files
   - Enforce HTTPS
   - Add security headers
   - Strengthen password policy

---

## 📝 NOTES

- This application should NOT be deployed to production without addressing Critical and High severity issues
- Consider implementing a security framework or using established PHP security libraries
- Regular security audits recommended
- Consider OWASP Top 10 compliance

---

**Report Generated:** Based on codebase analysis  
**Recommendation:** Complete security overhaul required before production deployment

---

## 🔴 ADDITIONAL CRITICAL VULNERABILITIES FOUND

### 18. Privilege Escalation / Authorization Bypass (Critical)
**Severity:** CRITICAL  
**Location:** prescribe.php  
**Description:** Doctors can prescribe for ANY appointment by manipulating URL parameters. No verification that the appointment belongs to that doctor.

**Vulnerable Code:**
```php
// prescribe.php:10-33
$doctor = $_SESSION['dname']; // Only checks session, not ownership
if(isset($_GET['pid']) && isset($_GET['ID']) && ...) {
    $pid = $_GET['pid'];
    $ID = $_GET['ID'];
    // No check if this appointment belongs to $doctor
}

// Later inserts prescription without verifying doctor owns appointment
$query=mysqli_query($con,"insert into prestb(doctor,pid,ID,...) values ('$doctor','$pid','$ID',...)");
```

**Impact:**
- Doctors can prescribe for other doctors' patients
- Unauthorized medical record creation
- HIPAA/GDPR violations
- Medical malpractice risk

**Fix:** Verify appointment ownership before allowing prescription:
```php
$verify_query = "SELECT * FROM appointmenttb WHERE ID='$ID' AND doctor='$doctor'";
// Only proceed if verification passes
```

---

### 19. Account Enumeration / Duplicate Registration (Critical)
**Severity:** CRITICAL  
**Location:** func2.php  
**Description:** No check for duplicate email addresses during registration. Allows multiple accounts with same email.

**Vulnerable Code:**
```php
// func2.php:13
$query="insert into patreg(fname,lname,gender,email,contact,password,cpassword) values ('$fname','$lname','$gender','$email','$contact','$password','$cpassword');";
// No check if email already exists
```

**Impact:**
- Multiple accounts with same email
- Account confusion
- Potential for account takeover
- Data integrity issues

**Fix:** Check for existing email before insertion:
```php
$check = "SELECT email FROM patreg WHERE email='$email'";
if (mysqli_num_rows($check) > 0) {
    // Reject registration
}
```

---

### 20. Session Variable Bug Leading to Undefined Behavior (High)
**Severity:** HIGH  
**Location:** func2.php:28  
**Description:** Uses undefined variable `$row` which will always be empty/null.

**Vulnerable Code:**
```php
// func2.php:25-29
$query1 = "select * from patreg;";
$result1 = mysqli_query($con,$query1);
if($result1){
  $_SESSION['pid'] = $row['pid']; // $row is never defined!
}
```

**Impact:**
- Session may not be properly set
- User may not be able to access their own data
- Potential for null pointer issues
- Broken functionality

**Fix:** Fetch the row first:
```php
$row = mysqli_fetch_array($result1);
if($row) {
    $_SESSION['pid'] = $row['pid'];
}
```

---

### 21. Missing Role-Based Access Control (RBAC) (Critical)
**Severity:** CRITICAL  
**Location:** All panel files  
**Description:** No verification of user roles. A patient could potentially access doctor or admin functions by manipulating sessions or URLs.

**Vulnerable Code:**
```php
// admin-panel.php:8-14
$pid = $_SESSION['pid'];
$username = $_SESSION['username'];
// No check if user is actually a patient
// No check if user should have access to this panel

// doctor-panel.php:5
$doctor = $_SESSION['dname'];
// No verification that user is actually a doctor
```

**Impact:**
- Patients accessing doctor/admin functions
- Unauthorized privilege escalation
- Data breach
- System compromise

**Fix:** Implement role checking:
```php
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: index.php");
    exit();
}
```

---

### 22. Unsafe GET Parameter Usage (High)
**Severity:** HIGH  
**Location:** prescribe.php, admin-panel.php, doctor-panel.php  
**Description:** GET parameters used directly in SQL queries and operations without validation.

**Vulnerable Code:**
```php
// prescribe.php:11-17
if(isset($_GET['pid']) && isset($_GET['ID']) && ...) {
    $pid = $_GET['pid']; // Direct assignment, no validation
    $ID = $_GET['ID'];   // Can be manipulated
    $fname = $_GET['fname']; // XSS risk
    // Used directly in SQL later
}

// doctor-panel.php:8
$query=mysqli_query($con,"update appointmenttb set doctorStatus='0' where ID = '".$_GET['ID']."'");
// No validation of ID format, ownership, etc.
```

**Impact:**
- SQL injection via GET parameters
- XSS attacks
- Unauthorized data modification
- Parameter manipulation attacks

**Fix:** Validate and sanitize all GET parameters:
```php
$ID = filter_var($_GET['ID'], FILTER_VALIDATE_INT);
if ($ID === false) {
    die("Invalid ID");
}
```

---

### 23. Mass Assignment Vulnerability (High)
**Severity:** HIGH  
**Location:** func.php, func2.php  
**Description:** Session variables set directly from POST data without validation or whitelisting.

**Vulnerable Code:**
```php
// func.php:12-18
$_SESSION['pid'] = $row['pid'];
$_SESSION['username'] = $row['fname']." ".$row['lname'];
$_SESSION['fname'] = $row['fname'];
$_SESSION['lname'] = $row['lname'];
$_SESSION['gender'] = $row['gender'];
$_SESSION['contact'] = $row['contact'];
$_SESSION['email'] = $row['email'];
// All from database, but func2.php sets from POST directly

// func2.php:16-21
$_SESSION['username'] = $_POST['fname']." ".$_POST['lname'];
$_SESSION['fname'] = $_POST['fname'];
// Direct from POST without validation
```

**Impact:**
- Session poisoning
- Privilege escalation
- Data integrity issues
- Unauthorized session manipulation

**Fix:** Validate all input before setting session variables.

---

### 24. Missing Directory Protection (Medium)
**Severity:** MEDIUM  
**Location:** Root directory, include/ directory  
**Description:** No .htaccess files to protect sensitive directories and files.

**Impact:**
- Direct access to PHP files
- Configuration file exposure
- Source code disclosure
- Database credentials exposure

**Fix:** Add .htaccess files:
```apache
# Deny direct access to include files
<FilesMatch "^(config|func|newfunc)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

### 25. Business Logic Flaw - Appointment Cancellation (High)
**Severity:** HIGH  
**Location:** admin-panel.php, doctor-panel.php  
**Description:** Users can cancel appointments without verifying ownership or checking business rules.

**Vulnerable Code:**
```php
// admin-panel.php:71
$query=mysqli_query($con,"update appointmenttb set userStatus='0' where ID = '".$_GET['ID']."'");
// No check if:
// - User owns this appointment
// - Appointment is in the past
// - Cancellation is allowed at this time
```

**Impact:**
- Users canceling others' appointments
- Business rule violations
- Data integrity issues

**Fix:** Add ownership and business rule validation.

---

## 📊 UPDATED SUMMARY

**Total Vulnerabilities Found:** 25

- **Critical:** 6 (was 3)
- **High:** 8 (was 4)
- **Medium:** 7 (was 6)
- **Low:** 4

## 🎯 REVISED PRIORITY FIXES

1. **Immediate (Critical - 6 issues):**
   - SQL Injection (all queries)
   - Plain text passwords
   - Missing authentication checks
   - **Privilege escalation in prescriptions**
   - **Account enumeration/duplicate registration**
   - **Missing role-based access control**

2. **Short-term (High - 8 issues):**
   - CSRF protection
   - XSS vulnerabilities
   - Session management
   - IDOR vulnerabilities
   - **Authorization bypass in prescriptions**
   - **Unsafe GET parameters**
   - **Mass assignment**
   - **Business logic flaws**

3. **Medium-term (Medium - 7 issues):**
   - Input validation
   - Remove password display
   - Rate limiting
   - Error handling
   - **Directory protection**
   - File operations
   - Hardcoded credentials

4. **Long-term (Low - 4 issues):**
   - HTTPS enforcement
   - Security headers
   - Password policy
   - Logout improvements

---

**⚠️ CRITICAL FINDING:** The prescription system allows doctors to prescribe for ANY patient by manipulating URL parameters. This is a **CRITICAL** security and compliance issue that could lead to:
- Medical malpractice
- HIPAA violations
- Legal liability
- Patient safety risks

**This must be fixed immediately before any production use.**

