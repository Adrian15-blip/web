<?php
// client/new_quote.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';


// new_quote.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = sanitize_input($_POST['comment']);
    $quote_id = sanitize_input($_POST['quote_id']);
    $user_id = $_SESSION['user_id']; // Asegúrate de que el usuario está autenticado

    $stmt = $conn->prepare("INSERT INTO comments (quote_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $quote_id, $user_id, $comment);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comentario agregado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar el comentario.']);
    }
    exit;
}


// Verificar si el usuario ha iniciado sesión y es un cliente
if (!is_logged_in() || is_admin()) {
    redirect('../login.php');
}

// Obtener la lista de servicios disponibles
$stmt = $conn->prepare("SELECT id, name, price FROM services");
$stmt->execute();
$services = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = sanitize_input($_POST['service']);
    $workers = (int)sanitize_input($_POST['workers']);
    $days = (int)sanitize_input($_POST['days']);
    $transport_cost = (float)sanitize_input($_POST['transport_cost']);
    $materials = sanitize_input($_POST['materials']);
    $details = sanitize_input($_POST['details']);
    $company_name = sanitize_input($_POST['company_name']);
    $start_date = sanitize_input($_POST['start_date']);
    $end_date = sanitize_input($_POST['end_date']);
    $payment_terms = sanitize_input($_POST['payment_terms']);
    $user_id = $_SESSION['user_id'];

    // Obtén el precio del servicio desde la base de datos
    $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->bind_result($service_price);
    $stmt->fetch();
    $stmt->close();

    // Verifica que el precio sea válido
    if (!$service_price) {
        $error = "El servicio seleccionado no tiene un precio definido.";
        return;
    }

    // Calcula el costo total
    $total_cost = ($service_price + $workers * $days) + $transport_cost;

    // Inserta los datos en la base de datos
    $stmt = $conn->prepare("INSERT INTO quotes (user_id, service_id, workers, days, materials, details, status, company_name, transport_cost, start_date, end_date, payment_terms, total_cost) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssssdssd", $user_id, $service_id, $workers, $days, $materials, $details, $company_name, $transport_cost, $start_date, $end_date, $payment_terms, $total_cost);

    if ($stmt->execute()) {
        $quote_id = $conn->insert_id;
        $_SESSION['success'] = "Cotización creada exitosamente.";
        redirect("view_quote.php?id=$quote_id");
    } else {
        $error = "Error al crear la cotización. Por favor, intente de nuevo.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Cotización - Sistema de Cotizaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        .suggestions-container {
            background-color: #f8f9fa;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <div class="sidebar">
            <h4 class="text-center">Menú</h4>
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
                <a href="view_quote.php" class="nav-link"><i class="fas fa-file-alt"></i> Mis Cotizaciones</a>
                <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content">
            <header class="mb-4">
                <h1>Nueva Cotización</h1>
            </header>

            <?php if (isset($error)) : ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <!-- Formulario -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="service" class="form-label">Servicio:</label>
                            <select id="service" name="service" class="form-select" required>
                                <option value="">Seleccione un servicio</option>
                                <?php while ($service = $services->fetch_assoc()) : ?>
                                    <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['price']; ?>">
                                      <?php echo htmlspecialchars($service['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Nombre de la Empresa:</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="workers" class="form-label">Número de trabajadores:</label>
                            <input type="number" id="workers" name="workers" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="days" class="form-label">Días de trabajo:</label>
                            <input type="number" id="days" name="days" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="transport_cost" class="form-label">Costo de Transporte:</label>
                            <input type="number" id="transport_cost" name="transport_cost" class="form-control" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label for="materials" class="form-label">Materiales necesarios:</label>
                            <textarea id="materials" name="materials" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="form-label">Fecha de Inicio:</label>
                            <input type="date" id="start_date" name="start_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label">Fecha de Fin:</label>
                            <input type="date" id="end_date" name="end_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="payment_terms" class="form-label">Condiciones de Pago:</label>
                            <input type="text" id="payment_terms" name="payment_terms" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="details" class="form-label">Detalles adicionales:</label>
                            <textarea id="details" name="details" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                        <label for="total_cost" class="form-label">Costo Total:</label>
                        <input type="number" id="total_cost" name="total_cost" class="form-control" step="0.01" required readonly>
                        </div>

                        <button type="submit" class="btn btn-primary">Crear Cotización</button>
                    </form>
                </div>

                <!-- Sugerencias -->
                <div class="col-md-4">
                    <div id="suggestions" class="suggestions-container d-none">
                        <h5>Sugerencias</h5>
                        <p>Basado en proyectos similares, le sugerimos considerar lo siguiente:</p>
                        <ul id="suggestion-list" class="list-unstyled"></ul>
                    </div>
                </div>
            </div>
        </div>


   

        <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
           <div class="modal-dialog">
                <div class="modal-content">
                   <div class="modal-header">
                      <h5 class="modal-title" id="commentModalLabel">Agregar Comentario</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
             
                  <div class="modal-body">
                         <form id="commentForm" action="/ruta/donde/se/envia/el/comentario" method="POST">
                              <div class="mb-3">
                                  <label for="comment" class="form-label">Comentario:</label>
                                  <textarea id="comment" name="comment" class="form-control" rows="4" required></textarea>
                                  <input type="hidden" id="quote_id" name="quote_id" value="<?php echo $quote_id; ?>">
                               </div>
                               <button type="submit" class="btn btn-primary">Enviar</butto>
                         </form>
                     </div>
                  </div>
              </div>
          </div>

   <!-- Botón para abrir el modal -->
  <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#commentModal">
    Agregar Comentario
   </button>

    </div>

    <script>
        const form = document.querySelector('form');
        const suggestionsDiv = document.getElementById('suggestions');
        const suggestionList = document.getElementById('suggestion-list');
        

        form.addEventListener('input', async () => {
            const serviceId = document.getElementById('service').value;
            const workers = document.getElementById('workers').value;
            const days = document.getElementById('days').value;
            const transportCost = document.getElementById('transport_cost').value;
            const totalCost = document.getElementById('total_cost').value;
 

            if (serviceId && workers && days) {
                try {
                    const response = await fetch('../api/get_suggestions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ service_id: serviceId, workers, days, transport_cost: transportCost, total_cost: totalCost }),
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.suggestions.length > 0) {
                            suggestionList.innerHTML = data.suggestions.map(s => `<li>${s}</li>`).join('');
                            suggestionsDiv.classList.remove('d-none');
                        } else {
                            suggestionsDiv.classList.add('d-none');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

        });

        document.addEventListener('DOMContentLoaded', () => {
        const serviceSelect = document.getElementById('service');
        const workersInput = document.getElementById('workers');
        const daysInput = document.getElementById('days');
        const transportInput = document.getElementById('transport_cost');
        const totalCostInput = document.getElementById('total_cost');

        function updateTotalCost() {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const servicePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const workers = parseInt(workersInput.value) || 0;
        const days = parseInt(daysInput.value) || 0;
        const transportCost = parseFloat(transportInput.value) || 0;

        const totalCost = (servicePrice + workers * days) + transportCost;
        totalCostInput.value = totalCost.toFixed(2);
    }

    // Escucha cambios en los campos relevantes
    serviceSelect.addEventListener('change', updateTotalCost);
    workersInput.addEventListener('input', updateTotalCost);
    daysInput.addEventListener('input', updateTotalCost);
    transportInput.addEventListener('input', updateTotalCost);
});



       document.getElementById('commentForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const comment = document.getElementById('comment').value;
        const quoteId = document.getElementById('quote_id').value;

        try {
            const response = await fetch('new_quote.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ comment, quote_id: quoteId })
            });

            const data = await response.json();
            if (data.success) {
                alert(data.message);
                document.getElementById('comment').value = '';
                // Opcional: Cerrar modal después del envío
                const modal = bootstrap.Modal.getInstance(document.getElementById('commentModal'));
                modal.hide();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error al enviar el comentario:', error);
        }
    });


    

    
    </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
