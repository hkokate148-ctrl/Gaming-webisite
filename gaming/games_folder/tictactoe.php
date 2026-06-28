<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// ================== INITIALIZE SCORES ==================
if(!isset($_SESSION['player_score'])) $_SESSION['player_score'] = 0;
if(!isset($_SESSION['computer_score'])) $_SESSION['computer_score'] = 0;
if(!isset($_SESSION['last_winner'])) $_SESSION['last_winner'] = "";

// ================== INITIALIZE BOARD ==================
$board = isset($_POST['board']) ? explode(",", $_POST['board']) : array_fill(0, 9, "");
$winner = "";
$winningCells = [];

// ================== WINNER CHECK FUNCTION ==================
function checkWinner($b){
    $winLines = [
        [0,1,2],[3,4,5],[6,7,8],
        [0,3,6],[1,4,7],[2,5,8],
        [0,4,8],[2,4,6]
    ];
    foreach($winLines as $line){
        if($b[$line[0]] != "" && $b[$line[0]] == $b[$line[1]] && $b[$line[1]] == $b[$line[2]]){
            return [$b[$line[0]], $line];
        }
    }
    if(!in_array("", $b)) return ["Draw", []];
    return ["", []];
}

// ================== COMPUTER MOVE FUNCTION ==================
function computerMove(&$b){
    // Try to win
    for($i=0;$i<9;$i++){
        if($b[$i]==""){
            $b[$i] = "O";
            list($win,$line)=checkWinner($b);
            if($win=="O") return;
            $b[$i]="";
        }
    }
    // Try to block player
    for($i=0;$i<9;$i++){
        if($b[$i]==""){
            $b[$i]="X";
            list($win,$line)=checkWinner($b);
            if($win=="X"){
                $b[$i]="O";
                return;
            }
            $b[$i]="";
        }
    }
    // Random move
    $empty = [];
    foreach($b as $i=>$c){ if($c=="") $empty[]=$i; }
    if(!empty($empty)) $b[$empty[array_rand($empty)]]="O";
}

// ================== PLAYER MOVE ==================
if(isset($_POST['cell'])){
    $index = $_POST['cell'];
    if($board[$index] == ""){
        $board[$index] = "X";
        list($winner,$winningCells) = checkWinner($board);
        if($winner=="") computerMove($board);
    }
}

// ================== CHECK WINNER AFTER COMPUTER MOVE ==================
list($winner,$winningCells) = checkWinner($board);

// ================== UPDATE SCORES ==================
if($winner!="" && $_SESSION['last_winner']!=$winner){
    if($winner=="X") $_SESSION['player_score']++;
    elseif($winner=="O") $_SESSION['computer_score']++;
    $_SESSION['last_winner']=$winner;
}

// ================== RESET GAME ==================
if(isset($_POST['reset'])){
    $_SESSION['player_score'] = 0;
    $_SESSION['computer_score'] = 0;
    $_SESSION['last_winner'] = "";
    $board = array_fill(0, 9, "");
    $winner = "";
    $winningCells = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tic Tac Toe</title>
<style>
body {
    margin:0; font-family:Arial,sans-serif; text-align:center;
    background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
}

.topbar{
    width:100%; max-width:600px; margin:auto;
    display:flex; justify-content:flex-start; padding:10px 0;
}

.backbtn{
    background:#ff8c00; color:white; border:none;
    padding:10px 20px; font-size:16px;
    border-radius:8px; cursor:pointer;
}
.backbtn:hover{ background:#e67600; }

h1{ margin-top:30px; text-shadow:0 0 15px cyan; }

.score-box{
    margin-top:20px; font-size:18px;
    background:rgba(0,0,0,0.3);
    display:inline-block; padding:10px 20px; border-radius:15px;
}

.result{ margin-top:20px; font-size:22px; color:yellow; }

.board{
    display:grid; grid-template-columns:repeat(3,100px);
    grid-template-rows:repeat(3,100px);
    gap:10px; justify-content:center; margin:30px auto;
}

.cell{
    font-size:40px; font-weight:bold;
    border-radius:15px; border:none;
    background:rgba(255,255,255,0.05);
    cursor:pointer; transition:0.3s;
    display:flex; align-items:center; justify-content:center;
}
.cell:hover{ background:rgba(0,255,255,0.3); }
.cell.x{ color:#00ffff; }
.cell.o{ color:#ff9933; }
.cell.winning{ background:rgba(255,255,0,0.4); color:#ffff00; }

button.restart{
    margin-top:20px; padding:10px 25px;
    background:cyan; border:none; border-radius:20px;
    cursor:pointer; font-weight:bold; transition:0.3s;
}
button.restart:hover{ box-shadow:0 0 15px cyan; }
button[disabled]{ cursor:default; opacity:0.7; }

@media(max-width:400px){
    .board{ grid-template-columns:repeat(3,70px); grid-template-rows:repeat(3,70px); }
    .cell{ font-size:30px; }
}
</style>
</head>
<body>

<h1>Tic Tac Toe</h1>

<div class="score-box">
You (X): <?php echo $_SESSION['player_score']; ?> | Computer (O): <?php echo $_SESSION['computer_score']; ?>
</div>

<form method="POST">
<input type="hidden" name="board" value="<?php echo implode(",",$board); ?>">
<div class="board">
<?php
foreach($board as $i=>$c){
    $class="";
    if($c=="X") $class="x";
    elseif($c=="O") $class="o";
    if(in_array($i,$winningCells)) $class.=" winning";
    $disabled = ($winner!="" || $c!="") ? "disabled" : "";
    echo '<button class="cell '.$class.'" type="submit" name="cell" value="'.$i.'" '.$disabled.'>'.$c.'</button>';
}
?>
</div>
</form>

<?php if($winner!=""){ ?>
<div class="result">
<?php echo $winner=="Draw"?"Game Draw! 🤝":"Winner: ".$winner." 🎉"; ?>
</div>
<?php } ?>

<form method="POST">
<button class="restart" name="reset">Restart Game</button>
</form>

</body>
</html>