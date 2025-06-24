<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? null;
    $autor_id = $_SESSION['usuario_id'];
    $mensaje = trim($_POST['mensaje']);

    if (!$ticket_id || $mensaje === '') {
        header("Location: ../ver_ticket.php?id=$ticket_id&error=mensaje_vacio");
        exit;
    }

    // Insertar respuesta
    $stmt = $pdo->prepare("INSERT INTO respuestas (ticket_id, autor_id, mensaje) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_id, $autor_id, $mensaje]);
    $respuesta_id = $pdo->lastInsertId();

    // Adjuntos
    if (!empty($_FILES['adjuntos']['name'][0])) {
        $uploadDir = '../uploads/'; // ubicación física en el servidor
        $webBase = 'tickets/uploads/'; // ruta web correcta (forzada)

        foreach ($_FILES['adjuntos']['tmp_name'] as $key => $tmp_name) {
            $filename = time() . "_" . basename($_FILES['adjuntos']['name'][$key]);
            $pathOnDisk = $uploadDir . $filename;
            $pathForBrowser = $webBase . $filename;

            if (move_uploaded_file($tmp_name, $pathOnDisk)) {
                $stmt = $pdo->prepare("INSERT INTO respuesta_adjuntos (respuesta_id, archivo) VALUES (?, ?)");
                $stmt->execute([$respuesta_id, $pathForBrowser]);
            }
        }
    }

    header("Location: ../ver_ticket.php?id=$ticket_id");
    exit;
}
?>
