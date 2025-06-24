<?php // header.php ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ford Bodyshop Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/clientes/index.php">
      <img src="/assets/img/ford.jpg" alt="Ford" style="height:36px;width:auto;border-radius:8px;box-shadow:0 1px 4px #0002;"> Ford Bodyshop
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/clientes/index.php">Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="/vehiculos/index.php">Vehículos</a></li>
        <li class="nav-item"><a class="nav-link" href="/citas/index.php">Citas</a></li>
        <li class="nav-item"><a class="nav-link" href="/servicios/index.php">Servicios</a></li>
        <li class="nav-item"><a class="nav-link" href="/usuarios/index.php">Usuarios</a></li>
      </ul>
      <?php
      if (session_status() === PHP_SESSION_NONE) session_start();
      $nombre = $_SESSION['nombre'] ?? ($_COOKIE['usuario_nombre'] ?? null);
      $rol = $_SESSION['rol'] ?? ($_COOKIE['usuario_rol'] ?? null);
      $usuario_id = $_SESSION['usuario_id'] ?? ($_COOKIE['usuario_id'] ?? null);
      $usuario = $_SESSION['usuario'] ?? null;
      if ($usuario && $nombre && $rol && $usuario_id): ?>
      <div class="dropdown ms-3">
        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 px-3 py-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 16px;">
          <i class="fas fa-user-circle fa-lg text-primary"></i>
          <span class="fw-semibold text-primary">Hola, <?php echo htmlspecialchars($nombre); ?></span>
          <span class="badge bg-primary ms-2" style="font-size:0.85em;"><?php echo htmlspecialchars($rol); ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
          <li><a class="dropdown-item" href="/usuarios/editar.php?id=<?php echo urlencode($usuario_id); ?>"><i class="fas fa-user-edit me-2"></i>Editar datos</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Salir</a></li>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<style>
.navbar {
    box-shadow: 0 2px 8px 0 rgba(0,0,0,0.07);
    border-radius: 0 0 18px 18px;
}
.navbar-brand {
    font-weight: 700;
    letter-spacing: 1px;
    font-size: 1.35rem;
}
.navbar-nav .nav-link {
    color: #fff !important;
    font-weight: 500;
    margin-right: 0.5rem;
    border-radius: 12px;
    transition: background 0.2s, color 0.2s;
    padding: 0.45rem 1.1rem;
}
.navbar-nav .nav-link.active, .navbar-nav .nav-link:focus, .navbar-nav .nav-link:hover {
    background: #1565c0;
    color: #fff !important;
}
@media (max-width: 991px) {
    .navbar-nav .nav-link {
        margin-bottom: 0.5rem;
    }
}
</style>
<div class="container">
