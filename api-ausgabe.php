<?php
require_once './default.php';

/*
 * Dieses Script soll die Temperaturen aus der Datenbank ausgeben.
 */
//ini_set('log_errors', TRUE);
//ini_set('error_log', 'error.log');
//error_log(var_export($_POST, TRUE));
//error_log(var_export($_GET, TRUE));

//$wert = filter_input(INPUT_POST, 'wert', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$date_start_object = new DateTime();
$date_start_object->sub(new DateInterval('PT360S'));

$stmt = $pdo->prepare('SELECT * FROM `temperaturen` WHERE `zeit` >= :start_zeit;');
//$result = $stmt->execute(array('start_zeit' => $date_start_object->getTimestamp()));
$result = $stmt->execute(array('start_zeit' => $date_start_object->format('c')));
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $data[] = $row;
}


echo json_encode($data);
