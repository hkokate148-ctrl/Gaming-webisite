<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// ================= SETTINGS =================
$boardSize = 100;

$snakes = [
    98=>61,
    90=>47,
    62=>44,
    36=>7,
];

$ladders = [
    9=>33,
    18=>24,
    41=>59,
    45=>77,
    66=>87,
];

// =============== SESSION INIT ===============
if(!isset($_SESSION['player'])) $_SESSION['player']=1;
if(!isset($_SESSION['computer'])) $_SESSION['computer']=1;
if(!isset($_SESSION['winner'])) $_SESSION['winner']="";
if(!isset($_SESSION['player_event'])) $_SESSION['player_event']="";
if(!isset($_SESSION['computer_event'])) $_SESSION['computer_event']="";

// =============== PLAY TURN ===================
function playTurn($key,$name,$snakes,$ladders,$boardSize){

    $_SESSION[$key."_prev"] = $_SESSION[$key];
    $_SESSION[$key."_event"] = "";   // reset

    $dice = rand(1,6);
    $_SESSION[$key."_dice"]=$dice;

    if($_SESSION[$key] + $dice <= $boardSize){
        $_SESSION[$key] += $dice;
    }

    if(isset($ladders[$_SESSION[$key]])){
        $_SESSION[$key."_event"] = "ladder";
        $_SESSION[$key] = $ladders[$_SESSION[$key]];
    }
    elseif(isset($snakes[$_SESSION[$key]])){
        $_SESSION[$key."_event"] = "snake";
        $_SESSION[$key] = $snakes[$_SESSION[$key]];
    }

    if($_SESSION[$key] == $boardSize){
        $_SESSION['winner'] = $name;
        $_SESSION[$key."_event"] = "win";
    }

    if($key == "player") $_SESSION['player_event'] = $_SESSION[$key."_event"];
    if($key == "computer") $_SESSION['computer_event'] = $_SESSION[$key."_event"];
}

// =============== BUTTON ACTIONS ===============
if(isset($_POST['roll']) && $_SESSION['winner']==""){
    playTurn("player","Player",$snakes,$ladders,$boardSize);

    if($_SESSION['winner']==""){
        playTurn("computer","Computer",$snakes,$ladders,$boardSize);
    }
}

