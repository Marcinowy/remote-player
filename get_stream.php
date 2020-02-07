<?php
function get_stream_urls($id) {
	$code=file_get_contents("http://www.rmfon.pl/stacje/flash_aac_".$id.".xml.txt");
	$code=explode("<playlistMp3>",$code);
	$code=explode("</playlistMp3>",$code[1]);
	$code=substr($code[0], 23, strlen($code[0])-29);
	$url=explode("</item_mp3>\n    <item_mp3 ads=\"1\">",$code);
	return $url[0];
}
$options = getopt("i:");
echo get_stream_urls($options["i"]);
?>