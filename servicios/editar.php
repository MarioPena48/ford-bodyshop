<?php
// servicios/editar.php
require_once __DIR__ . '/../config.php'; // Incluye el archivo de configuración
require_once __DIR__ . '/../header.php'; // Incluye el encabezado

$servicio = null;
$vehiculos = [];
$servicio_id = $_GET['id'] ?? null;

if (!$servicio_id) {
    echo '<div class="alert alert-danger" role="alert">ID de servicio no proporcionado.</div>';
    exit;
}

// Obtener vehículos para el select
try {
    $stmt = $pdo->query("SELECT v.vehiculo_id, v.marca, v.modelo, v.placas, c.nombre, c.apellido FROM vehiculos v JOIN clientes c ON v.cliente_id = c.cliente_id ORDER BY v.marca, v.modelo");
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">Error al cargar vehículos: ' . $e->getMessage() . '</div>';
}

// Obtener los datos del servicio a editar
try {
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE servicio_id = ?");
    $stmt->execute([$servicio_id]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        echo '<div class="alert alert-danger" role="alert">Servicio no encontrado.</div>';
        exit;
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">Error al cargar el servicio: ' . $e->getMessage() . '</div>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo_id = $_POST['vehiculo_id'] ?? null;
    $tipo_servicio = $_POST['tipo_servicio'] ?? null;
    $descripcion = $_POST['descripcion_problema'] ?? null;
    $fecha_recepcion = $_POST['fecha_recepcion'] ?? null;
    $fecha_estimada = $_POST['fecha_estimada_entrega'] ?? null;
    $fecha_real_entrega = $_POST['fecha_real_entrega'] ?? null;
    $costo_estimado = $_POST['costo_estimado'] ?? null;
    $costo_final = $_POST['costo_final'] ?? null;
    $estado_servicio = $_POST['estado_servicio'] ?? null;
    $notas = $_POST['notas'] ?? null;

    // Normalizar fechas vacías a null
    $fecha_recepcion = ($fecha_recepcion === '' ? null : $fecha_recepcion);
    $fecha_estimada = ($fecha_estimada === '' ? null : $fecha_estimada);
    $fecha_real_entrega = ($fecha_real_entrega === '' ? null : $fecha_real_entrega);

    // Normalizar numéricos vacíos a null
    $costo_estimado = ($costo_estimado === '' ? null : $costo_estimado);
    $costo_final = ($costo_final === '' ? null : $costo_final);

    try {
        $stmt = $pdo->prepare("UPDATE servicios SET vehiculo_id = ?, tipo_servicio = ?, descripcion_problema = ?, fecha_recepcion = ?, fecha_estimada_entrega = ?, fecha_real_entrega = ?, costo_estimado = ?, costo_final = ?, estado_servicio = ?, notas = ? WHERE servicio_id = ?");
        $stmt->execute([$vehiculo_id, $tipo_servicio, $descripcion, $fecha_recepcion, $fecha_estimada, $fecha_real_entrega, $costo_estimado, $costo_final, $estado_servicio, $notas, $servicio_id]);
        echo '
        <div class="d-flex flex-column align-items-center mt-4">
            <div class="alert alert-success text-center" role="alert">
                Servicio actualizado correctamente. Redirigiendo...
                <div class="spinner-border text-success ms-2" role="status" style="vertical-align: middle;">
                  <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <script>setTimeout(function(){ window.location.href = "index.php"; }, 1500);</script>
        ';
        exit;
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger" role="alert">Error al actualizar el servicio: ' . $e->getMessage() . '</div>';
    }
}

// Normalizar fechas vacías a null para mostrar en el formulario
$fecha_recepcion_val = (!empty($servicio['fecha_recepcion'])) ? date('Y-m-d\TH:i', strtotime($servicio['fecha_recepcion'])) : '';
$fecha_estimada_val = (!empty($servicio['fecha_estimada_entrega'])) ? date('Y-m-d', strtotime($servicio['fecha_estimada_entrega'])) : '';
$fecha_real_entrega_val = (!empty($servicio['fecha_real_entrega'])) ? date('Y-m-d', strtotime($servicio['fecha_real_entrega'])) : '';
?>

<h1 class="mb-4 text-center">Editar Servicio</h1>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-primary mb-5">
      <div class="card-body">
        <form action="editar.php?id=<?php echo htmlspecialchars($servicio_id); ?>" method="POST">
            <div class="mb-3">
                <label for="vehiculo_id" class="form-label">Vehículo</label>
                <select class="form-select" id="vehiculo_id" name="vehiculo_id" required>
                    <option value="">Seleccione un vehículo</option>
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <option value="<?php echo htmlspecialchars($vehiculo['vehiculo_id']); ?>" <?php echo ($vehiculo['vehiculo_id'] == $servicio['vehiculo_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['placas'] . ' (' . $vehiculo['nombre'] . ' ' . $vehiculo['apellido'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tipo_servicio" class="form-label">Tipo de Servicio</label>
                <input type="text" class="form-control" id="tipo_servicio" name="tipo_servicio" value="<?php echo htmlspecialchars($servicio['tipo_servicio'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion_problema" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion_problema" name="descripcion_problema" rows="3"><?php echo htmlspecialchars($servicio['descripcion_problema'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="fecha_recepcion" class="form-label">Fecha de Recepción</label>
                <input type="datetime-local" class="form-control" id="fecha_recepcion" name="fecha_recepcion" value="<?php echo htmlspecialchars($fecha_recepcion_val); ?>">
            </div>
            <div class="mb-3">
                <label for="fecha_estimada_entrega" class="form-label">Fecha Estimada de Entrega</label>
                <input type="date" class="form-control" id="fecha_estimada_entrega" name="fecha_estimada_entrega" value="<?php echo htmlspecialchars($fecha_estimada_val); ?>">
            </div>
            <div class="mb-3">
                <label for="fecha_real_entrega" class="form-label">Fecha Real de Entrega</label>
                <input type="date" class="form-control" id="fecha_real_entrega" name="fecha_real_entrega" value="<?php echo htmlspecialchars($fecha_real_entrega_val); ?>">
            </div>
            <div class="mb-3">
                <label for="costo_estimado" class="form-label">Costo Estimado</label>
                <input type="number" step="0.01" class="form-control" id="costo_estimado" name="costo_estimado" value="<?php echo htmlspecialchars($servicio['costo_estimado'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="costo_final" class="form-label">Costo Final</label>
                <input type="number" step="0.01" class="form-control" id="costo_final" name="costo_final" value="<?php echo htmlspecialchars($servicio['costo_final'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="estado_servicio" class="form-label">Estado del Servicio</label>
                <select class="form-select" id="estado_servicio" name="estado_servicio" required>
                    <option value="Programado" <?php echo ($servicio['estado_servicio'] == 'Programado') ? 'selected' : ''; ?>>Programado</option>
                    <option value="En Proceso" <?php echo ($servicio['estado_servicio'] == 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                    <option value="Completado" <?php echo ($servicio['estado_servicio'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                    <option value="Cancelado" <?php echo ($servicio['estado_servicio'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notas" class="form-label">Notas Adicionales</label>
                <textarea class="form-control" id="notas" name="notas" rows="3"><?php echo htmlspecialchars($servicio['notas'] ?? ''); ?></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; // Incluye el pie de página ?>