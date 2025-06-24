<?php
require_once '../header.php';
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $codigo_postal = $_POST['codigo_postal'];

    $stmt = $pdo->prepare('INSERT INTO clientes (nombre, apellido, telefono, email, direccion, ciudad, estado, codigo_postal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$nombre, $apellido, $telefono, $email, $direccion, $ciudad, $estado, $codigo_postal]);
    header('Location: index.php');
    exit;
}
?>
<h2>Agregar Cliente</h2>
<form method="post">
    <div class="mb-3">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Apellido</label>
        <input type="text" name="apellido" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control">
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
    </div>
    <div class="mb-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control">
    </div>
    <div class="mb-3">
        <label>Ciudad</label>
        <input type="text" name="ciudad" class="form-control">
    </div>
    <div class="mb-3">
        <label>Estado</label>
        <input type="text" name="estado" class="form-control">
    </div>
    <div class="mb-3">
        <label>Código Postal</label>
        <input type="text" name="codigo_postal" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>
<?php require_once '../footer.php'; ?>
