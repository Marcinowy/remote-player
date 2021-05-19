<?php

class rmfon
{
    public $id;
    public function __construct($id = 0)
    {
        if (!is_numeric($id) || $id <= 0) throw new Exception('Wrong ID');
        $this->id = (int)$id;
    }
    public function getStreamUrls()
    {
        $url = 'http://www.rmfon.pl/stacje/flash_aac_';
        $url .= $this->id;
        $url .= '.xml.txt';

        $xml = new SimpleXMLElement(file_get_contents($url));
        $list = $xml->playlistMp3->item_mp3;
        $links = [];
        for ($i=0; $i<count($list); $i++)
            $links[] = (string)$list[$i];

        return $links;
    }
    public function getCurrentMusic()
    {
        $url = 'http://rmfon.pl/stacje/playlista_';
        $url .= $this->id;
        $url .= '.json.txt';
        
        $music = file_get_contents($url);
        $music = json_decode($music, true);
        $music = $music[array_search(0, array_column($music, 'order'))];
        return $music;
    }
    public static function getStationsList()
    {
        $stations = json_decode(file_get_contents('http://www.rmfon.pl/json/stations.txt'), true);
        $stations = array_map(function($val) {
            return [
                'id' => $val['id'],
                'name' => $val['name']
            ];
        }, $stations);

        return $stations;
    }
}
