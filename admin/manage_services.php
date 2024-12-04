<?php
// admin/manage_services.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario ha iniciado sesión y es un administrador
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Manejar la adición de nuevos servicios
if (isset($_POST['add_service'])) {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    
    $stmt = $conn->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Servicio agregado exitosamente.";
    } else {
        $_SESSION['error'] = "Error al agregar el servicio.";
    }
    redirect('manage_services.php');
}

// Actualizar el precio del servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = sanitize_input($_POST['service_id']);
    $price = sanitize_input($_POST['price']);

    $stmt = $conn->prepare("UPDATE services SET price = ? WHERE id = ?");
    $stmt->bind_param("di", $price, $service_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Precio del servicio actualizado correctamente.";
    } else {
        $_SESSION['error'] = "Error al actualizar el precio del servicio.";
    }
    redirect('manage_services.php');
}

// Manejar la eliminación de servicios
if (isset($_POST['delete_service'])) {
    $service_id = sanitize_input($_POST['service_id']);
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Servicio eliminado exitosamente.";
    } else {
        $_SESSION['error'] = "Error al eliminar el servicio.";
    }
    redirect('manage_services.php');
}

// Obtener la lista de servicios
$stmt = $conn->prepare("SELECT id, name, description, price FROM services");
$stmt->execute();
$services = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Servicios - Panel de Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        /* Barra lateral */
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            padding-top: 1rem;
            position: fixed;
            height: 100vh;
        }

        .sidebar h1 {
            text-align: center;
            color: #ffffff;
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 1rem 0;
        }

        .sidebar ul li a {
            color: #adb5bd;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #495057;
            color: #ffffff;
        }

        /* Contenido principal */
        .content {
            margin-left: 250px;
            padding: 2rem;
            flex: 1;
        }

        .content h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #343a40;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .form-group textarea {
            resize: none;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background-color: #343a40;
            color: #ffffff;
            text-transform: uppercase;
        }

        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        .btn-delete {
            padding: 0.4rem 0.8rem;
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>Panel de Admin</h1>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="manage_services.php"><i class="fas fa-cogs"></i> Gestionar Servicios</a></li>
            <li><a href="pending_quotes.php"><i class="fas fa-file-invoice"></i> Cotizaciones Pendientes</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Gestionar Servicios</h1>
        <?php if (isset($_SESSION['success'])) : ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])) : ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])) : ?>
         <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])) : ?>
          <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>


        <section>
            <h2>Agregar Nuevo Servicio</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nombre del Servicio:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                <button type="submit" name="add_service">Agregar Servicio</button>
            </form>
        </section>

        <section>
            <h2>Servicios Existentes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio (S/)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($service = $services->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $service['id']; ?></td>
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td><?php echo htmlspecialchars($service['description']); ?></td>
                            <td><?php echo number_format($service['price'], 2); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar este servicio?');">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <input type="number" name="price" value="<?php echo $service['price']; ?>" class="form-control" step="0.01" required>
                                    <button type="submit" name="delete_service" class="btn-delete">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
