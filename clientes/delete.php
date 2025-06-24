<?php
require_once '../config.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM clientes WHERE cliente_id = ?');
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
