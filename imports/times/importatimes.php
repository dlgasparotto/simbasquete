<?
$db = new PDO("mysql:host=localhost; dbname=nba", "root", "");
$mapFile = file_get_contents('maptimes.json');
$campos = json_decode($mapFile);
var_dump($campos);

$csv = file_get_contents('times.csv');
$linhas = explode("\r\n", $csv);
//var_dump($linhas);

$cab = explode(";", $linhas[0]);
//var_dump($cab);

foreach($campos as $campo){
  $campo->posicao = array_search($campo->from, $cab);
}
//var_dump($campos);

$timescsv = array_slice($linhas, 1);
$times = [];
foreach($timescsv as $timecsv){
  $atimecsv = explode(";", $timecsv);
  $time = new stdClass;
  foreach($campos as $campo){
    $atr = isSet($campo->to) ? $campo->to : $campo->from;
    $time->$atr = $atimecsv[$campo->posicao];
  }
  array_push($times, $time);
}
//var_dump($times);


foreach($times as $time){
  $rs = $db->query("select time from times where time = '$time->TEAM'");
  if ($existe = $rs->fetchObject()) {
    echo "update times set posicao = '$time->POS', apr = '$time->WINP' where time = '$time->TEAM'; <br>";
  } else {
    echo "insert into times(time, posicao, apr) values ('$time->TEAM', '$time->POS', '$time->WINP'); <br>";
  }
}

?>



