<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'soporte'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? null;
    $asignado_a = $_POST['asignado_a'] ?? null;

    if (!$ticket_id || !$asignado_a) {
        die("Datos inválidos.");
    }

    $stmt = $pdo->prepare("UPDATE tickets SET asignado_a = ? WHERE id = ?");
    $stmt->execute([$asignado_a, $ticket_id]);

    header("Location: ../ver_ticket.php?id=$ticket_id");
    exit;
}
