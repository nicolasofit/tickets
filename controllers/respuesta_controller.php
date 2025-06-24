<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? null;
    $autor_id = $_SESSION['usuario_id'];
    $mensaje = trim($_POST['mensaje']);
    $accion = $_POST['accion'] ?? 'publica';
    $es_interna = ($accion === 'interna') ? 1 : 0;

    if (!$ticket_id || $mensaje === '') {
        header("Location: ../ver_ticket.php?id=$ticket_id&error=mensaje_vacio");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO respuestas (ticket_id, autor_id, mensaje, es_interna) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ticket_id, $autor_id, $mensaje, $es_interna]);
    $respuesta_id = $pdo->lastInsertId();

    if (!empty($_FILES['adjuntos']['name'][0])) {
        $uploadDir = '../uploads/';
        foreach ($_FILES['adjuntos']['tmp_name'] as $key => $tmp_name) {
            $filename = time() . "_" . basename($_FILES['adjuntos']['name'][$key]);
            $path = $uploadDir . $filename;
            if (move_uploaded_file($tmp_name, $path)) {
                $stmt = $pdo->prepare("INSERT INTO respuesta_adjuntos (respuesta_id, archivo) VALUES (?, ?)");
                $stmt->execute([$respuesta_id, 'uploads/' . $filename]);
            }
        }
    }

    header("Location: ../ver_ticket.php?id=$ticket_id");
    exit;
}
?>
