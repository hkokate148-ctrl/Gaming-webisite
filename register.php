<?php
include "../db.php";

$error = "";

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    // Validation
    if(empty($name) || empty($email) || empty($password)){
        $error = "All fields required!";
    }
    elseif(strlen($password) < 6){
        $error = "Password must be at least 6 characters!";
    }
    else{

        // Check email exist
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows > 0){
            $error = "Email already exists!";
        } else {

            // Insert user
            $pass = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)");
            $stmt->bind_param("sss", $name, $email, $pass);
            $stmt->execute();

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
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

.register-box{
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
</style>

</head>
<body>

<div class="register-box">
    <h2>Register</h2>
<?php if(!empty($error)) echo "<div style='color:red;'>$error</div>"; ?>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="register">Register</button>
    </form>

    <p>Already have account? <a href="login.php">Login</a></p>
</div>

</body>
</html>