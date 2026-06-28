<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user'])){
    echo "<h2 style='color:white;text-align:center;'>Please Login First</h2>";
    exit();
}

// ✅ ONLY ONE DB CONNECTION
include "../db.php";

// ✅ INSERT SCORE
if(isset($_POST['name']) && $_POST['name'] != ""){
    $name = $conn->real_escape_string($_POST['name']);
    $score = (int)$_POST['score'];

    $conn->query("INSERT INTO scores (username, game_name, score) 
                  VALUES ('$name', 'car_racing', '$score')");
    exit();
}

// ✅ FETCH LEADERBOARD
$topScores = $conn->query("SELECT username, score 
                          FROM scores 
                          WHERE game_name='car_racing'
                          ORDER BY score DESC 
                          LIMIT 3");
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Car Racing Game</title>
<style>
body{
    margin:0;
    background:#111;
    font-family:Arial;
    color:white;
    text-align:center;
}

.topbar{
    width:600px;
    margin:auto;
    display:flex;
    justify-content:space-between;
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

h2{
    margin:10px 0;
}

#score{
    font-size:22px;
    margin:10px 0;
}

#gameArea{
    width:90%;
    max-width:400px;
    height:600px;
    background:#222;
    overflow:hidden;
    position:relative;
    border:5px solid white;
    margin:auto;
}

#road {
    position: absolute;
    width: 100%;
    height: 200%;
    background: url('../images/road.png') repeat-y;
    animation: roadMove var(--roadSpeed) linear infinite;
}

:root{
    --roadSpeed:2s;
}

@keyframes roadMove{
    0%{top:-600px;}
    100%{top:0;}
}

#player {
    width: 50px;
    height: 90px;
    position: absolute;
    bottom: 20px;
    left: 175px;
    background: url('../images/player_car.png') no-repeat center/cover;
}

.enemy {
    width: 50px;
    height: 90px;
    position: absolute;
    top: -100px;
    background: url('../images/enemy_car.png') no-repeat center/cover;
}

button{
    padding:12px 25px;
    font-size:18px;
    margin:5px;
    cursor:pointer;
}

#startBtn{
    background:lime;
}

/* Main container: score - game - leaderboard */
#mainContainer{
    display:flex;
    justify-content:center;
    align-items:flex-start;
    gap:20px;
    margin-top:10px;
}

/* Left: Score */
#scoreContainer{
    width:100px;
    text-align:left;
}

/* Right: Leaderboard */
#leaderboard{
    width:160px;
    background: rgba(0,0,0,0.7);
    padding:10px;
    border:2px solid white;
    color:white;
    font-size:16px;
    text-align:left;
}

#leaderboard h3{
    text-align:center;
    margin-top:0;
}

#leaderboard ol{
    margin:5px 0 0 15px;
    padding:0;
}

/* Controls below game */
.controls{
    margin-top:10px;
    display:flex;
    justify-content:center;
    gap:10px;
}

/* ===== MOBILE RESPONSIVE ===== */
@media(max-width:768px){
    #mainContainer{
        flex-direction:column;
        align-items:center;
    }
    #scoreContainer, #leaderboard{
        width:90%;
        text-align:center;
    }
    #gameArea{
        width:90%;
    }
    .controls{
        flex-direction:row;
    }
}
</style>
</head>
<body>

  <audio id="slowEngine" preload="auto">
    <source src="/gaming/sounds/car/slow.mp3" type="audio/mpeg">
</audio>

<audio id="mediumEngine" preload="auto">
    <source src="/gaming/sounds/car/medium.mp3" type="audio/mpeg">
</audio>

<audio id="fastEngine" preload="auto">
    <source src="/gaming/sounds/car/fast.mp3" type="audio/mpeg">
</audio>

<audio id="explosionSound" preload="auto">
    <source src="/gaming/sounds/car/explosion.mp3" type="audio/mpeg">
</audio>


<h2>Car Racing Game</h2>

<div id="mainContainer">
    <!-- Left: Score -->
    <div id="scoreContainer">
        <div id="score">Score: 0</div>
        <button id="startBtn" onclick="startGame()">START</button>
    </div>

    <!-- Center: Game Area -->
    <div id="gameArea">
        <div id="road"></div>
        <div id="player"></div>
    </div>

    <!-- Right: Leaderboard -->
    <div id="leaderboard">
        <h3>🏆 Leaderboard</h3>
        <ol>
        <?php
        if($topScores->num_rows > 0){
            while($row = $topScores->fetch_assoc()){
                echo "<li>".$row['username']." - ".$row['score']."</li>";
            }
        }
        ?>
        </ol>
    </div>
