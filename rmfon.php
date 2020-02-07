<?php
header('Content-type: application/json');
$stations=json_decode(file_get_contents("http://www.rmfon.pl/json/stations.txt"),true);
for ($i=0;$i<count($stations);$i++) {
	$res[]=array("id"=>$stations[$i]["id"],"name"=>$stations[$i]["name"]);
}
echo json_encode($res);
?>