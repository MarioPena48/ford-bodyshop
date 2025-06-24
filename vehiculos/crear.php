<?php
require_once '../config.php';
require_once '../header.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación básica
    if (empty($_POST['cliente_id']) || empty($_POST['modelo']) || empty($_POST['placas'])) {
        $error = "Por favor, completa los campos obligatorios (Cliente, Modelo, Placas).";
    } else {
        $sql = "INSERT INTO vehiculos (cliente_id, marca, modelo, placas, vin, color, kilometraje) 
                VALUES (:cliente_id, :marca, :modelo, :placas, :vin, :color, :kilometraje)";
        
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':cliente_id' => $_POST['cliente_id'],
                ':marca' => $_POST['marca'] ?: 'FORD', // Valor por defecto
                ':modelo' => $_POST['modelo'],
                ':placas' => $_POST['placas'],
                ':vin' => $_POST['vin'] ?: null,
                ':color' => $_POST['color'] ?: null,
                ':kilometraje' => !empty($_POST['kilometraje']) ? (int)$_POST['kilometraje'] : null
            ]);
            
            header("Location: index.php?status=created");
            exit();

        } catch (PDOException $e) {
            $error = "Error al crear el vehículo: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h4">Añadir Nuevo Vehículo</h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="crear.php" method="POST" autocomplete="off">
                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative">
                                <label for="cliente_input" class="form-label">Cliente (*)</label>
                                <input type="text" class="form-control" id="cliente_input" placeholder="Nombre o apellido del cliente" autocomplete="off" required>
                                <input type="hidden" name="cliente_id" id="cliente_id">
                                <div id="cliente_sugerencias" class="list-group position-absolute w-100" style="z-index:10;"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca" value="FORD">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modelo" class="form-label">Modelo (*)</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="placas" class="form-label">Placas (*)</label>
                                <input type="text" class="form-control" id="placas" name="placas" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vin" class="form-label">VIN (Número de Serie)</label>
                            <input type="text" class="form-control" id="vin" name="vin">
                        </div>
                        
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kilometraje" class="form-label">Kilometraje</label>
                                <input type="number" class="form-control" id="kilometraje" name="kilometraje" step="1">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Vehículo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const inputCliente = document.getElementById('cliente_input');
const sugerencias = document.getElementById('cliente_sugerencias');
const inputClienteId = document.getElementById('cliente_id');
let timeout = null;

inputCliente.addEventListener('input', function() {
    clearTimeout(timeout);
    const q = this.value.trim();
    inputClienteId.value = '';
    sugerencias.innerHTML = '';
    if (q.length < 2) return;
    timeout = setTimeout(() => {
        fetch('../clientes/buscar_cliente.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                sugerencias.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    sugerencias.innerHTML = '<div class="list-group-item list-group-item-danger">No encontrado</div>';
                    return;
                }
                data.forEach(c => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = `${c.nombre} ${c.apellido}`;
                    item.onclick = () => {
                        inputCliente.value = `${c.nombre} ${c.apellido}`;
                        inputClienteId.value = c.cliente_id;
                        sugerencias.innerHTML = '';
                    };
                    sugerencias.appendChild(item);
                });
            });
    }, 250);
});
inputCliente.addEventListener('blur', () => setTimeout(()=>{sugerencias.innerHTML='';},200));
</script>

<?php require_once '../footer.php'; ?>