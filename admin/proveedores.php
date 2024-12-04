<?php
require_once '../config/database.php';

// Eliminar proveedor
if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = intval($_GET['id']); // Convertir a entero para evitar inyecciones
    $stmt = $conn->prepare("DELETE FROM proveedores WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: proveedores.php?msg=Proveedor eliminado exitosamente");
        exit;
    } else {
        echo "Error al eliminar el proveedor: " . $stmt->error;
    }
    $stmt->close();
}

// Registrar proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $contact_name = $_POST['contact_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    $stmt = $conn->prepare("INSERT INTO proveedores (name, contact_name, phone, email, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact_name, $phone, $email, $address);
    if ($stmt->execute()) {
        header("Location: proveedores.php?msg=Proveedor agregado exitosamente");
        exit;
    } else {
        echo "Error al agregar el proveedor: " . $stmt->error;
    }
    $stmt->close();
}

// Consultar proveedores
$sql = "SELECT * FROM proveedores ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Sistema de Cotizaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
     <!-- Botón para regresar al dashboard -->
     <div class="mb-4">
        <a href="dashboard.php" class="btn btn-secondary">← Regresar al Dashboard</a>
     </div>


    <h1 class="mb-4">Registro de Proveedor</h1>

    <!-- Formulario para agregar proveedor -->
    <form action="proveedores.php" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Nombre del proveedor</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
            <label for="contact_name" class="form-label">Nombre del contacto</label>
            <input type="text" class="form-control" name="contact_name">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Teléfono</label>
            <input type="text" class="form-control" name="phone">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Dirección</label>
            <textarea class="form-control" name="address"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Agregar proveedor</button>
    </form>

    <!-- Tabla de proveedores -->
    <h2 class="mt-5">Lista de Proveedores</h2>
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['contact_name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td>
                    <a href="edit_supplier.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="proveedores.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este proveedor?');">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
