<?php
// login.php
session_start();
require_once __DIR__ . '/config.php';
$error = '';

if (isset($_SESSION['usuario'])) {
    header('Location: servicios/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre_usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['contrasena_hash'])) {
        $_SESSION['usuario'] = $user['nombre_usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['usuario_id'] = $user['usuario_id'];
        setcookie('usuario_nombre', $user['nombre'], time() + 86400, '/');
        setcookie('usuario_rol', $user['rol'], time() + 86400, '/');
        setcookie('usuario_id', $user['usuario_id'], time() + 86400, '/');
        header('Location: servicios/index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ford Bodyshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f7f8fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 370px;
            margin: auto;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10);
            background: #fff;
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .login-logo {
            display: block;
            margin: 0 auto 1.5rem auto;
            height: 90px;
            width: auto;
            border-radius: 12px;
            box-shadow: 0 1px 4px #0002;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1565c0;
            text-align: center;
            margin-bottom: 1.2rem;
        }
        .form-control, .btn {
            border-radius: 14px;
        }
        .btn-primary {
            background: #1565c0;
            border: none;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #0d47a1;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="assets/img/ford.jpg" alt="Ford" class="login-logo">
        <div class="login-title">Ford Bodyshop</div>
        <?php if ($error): ?>
            <div class="alert alert-danger text-center py-2 mb-3" style="border-radius:12px; font-size:0.98rem;"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2">Ingresar</button>
        </form>
    </div>
</body>
</html>
