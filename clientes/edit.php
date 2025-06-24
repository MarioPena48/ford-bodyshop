<?php
require_once '../header.php';
require_once '../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM clientes WHERE cliente_id = ?');
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cliente) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $codigo_postal = $_POST['codigo_postal'];

    $stmt = $pdo->prepare('UPDATE clientes SET nombre=?, apellido=?, telefono=?, email=?, direccion=?, ciudad=?, estado=?, codigo_postal=? WHERE cliente_id=?');
    $stmt->execute([$nombre, $apellido, $telefono, $email, $direccion, $ciudad, $estado, $codigo_postal, $id]);
    header('Location: index.php');
    exit;
}
?>
<h2>Editar Cliente</h2>
<form method="post">
    <div class="mb-3">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
    </div>
    <div class="mb-3">
        <label>Apellido</label>
        <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
    </div>
    <div class="mb-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono']) ?>">
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email']) ?>">
    </div>
    <div class="mb-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($cliente['direccion']) ?>">
    </div>
    <div class="mb-3">
        <label>Ciudad</label>
        <input type="text" name="ciudad" class="form-control" value="<?= htmlspecialchars($cliente['ciudad']) ?>">
    </div>
    <div class="mb-3">
        <label>Estado</label>
        <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($cliente['estado']) ?>">
    </div>
    <div class="mb-3">
        <label>Código Postal</label>
        <input type="text" name="codigo_postal" class="form-control" value="<?= htmlspecialchars($cliente['codigo_postal']) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>
<?php require_once '../footer.php'; ?>
