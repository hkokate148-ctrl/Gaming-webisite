<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

include "games.php";

// 🔐 Login check
if(!isset($_SESSION['user'])){
    header("Location: registration/login.php");
    exit();
}

// ✅ Default game
$allowed_games = array_column($games, 'file');

if(isset($_GET['game']) && in_array($_GET['game'], $allowed_games)){
    $file = $_GET['game'];
    $current = "games_folder/".$file;
}

// ✅ Game selection FIXED
if(isset($_GET['game'])){
    $file = basename($_GET['game']); // security

    $path = "games_folder/".$file;

    if(file_exists($path)){
        $current = $path;

        // title set from games array
        foreach($games as $g){
            if($g['file'] == $file){
                $title = $g['name'];
                break;
            }
        }

    } else {
        $current = "games_folder/car_racing.php";
        $title = "Car Racing";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Play Game - AtoZ Games Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:#0f172a;
    color:white;
    overflow:hiidden;
}
.container{
    display:flex;
    height:100vh;
    overflow:hidden;
}
.game-area{
    flex:4;
    display:flex;
    flex-direction:column;
    background:#020617;
    position:relative;
}
.game-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 20px;
    background:#111827;
}
.game-top h3{ margin:0; }
.full-btn{
    background:#6366f1;
    border:none;
    padding:8px 14px;
    color:white;
    cursor:pointer;
    border-radius:6px;
}
.full-btn:hover{ background:#4f46e5; }
#gameFrame{
    flex:1;
    width:100%;
    border:none;
    overflow:hidden;
}

/* SIDEBAR */
.sidebar{
    flex:1;
    background:#020617;
    border-left:1px solid #1f2937;
    display:flex;
    flex-direction:column;
}
.side-title{
    padding:15px;
    font-weight:600;
    border-bottom:1px solid #1f2937;
    text-align:center;
    font-size:18px;
}

/* Carousel */
.carousel-wrapper{
    position:relative;
    display:flex;
    align-items:center;
    overflow:hidden;
    padding:10px 0;
}
.game-list{
    display:flex;
    flex-direction:column;
    gap:10px;
    overflow-y:auto;   /* ✅ vertical scroll */
    scroll-behavior:smooth;
    padding-left:10px;
    max-height: calc(100vh - 70px);
}
.game-list::-webkit-scrollbar{
    width:6px;
}

.game-list::-webkit-scrollbar-thumb{
    background:#6366f1;
    border-radius:10px;
}
.game-list:hover{
    overflow-y:auto;
}
.game-item{
    display:flex;
    align-items:center;
    text-decoration:none;
    color:white;
    padding:5px;
    border-radius:8px;
    transition: transform 0.2s, background 0.2s;
    background:#111827;
}
.game-item img{
    width:60px;
    height:60px;
    border-radius:8px;
    margin-right:10px;
}
.game-item div{ flex:1; }
.game-item:hover{
    background:#6366f1;
    transform:scale(1.05);
    cursor:pointer;
}
.scroll-btn{
    display:none;
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    background:#6366f1;
    border:none;
    color:white;
    font-size:18px;
    padding:5px 10px;
    cursor:pointer;
    border-radius:5px;
    z-index:2;
}
.scroll-btn.left{left:0;}
.scroll-btn.right{right:0;}
.scroll-btn:hover{background:#4f46e5;}

/* MOBILE RESPONSIVE */
@media(max-width:900px){
    .container{ flex-direction:column; }
    .sidebar{
        height:150px;
        flex-direction:row;
        overflow-x:auto;
        border-left:none;
        border-top:1px solid #1f2937;
        align-items:center;
    }
    .game-list{
        flex-direction:row; /* Mobile horizontal */
        max-height: unset;
        padding-left:10px;
    }
    .scroll-btn{ 
display:block; }
    .game-item{
        flex-direction:column;
        min-width:100px;
        margin:0 5px;
        text-align:center;
    }
    .game-item img{ margin:0 0 5px 0; }
    
}
.game-img{
    transition: 0.5s;
}
</style>
</head>
<body>

<div class="container">

<!-- MAIN GAME AREA -->
<div class="game-area">
    <div class="game-top">
        <h3><?php echo $title; ?></h3>
        <div>
            <button class="full-btn" onclick="openFullscreen()">⛶ Fullscreen</button>
            <button class="full-btn" onclick="goBack()">⬅ Back</button>
            <button class="full-btn" onclick="goHome()">🏠 Home</button>
        </div>
    </div>
    <iframe id="gameFrame" src="<?php echo $current; ?>"></iframe>
</div>

<!-- SIDEBAR: MORE GAMES -->
<div class="sidebar">
    <div class="side-title">More Games</div>
    <div class="carousel-wrapper">
        <button class="scroll-btn left" onclick="scrollCarousel(-1)">⬅</button>
        <div class="game-list">

           <?php foreach($games as $g){ ?>
    
<a class="game-item" 
   data-game="<?php echo $g['file']; ?>"
   href="play.php?game=<?php echo urlencode($g['file']); ?>">

    <img src="/gaming/<?php echo $g['img']; ?>" class="game-img">

    <div><?php echo $g['name']; ?></div>

</a>

<?php } ?>

        </div>
        <button class="scroll-btn right" onclick="scrollCarousel(1)">➡</button>
    </div>
</div>

</div>

<script>
// Fullscreen iframe
function openFullscreen(){
    let iframe=document.getElementById("gameFrame");
    if(iframe.requestFullscreen){ iframe.requestFullscreen(); }
    else if(iframe.webkitRequestFullscreen){ iframe.webkitRequestFullscreen(); }
}

// Navigation
function goBack(){ history.back(); }
function goHome(){ window.location.href="home.php"; }

// Desktop carousel scroll
function scrollCarousel(direction){
    const carousel = document.querySelector('.game-list');
    const scrollAmount = 120;
    carousel.scrollBy({ top: scrollAmount * direction, behavior: 'smooth' });
}

const previews = {

2048: [
"/gaming/images/2048_1.png",
"/gaming/images/2048_2.png",
"/gaming/images/2048_3.png"
],

snakewatergun: [
"/gaming/images/snake1.png",
"/gaming/images/snake2.png",
"/gaming/images/snake3.png"
],

tictactoe: [
"/gaming/images/ttt1.png",
"/gaming/images/ttt2.png",
"/gaming/images/ttt3.png"
],

snakes_ladders: [
"/gaming/images/snakes_ladders1.png",
"/gaming/images/snakes_ladders2.png",
"/gaming/images/snakes_ladders3.png"
],

car_racing: [
"/gaming/images/racing1.png",
"/gaming/images/racing2.png",
"/gaming/images/racing3.png"
]

};
// Desktop carousel scroll by mouse wheel
const gameList = document.querySelector('.game-list');
gameList.addEventListener('wheel', function(e){
    if(window.innerWidth > 900){  // only for desktop
        e.preventDefault();
        gameList.scrollBy({ top: e.deltaY, behavior: 'smooth' });
    }
});

document.querySelectorAll(".game-item").forEach(card => {
    let game = card.dataset.game;

    if(game.includes("2048")){
        game = "2048";
    }else{
        game = game.split("/").pop().replace(".php","");
    }

    const img = card.querySelector(".game-img");

    if(previews[game]){
        let i = 0;
        setInterval(() => {
            img.src = previews[game][i];
            i = (i + 1) % previews[game].length;
        }, 1000);
    }
});
</script>

</body>
</html>