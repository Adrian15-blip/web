<?php
// client/view_quotes.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario ha iniciado sesión y es un cliente
if (!is_logged_in() || is_admin()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Obtener todas las cotizaciones del cliente
$stmt = $conn->prepare("
    SELECT q.id, s.name as service_name, q.workers, q.days, q.status, q.created_at, q.company_name, q.transport_cost, q.start_date, q.end_date, q.payment_terms, q.total_cost
    FROM quotes q
    JOIN services s ON q.service_id = s.id
    WHERE q.user_id = ?
    ORDER BY q.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$quotes = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cotizaciones - Sistema de Cotizaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .container-fluid {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 8rem; /* Ajuste para que coincida con el dashboard */
            padding-left: 1rem;
            padding-right: 1rem;
            position: fixed;
            height: 100vh;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            margin: 0.5rem 0;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: #495057;
            border-radius: 4px;
            padding: 0.5rem;
        }

        .content {
            margin-left: 250px;
            padding: 2rem;
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        table th, table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        .status {
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            color: white;
            font-size: 0.875rem;
        }

        .status.pending {
            background-color: #ffc107;
        }

        .status.approved {
            background-color: #28a745;
        }

        .status.rejected {
            background-color: #dc3545;
        }

        .details-link {
            color: #007bff;
            text-decoration: none;
        }

        .details-link:hover {
            text-decoration: underline;
        }
        
        
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <div class="sidebar">
            <h4 class="text-center">Menú</h4>
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link "><i class="fas fa-home"></i> Inicio</a>
                <a href="new_quote.php" class="nav-link"><i class="fas fa-file-alt"></i> Nueva Cotización</a>
                <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content">
            <header>
                <h1>Mis Cotizaciones</h1>
            </header>

            <main>
                <?php if ($quotes->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Servicio</th>
                                <th>Trabajadores</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th>Fecha de Solicitud</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($quote = $quotes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $quote['id']; ?></td>
                                    <td><?php echo htmlspecialchars($quote['service_name']); ?></td>
                                    <td><?php echo $quote['workers']; ?></td>
                                    <td><?php echo $quote['days']; ?></td>
                                    <td>
                                        <?php
                                        switch ($quote['status']) {
                                            case 'pendiente':
                                                echo '<span class="status pending">Pendiente</span>';
                                                break;
                                            case 'aprobada':
                                                echo '<span class="status approved">Aprobada</span>';
                                                break;
                                            case 'rechazada':
                                                echo '<span class="status rejected">Rechazada</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($quote['created_at'])); ?></td>
                                    <td>
                                        <a href="view_quote_details.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-primary">Ver detalles</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tienes cotizaciones registradas aún. <a href="new_quote.php">Crea una nueva cotización</a>.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>
