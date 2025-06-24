<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../header.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    if (!$nombre_usuario || !$contrasena || !$rol) {
        $error = 'Usuario, contraseña y rol son obligatorios.';
    } else {
        try {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre_usuario, contrasena_hash, rol, nombre, apellido, email) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nombre_usuario, $hash, $rol, $nombre, $apellido, $email]);
            $success = 'Usuario creado correctamente.';
        } catch (PDOException $e) {
            $error = 'Error al crear usuario: ' . $e->getMessage();
        }
    }
}
?>
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 justify-content-center mb-4">
        <img src="/assets/img/ford.jpg" alt="Ford" style="height:80px;width:auto;border-radius:10px;box-shadow:0 1px 4px #0002;">
        <h1 class="fw-light mb-0 text-center" style="font-size:2rem; width:100%;">Crear Usuario</h1>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger text-center" style="border-radius:12px;"> <?php echo $error; ?> </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success text-center" style="border-radius:12px;"> <?php echo $success; ?> </div>
    <?php endif; ?>
    <form method="POST" class="card border-0 shadow-sm p-4 mx-auto" style="max-width:420px; background:#fafbfc; border-radius:18px;">
        <div class="mb-3">
            <label for="nombre_usuario" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
        </div>
        <div class="mb-3">
            <label for="contrasena" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
        </div>
        <div class="mb-3">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="">Selecciona un rol</option>
                <option value="admin">Administrador</option>
                <option value="tecnico">Técnico</option>
                <option value="recepcion">Recepción</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre">
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar Usuario</button>
        <a href="index.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
    </form>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
