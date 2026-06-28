<?php
session_start();
include "../db.php";

// Session email check
$email = $_SESSION['reset_email'] ?? '';
if(!$email){
    die("Access Denied!");
}

$error = "";

if(isset($_POST['verify'])){
    $otp_input = mysqli_real_escape_string($conn, trim($_POST['otp']));// string comparison

    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND otp='$otp_input' AND otp_expire > NOW()");

    if($res->num_rows > 0){
        $_SESSION['otp_verified'] = true;
        header("Location: newpass.php");
        exit();
    } else {
        $error = "Invalid or expired OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
<h2>Enter OTP</h2>
<form method="post">
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button name="verify">Verify OTP</button>
</form>

<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>