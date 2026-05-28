<?php
require_once './default.php';

header('Content-Type: application/json');

// Parameter: limit (Anzahl der letzten Datensätze) oder hours (Stunden zurück)
$hours = filter_input(INPUT_GET, 'hours', FILTER_SANITIZE_NUMBER_INT);

if ($hours) {
    $sql = 'SELECT * FROM `temperaturen` WHERE `zeit` >= NOW() - INTERVAL :hours HOUR ORDER BY `zeit` ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hours' => (int)$hours]);
} else {
    // Default: letzte 24 Stunden
    $sql = 'SELECT * FROM `temperaturen` WHERE `zeit` >= NOW() - INTERVAL 24 HOUR ORDER BY `zeit` ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
?>
