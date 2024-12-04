<?php
// client/new_quote.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario ha iniciado sesión y es un cliente
if (!is_logged_in() || is_admin()) {
    redirect('../login.php');
}

// Obtener la lista de servicios disponibles
$stmt = $conn->prepare("SELECT id, name FROM services");
$stmt->execute();
$services = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = sanitize_input($_POST['service']);
    $workers = sanitize_input($_POST['workers']);
    $days = sanitize_input($_POST['days']);
    $materials = sanitize_input($_POST['materials']);
    $details = sanitize_input($_POST['details']);
    $user_id = $_SESSION['user_id'];

    // Insertar la nueva cotización en la base de datos
    $stmt = $conn->prepare("INSERT INTO quotes (user_id, service_id, workers, days, materials, details, status) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("iiisss", $user_id, $service_id, $workers, $days, $materials, $details);

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
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .form-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .suggestions-container {
            flex-basis: 30%;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: none; /* Se muestra al tener sugerencias */
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Nueva Cotización</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Inicio</a></li>
                    <li><a href="view_quote.php">Mis Cotizaciones</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <?php if (isset($error)) : ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <!-- Campos del formulario -->
                    <div class="form-group">
                        <label for="service">Servicio:</label>
                        <select id="service" name="service" required>
                            <option value="">Seleccione un servicio</option>
                            <?php while ($service = $services->fetch_assoc()) : ?>
                                <option value="<?php echo $service['id']; ?>"><?php echo htmlspecialchars($service['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="workers">Número de trabajadores:</label>
                        <input type="number" id="workers" name="workers" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="days">Días de trabajo:</label>
                        <input type="number" id="days" name="days" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="materials">Materiales necesarios:</label>
                        <textarea id="materials" name="materials" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="details">Detalles adicionales:</label>
                        <textarea id="details" name="details" rows="4"></textarea>
                    </div>

                    <button type="submit">Crear Cotización</button>
                </form>

                <!-- Cuadro de Sugerencias -->
                <div id="suggestions" class="suggestions-container">
                    <h2>Sugerencias</h2>
                    <p>Basado en proyectos similares, le sugerimos considerar lo siguiente:</p>
                    <ul id="suggestion-list"></ul>
                </div>
            </div>
        </main>
    </div>

    <script>
        const form = document.querySelector('form');
        const suggestionsDiv = document.getElementById('suggestions');
        const suggestionList = document.getElementById('suggestion-list');

        form.addEventListener('input', async () => {
            const serviceId = document.getElementById('service').value;
            const workers = document.getElementById('workers').value;
            const days = document.getElementById('days').value;

            if (serviceId && workers && days) {
                try {
                    const response = await fetch('../api/get_suggestions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ service_id: serviceId, workers, days }),
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.suggestions.length > 0) {
                            suggestionList.innerHTML = data.suggestions.map(s => `<li>${s}</li>`).join('');
                            suggestionsDiv.style.display = 'block';
                        } else {
                            suggestionsDiv.style.display = 'none';
                        }
                    } else {
                        console.error('Error al obtener sugerencias');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        });
    </script>
</body>
</html>

