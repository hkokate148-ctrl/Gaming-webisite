<?php
session_start();
include "../db.php";

$error = "";

if(isset($_POST['login'])){
    $email = strtolower(trim($_POST['email']));
    $pass = $_POST['password'];

    // Prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        $user = $res->fetch_assoc();

        if(password_verify($pass, $user['password'])){
            $_SESSION['user'] = $user['name'];
            header("Location: ../home.php");
            exit();
        } else {
            $error = "Wrong Password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">

<style>
body{
    margin:0;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    font-family:'Poppins',sans-serif;
    color:white;
}

.login-box{
    background: rgba(255,255,255,0.08);
    padding:40px;
    border-radius:20px;
    backdrop-filter: blur(10px);
    text-align:center;
    width:320px;
    box-shadow:0 0 20px rgba(0,255,255,0.4);
}

h2{
    margin-bottom:20px;
    text-shadow:0 0 10px cyan;
}

input{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:none;
    border-radius:10px;
}

button{
    width:100%;
    padding:10px;
    background:cyan;
    border:none;
    border-radius:20px;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    box-shadow:0 0 15px cyan;
}

a{
    color:cyan;
    text-decoration:none;
}

.error{
    color:red;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
    </form>

    <p>Don't have account? <a href="register.php">Register</a></p>
    <p><a href="forgot.php">Forgot Password?</a></p>
</div>

</body>
</html>