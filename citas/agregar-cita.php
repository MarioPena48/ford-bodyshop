<?php
// agregar-cita.php: endpoint AJAX para crear, editar y eliminar citas
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['action'])) {
            echo json_encode(['success' => false, 'error' => 'Falta el campo action']);
            exit;
        }
        if ($data['action'] === 'create') {
            if (empty($data['cliente_id']) || empty($data['fecha_hora_cita'])) {
                echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
                exit;
            }
            $stmt = $pdo->prepare('INSERT INTO citas (cliente_id, fecha_hora_cita, notas) VALUES (?, ?, ?)');
            $stmt->execute([
                $data['cliente_id'],
                $data['fecha_hora_cita'],
                $data['notas'] ?? ''
            ]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            exit;
        } elseif ($data['action'] === 'update') {
            if (empty($data['cliente_id']) || empty($data['fecha_hora_cita']) || empty($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
                exit;
            }
            $stmt = $pdo->prepare('UPDATE citas SET cliente_id=?, fecha_hora_cita=?, notas=? WHERE cita_id=?');
            $stmt->execute([
                $data['cliente_id'],
                $data['fecha_hora_cita'],
                $data['notas'] ?? '',
                $data['id']
            ]);
            echo json_encode(['success' => true]);
            exit;
        } elseif ($data['action'] === 'delete') {
            if (empty($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'Falta el id']);
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM citas WHERE cita_id=?');
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        exit;
    }
    echo json_encode(['error' => 'Método no permitido']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
