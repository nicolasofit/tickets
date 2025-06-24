<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'soporte'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? null;
    $nuevo_estado = $_POST['nuevo_estado'] ?? null;

    if (!$ticket_id || !in_array($nuevo_estado, ['abierto', 'en_proceso', 'cerrado'])) {
        die("Datos inválidos.");
    }

    $stmt = $pdo->prepare("UPDATE tickets SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?");
    $stmt->execute([$nuevo_estado, $ticket_id]);

    header("Location: ../ver_ticket.php?id=$ticket_id");
    exit;
}
?>
