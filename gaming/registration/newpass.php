<?php
session_start();
include "../db.php";

if(!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']){
    die("Access Denied!");
}

$email = $_SESSION['reset_email'];
$msg = "";

if(isset($_POST['reset'])){
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $pass = password_hash($password, PASSWORD_BCRYPT);

    $conn->query("UPDATE users SET password='$pass', otp=NULL, otp_expire=NULL WHERE email='$email'");

    session_destroy();

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Password</title>
</head>
<body>
<h2>Set New Password</h2>
<form method="post">
    <input type="password" name="password" placeholder="Enter new password" required>
    <button name="reset">Reset Password</button>
</form>

<?php if($msg) echo "<div style='color:cyan;'>$msg</div>"; ?>
</body>
</html>