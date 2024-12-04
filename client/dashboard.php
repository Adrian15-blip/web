<?php
// client/dashboard.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario ha iniciado sesión y es un cliente
if (!is_logged_in() || is_admin()) {
    redirect('../login.php');
}

// Verifica que $user_id esté definido y contiene el ID del usuario actual
if (!isset($user_id)) {
    $user_id = $_SESSION['user_id']; // O ajusta la forma de obtener el ID del usuario si es necesario
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Obtener las cotizaciones del usuario
$stmt = $conn->prepare("SELECT id, status, created_at FROM quotes WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $quotes = $stmt->get_result();
} else {
    echo "Error en la preparación de la consulta: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        .sidebar {
            min-width: 250px;
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }
        .sidebar .user-info {
            padding: 1.5rem;
            text-align: center;
        }
        .sidebar .user-info .fa-user-circle {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .sidebar .nav-link {
            color: #adb5bd;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 2rem;
            flex: 1;
        }
        table {
            width: 100%;
        }
        

    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-info">
            <i class="fa fa-user-circle"></i>
            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link px-3"><i class="fas fa-home"></i> Inicio</a>
            <a href="new_quote.php" class="nav-link px-3"><i class="fas fa-file-alt"></i> Nueva Cotización</a>
            <a href="view_quote.php" class="nav-link px-3"><i class="fas fa-file-alt"></i> Mis Cotizaciones</a>
            <a href="../logout.php" class="nav-link px-3 text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content">
        <main>
            <section>
                <h2>Resumen</h2>
                <div class="card mb-4">
                    <div class="card-body">
                        <p class="mb-0">Correo electrónico: <?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </section>
            
            <section>
                <h2>Últimas Cotizaciones</h2>
                <?php if ($quotes->num_rows > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($quote = $quotes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $quote['id']; ?></td>
                                    <td><?php echo isset($quote['service']) ? htmlspecialchars($quote['service']) : 'Servicio no disponible'; ?></td>
                                    <td><?php echo htmlspecialchars($quote['status']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($quote['created_at'])); ?></td>
                                    <td>
                                        <a href="view_quote.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-primary">Ver detalles</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tienes cotizaciones recientes.</p>
                <?php endif; ?>
                <a href="view_quote.php" class="btn btn-success">Ver todas las cotizaciones</a>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
