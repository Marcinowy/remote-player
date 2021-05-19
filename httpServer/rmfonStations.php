<?php

header('Content-type: application/json');

include '../includes/autoload.php';

echo json_encode(rmfon::getStationsList(), JSON_PRETTY_PRINT);
