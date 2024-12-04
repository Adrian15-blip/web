<?php

session_start();
require_once '../config/database.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado. Por favor, inicie sesión.");
}

// Función para registrar en logs
function logAction($user_id, $action, $description) {
    global $conn; // Conexión a la base de datos

    // Verificar que los parámetros sean válidos
    if (empty($user_id) || empty($action) || empty($description)) {
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, description) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("iss", $user_id, $action, $description);
    $stmt->execute();
    $stmt->close();
    return true;
}

// Registrar acción de creación de cotización
$user_id = $_SESSION['user_id']; // ID del usuario actual
$action = "Creación de cotización";
$id = rand(1, 9); // Simulación del ID de cotización
$description = "El usuario creó una nueva cotización con ID: " . $id;

// Registrar acción en los logs
logAction($user_id, $action, $description);

// Consultar logs
$sql = "SELECT logs.id, users.name AS user_name, logs.action, logs.description, logs.created_at 
        FROM logs 
        JOIN users ON logs.user_id = users.id 
        ORDER BY logs.created_at DESC";
$result = $conn->query($sql);

// Verificar si hubo un error en la consulta
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Sistema de Cotizaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Registro de Actividades</h1>
    <table class="table table-striped">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Descripción</th>
            <th>Fecha</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['action']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>



