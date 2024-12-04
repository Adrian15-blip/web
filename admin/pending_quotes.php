<?php
// admin/pending_quotes.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Verificar si se ha enviado la aprobación o rechazo
if (isset($_POST['quote_id']) && isset($_POST['action'])) {
    $quote_id = (int)$_POST['quote_id'];
    $action = $_POST['action'];

    // Actualizar estado de la cotización
    $new_status = ($action === 'approve') ? 'aprobada' : 'rechazada';
    $stmt = $conn->prepare("UPDATE quotes SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $quote_id);
    $stmt->execute();
}

// Obtener cotizaciones pendientes
$stmt = $conn->prepare("
    SELECT q.*, s.name as service_name, u.name as client_name, u.phone as client_phone 
    FROM quotes q 
    JOIN services s ON q.service_id = s.id 
    JOIN users u ON q.user_id = u.id 
    WHERE q.status = 'pendiente'
    ORDER BY q.created_at DESC
");
$stmt->execute();
$quotes = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizaciones Pendientes - Panel de Admin</title>
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

        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }

        h1 {
            font-size: 2rem;
            color: #343a40;
            margin-bottom: 1rem;
            text-align: center;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .quote-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .quote-card:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .quote-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .quote-header h2 {
            font-size: 1.25rem;
            color: #495057;
        }

        .quote-header span {
            font-size: 0.9rem;
            color: #868e96;
        }

        .quote-content p {
            margin: 0 0 10px;
            font-size: 0.95rem;
            color: #495057;
        }

        .quote-content p strong {
            color: #343a40;
        }

        .quote-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-whatsapp {
            background: #25d366;
            color: white;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-whatsapp:hover {
            background: #128c7e;
        }

        .no-quotes {
            text-align: center;
            font-size: 1rem;
            color: #868e96;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Barra Lateral -->
    <div class="sidebar">
        <h1>Panel de Admin</h1>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="manage_services.php"><i class="fas fa-cogs"></i> Gestionar Servicios</a></li>
            <li><a href="pending_quotes.php"><i class="fas fa-file-invoice"></i> Cotizaciones Pendientes</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="content">
        <a href="dashboard.php" class="btn-back">Regresar al Dashboard</a>

        <h1>Cotizaciones Pendientes</h1>
        
        <?php if ($quotes->num_rows > 0) : ?>
            <?php while ($quote = $quotes->fetch_assoc()) : ?>
                <div class="quote-card">
                    <div class="quote-header">
                        <h2>Cotización #<?php echo $quote['id']; ?></h2>
                        <span>Fecha: <?php echo date('d/m/Y', strtotime($quote['created_at'])); ?></span>
                    </div>
                    
                    <div class="quote-content">
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($quote['client_name']); ?></p>
                        <p><strong>Servicio:</strong> <?php echo htmlspecialchars($quote['service_name']); ?></p>
                        <p><strong>Trabajadores:</strong> <?php echo $quote['workers']; ?></p>
                        <p><strong>Días:</strong> <?php echo $quote['days']; ?></p>
                    </div>
                    
                    <div class="quote-actions">
                        <form method="POST">
                            <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success">Aprobar</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">Rechazar</button>
                        </form>
                        
                        <button onclick="verDetalles(<?php echo $quote['id']; ?>)" class="btn btn-primary">Ver Detalles</button>
                        
                        <a href="https://wa.me/<?php echo $quote['client_phone']; ?>?text=<?php 
                            echo urlencode("Hola " . $quote['client_name'] . ", respecto a su cotización #" . $quote['id'] . "..."); 
                        ?>" target="_blank" class="btn-whatsapp">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.917 1.045 5.587 2.775 7.683L.831 23.316l3.829-1.228C6.462 23.283 8.291 24 10.5 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.068 0-4.018-.537-5.714-1.479l-3.143 1.01 1.03-3.072C2.79 16.705 2 14.44 2 12 2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                            Enviar por WhatsApp
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <p class="no-quotes">No hay cotizaciones pendientes.</p>
        <?php endif; ?>
    </div>

    <script>
        function verDetalles(id) {
            window.location.href = `../client/view_quote_details.php?id=${id}`;
        }
    </script>
</body>
</html>

