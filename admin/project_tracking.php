<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quote_id = $_POST['quote_id'];
    $progress_percentage = $_POST['progress_percentage'];
    $notes = $_POST['notes'];

    // Validaciones básicas
    if (!isset($quote_id, $progress_percentage, $notes)) {
        die("Faltan datos requeridos");
    }
    if (!is_numeric($progress_percentage) || $progress_percentage < 0 || $progress_percentage > 100) {
        die("El porcentaje de progreso no es válido");
    }

    // Preparar la consulta SQL
    $stmt = $conn->prepare("
        INSERT INTO project_tracking (quote_id, progress_percentage, notes)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        progress_percentage = VALUES(progress_percentage),
        notes = VALUES(notes),
        updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->bind_param('iis', $quote_id, $progress_percentage, $notes);

    if ($stmt->execute()) {
        $message = "Seguimiento actualizado con éxito.";
    } else {
        $message = "Error al actualizar el seguimiento: " . $stmt->error;
    }
    $stmt->close();
}


$sql = "
    SELECT q.id AS quote_id, 
           s.name AS service_name, 
           pt.progress_percentage, 
           pt.notes
    FROM quotes q
    LEFT JOIN services s ON q.service_id = s.id
    LEFT JOIN project_tracking pt ON q.id = pt.quote_id
    ORDER BY q.id DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Proyectos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    
    <!-- Botón para regresar al dashboard -->
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-secondary">← Regresar al Dashboard</a>
    </div>



    <h1 class="mb-4">Seguimiento de Proyectos</h1>

    <?php if (isset($message)) { ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php } ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID Cotización</th>
                <th>Servicio</th>
                <th>Progreso (%)</th>
                <th>Notas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['quote_id']; ?></td>
                <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                <td><?php echo $row['progress_percentage'] ?? 0; ?></td>
                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#editTrackingModal" 
                            data-quote-id="<?php echo $row['quote_id']; ?>" 
                            data-progress="<?php echo $row['progress_percentage'] ?? 0; ?>" 
                            data-notes="<?php echo htmlspecialchars($row['notes']); ?>">
                        Editar
                    </button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para editar seguimiento -->
<div class="modal fade" id="editTrackingModal" tabindex="-1" aria-labelledby="editTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTrackingModalLabel">Editar Seguimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="quote_id" id="modal-quote-id">
                    <div class="mb-3">
                        <label for="modal-progress" class="form-label">Progreso (%)</label>
                        <input type="number" class="form-control" name="progress_percentage" id="modal-progress" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal-notes" class="form-label">Notas</label>
                        <textarea class="form-control" name="notes" id="modal-notes" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Rellenar el modal con los datos seleccionados
    var editModal = document.getElementById('editTrackingModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;

        // Recuperar datos del botón
        var quoteId = button.getAttribute('data-quote-id');
        var progress = button.getAttribute('data-progress');
        var notes = button.getAttribute('data-notes');

        // Asignar valores a los campos del modal
        document.getElementById('modal-quote-id').value = quoteId;
        document.getElementById('modal-progress').value = progress;
        document.getElementById('modal-notes').value = notes;
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
