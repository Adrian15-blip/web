<?php

// api/get_suggestions.php

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['service_id'], $data['workers'], $data['days'], $data['transport_cost'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$service_id = (int)$data['service_id'];
$workers = (int)$data['workers'];
$days = (int)$data['days'];
$transport_cost = (float)$data['transport_cost'];

// Obtener datos históricos
$stmt = $conn->prepare("
    SELECT AVG(workers) as avg_workers, AVG(days) as avg_days, AVG(transport_cost) as avg_transport
    FROM quotes
    WHERE service_id = ? AND status = 'aprobada'
");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// Obtener sugerencias de servicios adicionales y precios
$additional_services = [
    ["name" => "Equipo de seguridad adicional", "price" => 200],
    ["name" => "Inspección posterior al servicio", "price" => 150],
    ["name" => "Limpieza profunda", "price" => 100]
    
];

$suggestions = [];

// Comparar con promedios
if ($workers < $result['avg_workers']) {
    $suggestions[] = "Considere aumentar el número de trabajadores a " . ceil($result['avg_workers']) . " para este servicio.";
}

if ($days < $result['avg_days']) {
    $suggestions[] = "La duración promedio para este servicio es de " . ceil($result['avg_days']) . " días. Considere ajustar su estimación.";
}

if ($days > 5) {
    $suggestions[] = "Para proyectos largos, considera un equipo de supervisión para asegurar la calidad.";
}

if ($service_id == 1 && $days > 10) { // Ejemplo de tipo de servicio específico
    $suggestions[] = "Recomendamos incluir servicios de inspección y monitoreo cada 10 dias para este tipo de proyecto.";
}


if ($service_id == 2 && $workers < 8) { // Ejemplo de sugerencia para otro servicio
    $suggestions[] = "Para este tipo de servicio, se recomienda un mínimo de 8 trabajadores para garantizar la eficiencia.";
}

// Sugerencias sobre costos de transporte
if (isset($result['avg_transport']) == 1 && $transport_cost <50) {
    $suggestions[] = "El costo promedio de transporte para proyectos similares es de S/50.".number_format($result['avg_transport'], 2) . ". Verifique el presupuesto de transporte.";

}

// Recomendaciones de materiales y detalles adicionales
if (!empty($result['materials'])) {
    $suggestions[] = "Materiales recomendados para este servicio incluyen: " . $result['materials'] . ".";
}

if (!empty($result['additional_details'])) {
    $suggestions[] = "Detalles adicionales: " . $result['additional_details'] . ".";
}

// Sugerencia del costo total basado en datos históricos
if (isset($result['total_cost'])) {
    $suggestions[] = "El costo total promedio para proyectos similares es de S/" . number_format($result['total_cost'], 2) . ".";
}

// Sugerir servicios adicionales
foreach ($additional_services as $service) {
    $suggestions[] = "Agregar {$service['name']} por un costo adicional de S/{$service['price']}.";
}

echo json_encode(['suggestions' => $suggestions]);
exit;

?>
