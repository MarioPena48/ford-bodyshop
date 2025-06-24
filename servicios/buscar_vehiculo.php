<?php
// servicios/buscar_vehiculo.php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode([]);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT v.vehiculo_id, v.marca, v.modelo, v.placas, c.nombre, c.apellido FROM vehiculos v JOIN clientes c ON v.cliente_id = c.cliente_id WHERE v.marca LIKE ? OR v.modelo LIKE ? OR v.placas LIKE ? OR c.nombre LIKE ? OR c.apellido LIKE ? ORDER BY v.marca, v.modelo LIMIT 10");
    $like = "%$q%";
    $stmt->execute([$like, $like, $like, $like, $like]);
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($vehiculos);
} catch (PDOException $e) {
    echo json_encode([]);
}
// Mostrar imagen si no hay resultados y petición directa desde navegador
if (php_sapi_name() !== 'cli' && empty($vehiculos) && empty($q) && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    echo '<img src="../assets/img/ford.jpg" alt="Ford" style="display:block;margin:2rem auto;max-width:320px;width:100%;">';
}
