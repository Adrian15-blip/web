<?php
// admin/manage_users.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario ha iniciado sesión y es un administrador
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Manejar la eliminación de usuarios
if (isset($_POST['delete_user'])) {
    $user_id = sanitize_input($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Usuario eliminado exitosamente.";
    } else {
        $_SESSION['error'] = "No se pudo eliminar el usuario.";
    }
    redirect('manage_users.php');
}

// Obtener la lista de usuarios
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Panel de Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
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

        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .content table thead {
            background-color: #343a40;
            color: #ffffff;
            text-transform: uppercase;
        }

        .content table th, .content table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .content table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .content table tbody tr:hover {
            background-color: #e9ecef;
        }

        .content .btn-delete {
            padding: 0.4rem 0.8rem;
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
        }

        .content .btn-delete:hover {
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
            <li><a href="../logout.php" class="nav-link px-3 text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>

        </ul>
    </div>
    <div class="content">
        <h1>Gestionar Usuarios</h1>
        <?php if (isset($_SESSION['success'])) : ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])) : ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha de Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
