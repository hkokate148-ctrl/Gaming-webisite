<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user_score'])) {
    $_SESSION['user_score'] = 0;
    $_SESSION['computer_score'] = 0;
}

$choices = ["Snake", "Water", "Gun"];
$result = "";
$user = "";
$computer = "";

if(isset($_POST['choice'])) {

    $user = $_POST['choice'];
    $computer = $choices[array_rand($choices)];

    if($user == $computer) {
        $result = "Draw 🤝";
    }
    elseif(
        ($user == "Snake" && $computer == "Water") ||
        ($user == "Water" && $computer == "Gun") ||
        ($user == "Gun" && $computer == "Snake")
    ) {
        $_SESSION['user_score']++;
        $result = "You Win 🎉";
    }
    else {
        $_SESSION['computer_score']++;
        $result = "Computer Wins 😢";
    }
}

if(isset($_POST['reset'])) {
    $_SESSION['user_score'] = 0;
    $_SESSION['computer_score'] = 0;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Snake Water Gun - Gaming Zone</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:linear-gradient(135deg,#1f1c2c,#928dab);
    color:white;
    text-align:center;
}

.topbar{
    width:600px;
    margin:auto;
    display:flex;
    justify-content:flex-start;
}

.backbtn{
    background:#ff8c00;
    color:white;
    border:none;
    padding:10px 20px;
    font-size:16px;
    border-radius:8px;
    cursor:pointer;
}

.backbtn:hover{
    background:#e67600;
}

.container{
    padding:40px;
}

h1{
    font-size:40px;
}

button{
    padding:15px 25px;
    margin:15px;
    font-size:18px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.1);
}

.snake{background:#27ae60;color:white;}
.water{background:#3498db;color:white;}
.gun{background:#e74c3c;color:white;}
.reset{background:black;color:white;}

.result{
    margin-top:30px;
    font-size:22px;
}

.score{
    margin-top:20px;
    font-size:20px;
    background:rgba(0,0,0,0.3);
    padding:10px;
    border-radius:10px;
    display:inline-block;
}
</style>
</head>

<body>

<div class="container">

<h1>🐍 Snake Water Gun 🔫</h1>

<form method="POST">
    <button class="snake" name="choice" value="Snake">🐍 Snake</button>
    <button class="water" name="choice" value="Water">💧 Water</button>
    <button class="gun" name="choice" value="Gun">🔫 Gun</button>
</form>

<form method="POST">
    <button class="reset" name="reset">Reset Score</button>
</form>

<div class="score">
    You: <?php echo $_SESSION['user_score']; ?> |
    Computer: <?php echo $_SESSION['computer_score']; ?>
</div>

<?php if($user != "") { ?>
<div class="result">
    <p>You chose: <b><?php echo $user; ?></b></p>
    <p>Computer chose: <b><?php echo $computer; ?></b></p>
    <h2><?php echo $result; ?></h2>
</div>
<?php } ?>

<br><br>
<!-- Updated link for games_folder -->
<a href="../home.php" style="color:white;text-decoration:none;">⬅ Back to Home</a>
</div>

</body>
</html>