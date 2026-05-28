<?php

require_once './default.php';
error_log("Starte Temperatureingabe");
/*
 * Dieses Script soll die Temperaturen von der Apotheke empfangen.
 */
//ini_set('log_errors', TRUE);
//ini_set('error_log', 'error.log');
error_log(var_export($_POST, TRUE));
//error_log(var_export($_GET, TRUE));


$wert = filter_input(INPUT_POST, 'wert', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$ident_nummer = filter_input(INPUT_POST, 'ident_nummer', FILTER_SANITIZE_NUMBER_INT);
$typ = filter_input(INPUT_POST, 'typ', FILTER_SANITIZE_NUMBER_INT);
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$name = mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1');
$zeit = filter_input(INPUT_POST, 'zeit', FILTER_SANITIZE_NUMBER_INT); //UNIX
$inaktiv_seit = filter_input(INPUT_POST, 'inaktiv_seit', FILTER_SANITIZE_NUMBER_INT);
$rssi_value = filter_input(INPUT_POST, 'rssi_value', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

if (empty($wert)){
    die('Es wurden keine Werte eingegeben.');
}

$stmt = $pdo->prepare('INSERT INTO `temperaturen` SET
`wert` = :wert,
`ident_nummer` = :ident_nummer,
`typ` = :typ,
`name` = :name,
`zeit` = :zeit,
`inaktiv_seit` = :inaktiv_seit,
`rssi_value` = :rssi_value
');
$stmt->execute(array(
'wert' => $wert,
'ident_nummer' => $ident_nummer,
'typ' => $typ,
'name' => $name,
'zeit' => $zeit, //TODO: klappt das?
'inaktiv_seit' => $inaktiv_seit,
'rssi_value' => $rssi_value
));

error_log("Eingetragen $name");

echo "Done";
