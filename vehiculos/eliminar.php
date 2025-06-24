<?php
require_once '../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit();
}

// Antes de eliminar, podrías verificar si el vehículo tiene citas o servicios asociados
// para evitar errores de integridad referencial. Por simplicidad, aquí lo eliminamos directamente.
// EJEMPLO: 
// $stmt_check = $pdo->prepare('SELECT COUNT(*) FROM citas WHERE vehiculo_id = ?');
// $stmt_check->execute([$id]);
// if ($stmt_check->fetchColumn() > 0) {
//     header('Location: index.php?error=has_relations');
//     exit();
// }

try {
    $stmt = $pdo->prepare('DELETE FROM vehiculos WHERE vehiculo_id = ?');
    $stmt->execute([$id]);
    
    header('Location: index.php?status=deleted');
    exit();

} catch (PDOException $e) {
    // Si hay una restricción de clave foránea, la base de datos lanzará un error.
    // Lo ideal es manejar este error de forma elegante.
    header('Location: index.php?status=error&message=' . urlencode($e->getMessage()));
    exit();
}
?>