if(isset($_POST['reset'])){
    session_destroy();
    header("Location: snakes_ladders.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Snake & Ladder</title>
<style>
body{
    background:#222;
    text-align:center;
    color:white;
    font-family:Arial;
}

.topbar{
    width:600px;
    margin:auto;
    display:flex;
    justify-content:flex-start;
    gap:10px;
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

.board{
    position:relative;
    width:600px;
    height:600px;
    margin:20px auto;
    background:url("../images/board.jpg") no-repeat center;
    background-size:600px 600px;
}
.token{
    position:absolute;
    width:40px;
    height:40px;
    font-size:30px;
    text-align:center;
    line-height:40px;
    border-radius:50%;
    pointer-events:none;
    transition: transform 0.2s;
}
.target{
    position:absolute;
    width:30px;
    height:30px;
    font-size:25px;
    display:none;
    pointer-events:none;
}
.player{background:#00bfff;} /* blue */
.computer{background:#ff8c00;} /* orange */

button{
    padding:10px 20px;
    font-size:18px;
    margin:10px;
    cursor:pointer;
}
.winner{
    font-size:25px;
    color:yellow;
}
#dice{
    display:inline-block;
    font-size:60px;
    margin-left:20px;
    vertical-align:middle;
}
</style>
</head>
<body>

<h1>🐍 Snake & Ladder 🪜</h1>

<?php if($_SESSION['winner']!=""){ ?>
<div class="winner">
🏆 <?php echo $_SESSION['winner']; ?> Wins!
</div>
<?php } ?>

<div class="board">
    <div id="player" class="token player">🐱</div>
    <div id="computer" class="token computer">🐶</div>
    <div id="targetPoint" class="target">📍</div>
</div>

<form method="POST">
<?php if($_SESSION['winner']==""){ ?>
<button type="submit" name="roll">🎲 Roll Dice</button>
<div id="dice">⚀</div>
<?php } ?>
<button type="submit" name="reset">🔄 Restart</button>
</form>

<!-- Sounds -->
<audio id="diceSound" src="../sounds/snake_ladder/dice.mp3"></audio>
<audio id="snakeSound" src="../sounds/snake_ladder/snake.mp3"></audio>
<audio id="ladderSound" src="../sounds/snake_ladder/ladder.mp3"></audio>
<audio id="winSound" src="../sounds/snake_ladder/win.mp3"></audio>

<script>
let playerEvent = "<?php echo $_SESSION['player_event']; ?>";
let computerEvent = "<?php echo $_SESSION['computer_event']; ?>";

function getPosition(square){
    const boardSize = 600;
    const cellSize = boardSize / 10;
    let row = Math.floor((square - 1) / 10);
    let col = (square - 1) % 10;
    if(row % 2 === 1) col = 9 - col;
    let x = col * cellSize + cellSize/2 - 15;
    let y = boardSize - ((row + 1) * cellSize) + cellSize/2 - 15;
    return {x, y};
}

function moveSmooth(id, start, end, callback){
    let current = start;
    let stepValue = start < end ? 1 : -1;
    function step(){
        if(current === end){
            document.getElementById("targetPoint").style.display="none";
            if(callback) callback();
            return;
        }
        current += stepValue;
        let pos = getPosition(current);
        let el = document.getElementById(id);
        el.style.left = pos.x + "px";
        el.style.top = pos.y + "px";
        setTimeout(step,120);
    }
    step();
}

function animateDice(finalNumber){
    document.getElementById("diceSound").play();
    let dice = document.getElementById("dice");
    const diceFaces = ["","⚀","⚁","⚂","⚃","⚄","⚅"];
    let counter = 0;
    let rolling = setInterval(function(){
        let random = Math.floor(Math.random()*6)+1;
        dice.innerHTML = diceFaces[random];
        counter++;
        if(counter>12){
            clearInterval(rolling);
            dice.innerHTML = diceFaces[finalNumber];
        }
    },80);
}

let playerStart = <?php echo isset($_SESSION['player_prev'])?$_SESSION['player_prev']:$_SESSION['player']; ?>;
let playerEnd = <?php echo $_SESSION['player']; ?>;
let computerStart = <?php echo isset($_SESSION['computer_prev'])?$_SESSION['computer_prev']:$_SESSION['computer']; ?>;
let computerEnd = <?php echo $_SESSION['computer']; ?>;

window.onload = function(){
    let target = document.getElementById("targetPoint");

<?php if(isset($_POST['roll'])){ ?>
    let targetPos = getPosition(playerEnd);
    target.style.left = targetPos.x + "px";
    target.style.top = targetPos.y + "px";
    target.style.display = "block";
<?php } ?>

    let p = getPosition(playerStart);
    document.getElementById("player").style.left = p.x + "px";
    document.getElementById("player").style.top = p.y + "px";

    let c = getPosition(computerStart);
    document.getElementById("computer").style.left = c.x + "px";
    document.getElementById("computer").style.top = c.y + "px";

<?php if(isset($_POST['roll'])){ ?>
    animateDice(<?php echo $_SESSION['player_dice']; ?>);
    function moveSmooth(id, start, end, eventType, callback){
    let current = start;
    let stepValue = start < end ? 1 : -1;

    function step(){
        if(current === end){

            // 🎯 FINAL POSITION par event sound
            if(eventType === "snake"){
                document.getElementById("snakeSound").play();
            }
            if(eventType === "ladder"){
                document.getElementById("ladderSound").play();
            }
            if(eventType === "win"){
                document.getElementById("winSound").play();
            }

            document.getElementById("targetPoint").style.display="none";
            if(callback) callback();
            return;
        }

        current += stepValue;

        let pos = getPosition(current);
        let el = document.getElementById(id);
        el.style.left = pos.x + "px";
        el.style.top = pos.y + "px";

        setTimeout(step,120);
    }

    step();
}
    setTimeout(function(){
        moveSmooth("player", playerStart, playerEnd, playerEvent, function(){
            if(computerEvent === "snake") document.getElementById("snakeSound").play();
            if(computerEvent === "ladder") document.getElementById("ladderSound").play();
            if(computerEvent === "win") document.getElementById("winSound").play();
            setTimeout(function(){
               moveSmooth("computer", computerStart, computerEnd, computerEvent);
            },600);
        });
    },500);
<?php } ?>
};
</script>

</body>
</html>