<?php
session_start();
include "../db.php";

$msg = "";

if(isset($_POST['send_otp'])){
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    $res = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($res->num_rows > 0){

        $otp = rand(100000,999999);
        $expire = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // OTP update in DB
        $conn->query("UPDATE users SET otp='$otp', otp_expire='$expire' WHERE email='$email'");

        // Session for verify.php
        $_SESSION['reset_email'] = $email;

        // Show OTP on screen (Testing mode)
        $msg = "Your OTP (Testing purpose): <b>$otp</b><br><a href='verify.php'>Go to Verify OTP</a>";
    } else {
        $msg = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<style>
body{
    background:#0f0c29;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    font-family:Poppins;
}
.box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    text-align:center;
}
input,button{
    width:100%;
    margin:10px 0;
    padding:10px;
}
button{
    background:cyan;
    border:none;
    cursor:pointer;
}
.message{
    margin-top:15px;
    color:cyan;
}
</style>
</head>

<body>

<div class="box">
<h2>Reset Password</h2>

<!-- HTML form -->
<form method="post">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button name="send_otp">Send OTP</button>
</form>

<?php if(!empty($msg)) echo "<div class='message'>$msg</div>"; ?>

</div>

</body>
</html>