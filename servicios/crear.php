<?php
// servicios/crear.php
require_once __DIR__ . '/../config.php'; // Incluye el archivo de configuración
require_once __DIR__ . '/../header.php'; // Incluye el encabezado

$redirect = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo_id = $_POST['vehiculo_id'] ?? null;
    $tipo_servicio = $_POST['tipo_servicio'] ?? null;
    $descripcion = $_POST['descripcion_problema'] ?? null;
    $fecha_estimada = $_POST['fecha_estimada_entrega'] ?? null;
    $costo_estimado = isset($_POST['costo_estimado']) && $_POST['costo_estimado'] !== '' ? $_POST['costo_estimado'] : 0;
    $estado_servicio = $_POST['estado_servicio'] ?? 'Programado'; // Valor por defecto
    $notas = $_POST['notas'] ?? null;
    $creado_por = $_SESSION['usuario_id'] ?? ($_COOKIE['usuario_id'] ?? null);

    try {
        $stmt = $pdo->prepare("INSERT INTO servicios (vehiculo_id, tipo_servicio, descripcion_problema, fecha_estimada_entrega, costo_estimado, estado_servicio, notas, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$vehiculo_id, $tipo_servicio, $descripcion, $fecha_estimada, $costo_estimado, $estado_servicio, $notas, $creado_por]);
        $redirect = true;
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger" role="alert">Error al agregar el servicio: ' . $e->getMessage() . '</div>';
    }
}
?>

<h1 class="mb-4">Agregar Nuevo Servicio</h1>

<form action="crear.php" method="POST" autocomplete="off" id="formServicio">
    <div class="mb-3 position-relative">
        <label for="vehiculo_input" class="form-label">Vehículo</label>
        <input type="text" class="form-control" id="vehiculo_input" placeholder="Marca, modelo, placas o cliente" autocomplete="off" required>
        <input type="hidden" name="vehiculo_id" id="vehiculo_id">
        <div id="vehiculo_sugerencias" class="list-group position-absolute w-100" style="z-index:10;"></div>
    </div>
    <div class="mb-3">
        <label for="tipo_servicio" class="form-label">Tipo de Servicio</label>
        <input type="text" class="form-control" id="tipo_servicio" name="tipo_servicio" required>
    </div>
    <div class="mb-3">
        <label for="descripcion_problema" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion_problema" name="descripcion_problema" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label for="fecha_estimada_entrega" class="form-label">Fecha Estimada de Entrega</label>
        <input type="date" class="form-control" id="fecha_estimada_entrega" name="fecha_estimada_entrega">
    </div>
    <div class="mb-3">
        <label for="costo_estimado" class="form-label">Costo Estimado</label>
        <input type="number" step="0.01" class="form-control" id="costo_estimado" name="costo_estimado">
    </div>
    <div class="mb-3">
        <label for="estado_servicio" class="form-label">Estado del Servicio</label>
        <select class="form-select" id="estado_servicio" name="estado_servicio" required>
            <option value="Programado">Programado</option>
            <option value="En Proceso">En Proceso</option>
            <option value="Completado">Completado</option>
            <option value="Cancelado">Cancelado</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="notas" class="form-label">Notas Adicionales</label>
        <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Guardar Servicio</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<script>
const inputVehiculo = document.getElementById('vehiculo_input');
const sugerencias = document.getElementById('vehiculo_sugerencias');
const inputVehiculoId = document.getElementById('vehiculo_id');
let timeout = null;

inputVehiculo.addEventListener('input', function() {
    clearTimeout(timeout);
    const q = this.value.trim();
    inputVehiculoId.value = '';
    sugerencias.innerHTML = '';
    if (q.length < 2) return;
    timeout = setTimeout(() => {
        fetch('buscar_vehiculo.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                sugerencias.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    sugerencias.innerHTML = '<div class="list-group-item list-group-item-danger">No encontrado</div>';
                    return;
                }
                data.forEach(v => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = `${v.marca} ${v.modelo} - ${v.placas} (${v.nombre} ${v.apellido})`;
                    item.onclick = () => {
                        inputVehiculo.value = `${v.marca} ${v.modelo} - ${v.placas} (${v.nombre} ${v.apellido})`;
                        inputVehiculoId.value = v.vehiculo_id;
                        sugerencias.innerHTML = '';
                    };
                    sugerencias.appendChild(item);
                });
            });
    }, 250);
});
inputVehiculo.addEventListener('blur', () => setTimeout(()=>{sugerencias.innerHTML='';},200));

<?php if ($redirect): ?>
setTimeout(function() {
    if (confirm('Servicio agregado correctamente. ¿Deseas volver al listado?')) {
        window.location.href = 'index.php';
    }
}, 200);
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../footer.php'; // Incluye el pie de página ?>