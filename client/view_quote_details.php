<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php'; 

if (!is_logged_in()) {
    redirect('../login.php');
}

$quote_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener detalles de la cotización
$stmt = $conn->prepare("
    SELECT q.*, s.name as service_name, u.name as client_name, u.email as client_email 
    FROM quotes q 
    JOIN services s ON q.service_id = s.id 
    JOIN users u ON q.user_id = u.id  -- Corregido el JOIN aquí
    WHERE q.id = ?
");
$stmt->bind_param("i", $quote_id);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();

if (!$quote) {
    redirect('dashboard.php');
}

// Generar PDF
if (class_exists('TCPDF')) {
    ob_clean();

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    

    // Configuración del PDF
    $pdf->SetCreator('Sistema de Cotizaciones');
    $pdf->SetAuthor('Nombre de la Empresa');
    $pdf->SetTitle('Cotización N°' . $quote_id);
    
    // Agregar página
    $pdf->AddPage();
    
    // Agregar logo
    $pdf->Image('../assets/img/logo.jpeg', 15, 10, 50);
    
    // Contenido del PDF
    $html = generateQuoteHTML($quote);
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Generar el PDF
    $pdf->Output('Cotizacion_' . $quote_id . '.pdf', 'D');
    exit();
    

}

// Enviar a administrador
if (isset($_POST['send_to_admin'])) {
    $stmt = $conn->prepare("UPDATE quotes SET status = 'pendiente' WHERE id = ?");
    $stmt->bind_param("i", $quote_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Cotización enviada al administrador exitosamente.";
    }
    redirect('view_quote_details.php?id=' . $quote_id);
}

// Función para generar HTML de la cotización
// Función para generar HTML de la cotización
function generateQuoteHTML($quote) {
    // Formatear fecha de inicio y costo total
    $start_date = !empty($quote['start_date']) ? date('d/m/Y', strtotime($quote['start_date'])) : 'N/A';
    $end_date = !empty($quote['end_date']) ? date('d/m/Y', strtotime($quote['end_date'])) : 'N/A';
    $total_cost = number_format((float)$quote['total_cost'], 2, '.', ',');   

    return '
    <style>
        .quote-header { text-align: right; margin-bottom: 20px; }
        .quote-details { margin-top: 30px; }
        .quote-table { width: 100%; border-collapse: collapse; }
        .quote-table th, .quote-table td { border: 1px solid #ddd; padding: 8px; }
        .quote-table th { background-color: #6c757d; color: #fff; }
    </style>
    
    <div class="quote-header">
        <h1>Cotización #' . $quote['id'] . '</h1>
        <p>Fecha: ' . date('d/m/Y', strtotime($quote['created_at'])) . '</p>
    </div>
    
    <div class="quote-details">
        <table class="quote-table">
            <tr>
                <th>Cliente:</th>
                <td>' . htmlspecialchars($quote['client_name']) . '</td>
            </tr>
            <tr>
                <th>Servicio:</th>
                <td>' . htmlspecialchars($quote['service_name']) . '</td>
            </tr>
            
            <tr>
                <th>Nombre de la Empresa:</th>
                <td>' . htmlspecialchars($quote['company_name']) . '</td>
            </tr>
            <tr>
                <th>Trabajadores:</th>
                <td>' . $quote['workers'] . '</td>
            </tr>
            <tr>
                <th>Días de trabajo:</th>
                <td>' . $quote['days'] . '</td>
            </tr>
            <tr>
                <th>Costo de Transporte:</th>
                <td>' . number_format((float)$quote['transport_cost'], 2, '.', ',') . '</td>
            </tr>
            <tr>
                <th>Materiales:</th>
                <td>' . nl2br(htmlspecialchars($quote['materials'])) . '</td>
            </tr>
            <tr>
                <th>Fecha de Inicio:</th>
                <td>' . $start_date . '</td>
            </tr>
            <tr>
                <th>Fecha de Fin:</th>
                <td>' . $end_date . '</td>
            </tr>
            <tr>
                <th>Condiciones de Pago:</th>
                <td>' . nl2br(htmlspecialchars($quote['payment_terms'])) . '</td>
            </tr>
            <tr>
                <th>Detalles:</th>
                <td>' . nl2br(htmlspecialchars($quote['details'])) . '</td>
            </tr>
            <tr>
                <th>Costo Total:</th>
                <td>' . $total_cost . '</td>
            </tr>
        </table>
    </div>';
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Cotización #<?php echo $quote_id; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .quote-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .company-logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        
        .quote-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
        }
        
        .quote-details {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #007bff;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="quote-container">
        <?php if (isset($_SESSION['success'])) : ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="quote-header">
            <img src="../assets/img/logo.jpeg" alt="Logo de la empresa" class="company-logo">
            <div>
                <h1>Cotización #<?php echo $quote_id; ?></h1>
                <p>Fecha: <?php echo date('d/m/Y', strtotime($quote['created_at'])); ?></p>
            </div>
        </div>

        <div class="quote-details">
            <?php echo generateQuoteHTML($quote); ?>
        </div>

        <div class="action-buttons">
            <form method="POST" style="display: inline;">
                <button type="submit" name="generate_pdf" class="btn btn-primary">
                    Descargar PDF
                </button>
            </form>
            
            <form method="POST" style="display: inline;">
                <button type="submit" name="send_to_admin" class="btn btn-secondary">
                    Enviar al Administrador
                </button>
            </form>
        </div>
    </div>
</body>
</html>