</div>

<!-- Controls -->
<div class="controls">
    <button onclick="moveLeft()">⬅</button>
    <button onclick="moveRight()">➡</button>
</div>

<audio id="crashSound" preload="auto">
    <source src="https://www.soundjay.com/mechanical/sounds/car-crash-1.mp3" type="audio/mpeg">
</audio>

<script>
    let currentEngine = null;
let slowSound = document.getElementById("slowEngine");
let mediumSound = document.getElementById("mediumEngine");
let fastSound = document.getElementById("fastEngine");

let explosionSound = document.getElementById("explosionSound");
let road = document.getElementById("road");
let player=document.getElementById("player");
let gameArea=document.getElementById("gameArea");
let scoreText=document.getElementById("score");

let playerX=175;
let score=0;
let gameRunning=false;
let speed = 5; // default enemy speed

let enemyInterval;
let scoreInterval;

function startGame(){
    score=0;
    scoreText.innerText="Score: 0";
    gameRunning=true;
    document.getElementById("startBtn").style.display="none";

    slowSound.load();
mediumSound.load();
fastSound.load();

    updateEngineSound();

    enemyInterval=setInterval(createEnemy,2000);
    scoreInterval=setInterval(function(){
    score += Math.floor(speed/2);
    scoreText.innerText="Score: "+score;
},1000);
}

function moveLeft(){
    if(!gameRunning) return;
    if(playerX>0){
        playerX-=50;
        player.style.left=playerX+"px";
    }
}

function moveRight(){
    if(!gameRunning) return;
    if(playerX<350){
        playerX+=50;
        player.style.left=playerX+"px";
    }
}

function createEnemy(){
    if(!gameRunning) return;

    let enemy=document.createElement("div");
    enemy.classList.add("enemy");
    enemy.style.left=Math.floor(Math.random()*7)*50+"px";
    gameArea.appendChild(enemy);

    let enemyY=-100;

    let move=setInterval(function(){
        if(!gameRunning){
            clearInterval(move);
            enemy.remove();
            return;
        }

       enemyY += speed;
        enemy.style.top=enemyY+"px";

        let playerRect=player.getBoundingClientRect();
        let enemyRect=enemy.getBoundingClientRect();

        if(
            playerRect.left<enemyRect.right &&
            playerRect.right>enemyRect.left &&
            playerRect.top<enemyRect.bottom &&
            playerRect.bottom>enemyRect.top
        ){
            explosionSound.currentTime = 0;
            explosionSound.play();
            clearInterval(move);
            gameOver();
        }

        if(enemyY>600){
            enemy.remove();
            clearInterval(move);
        }
    },30)
}

function updateScore(){
    if(!gameRunning) return;
    score++;
    scoreText.innerText="Score: "+score;
}

function gameOver() {
    gameRunning = false;
    clearInterval(enemyInterval);
    clearInterval(scoreInterval);

    let name = "<?php echo $_SESSION['user']; ?>";

    let formData = new FormData();
    formData.append("name", name);
    formData.append("score", score);

    fetch("", {
        method: "POST",
        body: formData
    }).then(() => {
        alert("Score Saved!");
        location.reload();
    });
}
// Keyboard support: arrows
document.addEventListener("keydown", function(e){
    if(!gameRunning) return;

    if(e.key === "ArrowLeft"){   // Left arrow
        moveLeft();
    }
    else if(e.key === "ArrowRight"){  // Right arrow
        moveRight();
    }
    else if(e.key === "ArrowUp"){     
    speed += 2;
    if(speed > 20) speed = 20;

    document.documentElement.style.setProperty('--roadSpeed', (2 - speed*0.05)+'s');
    updateEngineSound();
}
else if(e.key === "ArrowDown"){   
    speed -= 2;
    if(speed < 2) speed = 2;

    document.documentElement.style.setProperty('--roadSpeed', (2 - speed*0.05)+'s');

    updateEngineSound();
}
});

function updateEngineSound(){

let newSound;

if(speed <= 6){
    newSound = slowSound;
}
else if(speed <= 12){
    newSound = mediumSound;
}
else{
    newSound = fastSound;
}

if(currentEngine !== newSound){

    if(currentEngine){
        currentEngine.pause();
        currentEngine.currentTime = 0;
    }

    currentEngine = newSound;
    currentEngine.currentTime = 0;
    currentEngine.loop = true;
    currentEngine.play();
}

}

</script>

</body>
</html>