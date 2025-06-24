<?php
require_once '../config.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}
$stmt = $pdo->prepare('SELECT cliente_id, nombre, apellido FROM clientes WHERE nombre ILIKE :q OR apellido ILIKE :q ORDER BY nombre LIMIT 10');
$stmt->execute([':q' => "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
