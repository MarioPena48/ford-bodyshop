<?php
require_once '../config.php';
require_once '../header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

// Obtener datos del vehículo a editar
$stmt_vehiculo = $pdo->prepare('SELECT * FROM vehiculos WHERE vehiculo_id = ?');
$stmt_vehiculo->execute([$id]);
$vehiculo = $stmt_vehiculo->fetch(PDO::FETCH_ASSOC);

if (!$vehiculo) {
    echo "<div class='alert alert-danger'>Vehículo no encontrado.</div>";
    require_once '../footer.php';
    exit();
}

// Obtener la lista de clientes
$stmt_clientes = $pdo->query('SELECT cliente_id, nombre, apellido FROM clientes ORDER BY nombre');
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación similar a la de crear.php
    if (empty($_POST['cliente_id']) || empty($_POST['modelo']) || empty($_POST['placas'])) {
        $error = "Los campos Cliente, Modelo y Placas son obligatorios.";
    } else {
        $sql = "UPDATE vehiculos SET 
                    cliente_id = :cliente_id, 
                    marca = :marca, 
                    modelo = :modelo, 
                    placas = :placas, 
                    vin = :vin, 
                    color = :color, 
                    kilometraje = :kilometraje
                WHERE vehiculo_id = :id";
        
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':id' => $id,
                ':cliente_id' => $_POST['cliente_id'],
                ':marca' => $_POST['marca'] ?: 'FORD',
                ':modelo' => $_POST['modelo'],
                ':placas' => $_POST['placas'],
                ':vin' => $_POST['vin'] ?: null,
                ':color' => $_POST['color'] ?: null,
                ':kilometraje' => !empty($_POST['kilometraje']) ? (int)$_POST['kilometraje'] : null
            ]);
            
            header("Location: index.php?status=updated");
            exit();

        } catch (PDOException $e) {
            $error = "Error al actualizar el vehículo: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h4">Editar Vehículo #<?php echo htmlspecialchars($vehiculo['vehiculo_id']); ?></h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                         <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cliente_id" class="form-label">Cliente (*)</label>
                                <select class="form-select" id="cliente_id" name="cliente_id" required>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['cliente_id']; ?>" <?php echo ($cliente['cliente_id'] == $vehiculo['cliente_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca" value="<?php echo htmlspecialchars($vehiculo['marca']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modelo" class="form-label">Modelo (*)</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo htmlspecialchars($vehiculo['modelo']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="placas" class="form-label">Placas (*)</label>
                                <input type="text" class="form-control" id="placas" name="placas" value="<?php echo htmlspecialchars($vehiculo['placas']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vin" class="form-label">VIN (Número de Serie)</label>
                            <input type="text" class="form-control" id="vin" name="vin" value="<?php echo htmlspecialchars($vehiculo['vin']); ?>">
                        </div>
                        
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($vehiculo['color']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kilometraje" class="form-label">Kilometraje</label>
                                <input type="number" class="form-control" id="kilometraje" name="kilometraje" value="<?php echo htmlspecialchars($vehiculo['kilometraje']); ?>" step="1">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Vehículo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>