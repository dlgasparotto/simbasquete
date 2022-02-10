<?
$db = new PDO("mysql:host=localhost; dbname=nba", "root", "");
$mapFile = file_get_contents('mapplayers.json');
$campos = json_decode($mapFile);
//var_dump($campos);


$csv = file_get_contents('players.csv');
$linhas = explode("\r\n", $csv);
//var_dump($linhas);

$cab = explode(";", $linhas[0]);
//var_dump($cab);

foreach($campos as $campo){
  $campo->posicao = array_search($campo->from, $cab);
}
//var_dump($campos);

$linhascsv = array_slice($linhas, 1);
$players = [];
foreach($linhascsv as $linhacsv){
  $alinhacsv = explode(";", $linhacsv);
  
  if ($alinhacsv[1] <> '' and $alinhacsv[2] <> '') {

    $player = new stdClass;
    foreach($campos as $campo){
      $atr = isSet($campo->to) ? $campo->to : $campo->from;
      $player->$atr = $alinhacsv[$campo->posicao];
      if ($atr == 'PLAYER') {
        $player->$atr = clearName($player->$atr);
      }
      if ($atr == 'POSITION') {
        setPositions($player);
      }
      if ($atr == 'HEIGHT') {
        $player->$atr = (int) convInchsToCm($player->$atr);
      }
      if ($atr == 'WEIGHT') {
        $player->$atr = fnum($player->$atr);
        $player->$atr = (int) convLibraToKg($player->$atr);
      }
    }
    array_push($players, $player);
  }
  
  
}
var_dump($players);
//die();

updatePlayers($players);


function updatePlayers($players) {

  global $db;

  foreach($players as $player){
    $rs = $db->query("select id from players where player = '$player->PLAYER'");
    if ($existe = $rs->fetchObject()) {
      echo "update players set 
      team = '$player->TEAM', 
      number = '$player->NUMBER',
      position1 = '$player->POSITION1',
      position2 = '$player->POSITION2',
      height = '$player->HEIGHT',
      weight = '$player->WEIGHT',
      country = '$player->COUNTRY'
      where id = '$existe->id'; <br>";
    } else {
      echo "insert into players
        (player, team, number, position1, position2, height, weight, country) 
        values 
        ('$player->PLAYER','$player->TEAM','$player->NUMBER',
         '$player->POSITION1','$player->POSITION2','$player->HEIGHT',
         '$player->WEIGHT','$player->COUNTRY'); <br>";
    }
  }

}

function convLibraToKg($libra){

  return ((float) $libra) * 0.453592;

}

function convInchsToCm($height){

  $heightInch = explode('-', $height);
  $inchs = (float) $heightInch[0];
  $polegadas = (float) (count($heightInch) > 1 ? $heightInch[1] : 0);
  $heightCm = $inchs * 30.48 + $polegadas * 2.54;

  return $heightCm;

}

function setPositions($player) {

  $positions = explode('-', $player->POSITION);
  $player->POSITION1 = $positions[0];
  $player->POSITION2 = count($positions) > 1 ? $positions[1] : '';

}

function fnum($txt){

  return preg_replace('/\D/', '', $txt);

}

function clearName($txt){

  $temp = str_replace(' Headshot', '', $txt);
  $temp = str_replace("'", "''", $temp);
  
  return $temp;

}


?>



