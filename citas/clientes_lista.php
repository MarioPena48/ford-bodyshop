<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
try {
    $stmt = $pdo->query('SELECT cliente_id, nombre, apellido FROM clientes ORDER BY nombre, apellido');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>