<?php
require_once '../config/database.php';

if (isset($_GET['service_id'])) {
    $service_id = (int)$_GET['service_id'];
    $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();

    echo json_encode(['price' => $price]);
} else {
    echo json_encode(['error' => 'ID de servicio no proporcionado']);
}
?>
