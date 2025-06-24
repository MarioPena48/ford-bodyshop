<?php
// config.php
$host = '192.168.100.5'; // <--- ¡Usa la IP LOCAL de tu Raspberry Pi!
$db   = 'Ford_bodyshop';
$user = 'mario';
$pass = 'Mayito1689'; // Asegúrate de que esta sea la contraseña REAL de tu DB
$port = '5432'; // Asegúrate de que este puerto esté mapeado en tu docker-compose.yml (5432:5432)

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // Si la petición espera JSON, responde con JSON
    if (
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    ) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    } else {
        echo "<b>Error de conexión:</b> " . $e->getMessage();
    }
    die();
}
?>