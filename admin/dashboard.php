<?php
// admin/dashboard.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verifica si el usuario ha iniciado sesión y es un administrador
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Obtener estadísticas
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'client'");
$stmt->execute();
$result = $stmt->get_result();
$total_users = $result->fetch_assoc()['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_quotes FROM quotes");
$stmt->execute();
$result = $stmt->get_result();
$total_quotes = $result->fetch_assoc()['total_quotes'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending_quotes FROM quotes WHERE status = 'pendiente'");
$stmt->execute();
$result = $stmt->get_result();
$pending_quotes = $result->fetch_assoc()['pending_quotes'];

// Obtener datos para un gráfico (por ejemplo, cotizaciones por estado)
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM quotes GROUP BY status");
$stmt->execute();
$quotes_by_status = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Convertir datos para JavaScript
$status_labels = [];
$status_counts = [];
foreach ($quotes_by_status as $row) {
    $status_labels[] = $row['status'];
    $status_counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Sistema de Cotizaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .sidebar .admin-info {
            padding: 1.5rem;
            text-align: center;
        }
        .sidebar .admin-info .fa-user-circle {
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
        .stat-container {
            display: flex;
            gap: 1rem;
        }
        .stat-box {
            flex: 1;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="admin-info">
            <i class="fa fa-user-circle"></i>
            <h5>Administrador</h5>
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link px-3"><i class="fas fa-home"></i> Inicio</a>
            <a href="manage_users.php" class="nav-link px-3"><i class="fas fa-cogs"></i> Gestionar Usuarios</a>
            <a href="manage_services.php" class="nav-link px-3"><i class="fas fa-cogs"></i> Gestionar Servicios</a>
            <a href="pending_quotes.php" class="nav-link px-3"><i class="fas fa-file-invoice"></i>  Cotizaciones Pendientes</a>
            <a href="logs.php" class="nav-link px-3"><i class="fas fa-cogs"></i> Logs</a>
            <a href="proveedores.php" class="nav-link px-3"><i class="fas fa-cogs"></i> Proveedores</a>
            <a href="project_tracking.php" class="nav-link px-3"><i class="fas fa-file-invoice"></i>  Seguimiento de proyecto</a>
            <a href="../logout.php" class="nav-link px-3 text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content">
        <main>
            <section class="dashboard-stats mb-4">
                <h2>Dashboard</h2>
                <div class="stat-container">
                    <div class="stat-box">
                        <h3>Total de Usuarios</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Total de Cotizaciones</h3>
                        <p><?php echo $total_quotes; ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Cotizaciones Pendientes</h3>
                        <p><?php echo $pending_quotes; ?></p>
                    </div>
                </div>
            </section>

            <section class="charts mb-4">
                <h2>Gráfico de Cotizaciones por Estado</h2>
                <div class="chart-container" style="width: 50%; margin: 20px auto;">
                    <canvas id="quotesStatusChart"></canvas>
                </div>
            </section>

            <section class="recent-activity">
                <h2>Actividad Reciente</h2>
                <p>Funcionalidad de actividad reciente pendiente de implementar.</p>
            </section>
        </main>
    </div>

    <script>
        // Datos para el gráfico
        const statusLabels = <?php echo json_encode($status_labels); ?>;
        const statusCounts = <?php echo json_encode($status_counts); ?>;

        // Configuración del gráfico
        const ctx = document.getElementById('quotesStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Cotizaciones por Estado',
                    data: statusCounts,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
