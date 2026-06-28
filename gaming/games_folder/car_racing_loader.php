<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['user'])){
    die("<h2 style='color:white;text-align:center;'>⚠️ Please Login First</h2>");
}

// Redirect to actual game
header("Location: car_racing.php");
exit();
?>