<?php
// logout.php
session_start();
// Borrar todas las variables de sesión
$_SESSION = array();
// Destruir la sesión
session_destroy();
// Borrar cookies de usuario
setcookie('usuario_nombre', '', time() - 3600, '/');
setcookie('usuario_rol', '', time() - 3600, '/');
setcookie('PHPSESSID', '', time() - 3600, '/');
// Redirigir al login
header('Location: /login.php');
exit;
