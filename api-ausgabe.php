<?php
require_once './default.php';

header('Content-Type: application/json');

// Parameter: hours oder dateFrom/dateTo
$dateFrom = DateTime::createFromFormat('Y-m-d', filter_input(INPUT_GET, 'dateFrom', FILTER_UNSAFE_RAW));
$dateTo = DateTime::createFromFormat('Y-m-d', filter_input(INPUT_GET, 'dateTo', FILTER_UNSAFE_RAW));
if(null != $dateTo){
    $dateTo->modify('+1 day');
}
$hours = filter_input(INPUT_GET, 'hours', FILTER_SANITIZE_NUMBER_INT);



if ($hours) {
    $sql = 'SELECT * FROM `temperaturen` WHERE `zeit` >= NOW() - INTERVAL :hours HOUR ORDER BY `zeit` ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hours' => (int)$hours]);
} elseif ($dateFrom && $dateTo) {
    $sql = 'SELECT * FROM `temperaturen` WHERE `zeit` >= :dateFrom AND `zeit` < :dateTo ORDER BY `zeit` ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['dateFrom' => $dateFrom->format('Y-m-d'), 'dateTo' => $dateTo->format('Y-m-d')]);
} else {
    // Default: letzte 24 Stunden
    $sql = 'SELECT * FROM `temperaturen` WHERE `zeit` >= NOW() - INTERVAL 24 HOUR ORDER BY `zeit` ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
?>
