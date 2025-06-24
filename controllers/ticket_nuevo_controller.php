<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $categoria = trim($_POST['categoria']);
    $prioridad = $_POST['prioridad'];

    // Insertar el nuevo ticket
    $stmt = $pdo->prepare("INSERT INTO tickets (titulo, descripcion, categoria, prioridad, estado, usuario_id) VALUES (?, ?, ?, ?, 'abierto', ?)");
    $stmt->execute([$titulo, $descripcion, $categoria, $prioridad, $usuario_id]);
    $ticket_id = $pdo->lastInsertId();

    // Subir archivos adjuntos si hay
    if (!empty($_FILES['adjuntos']['name'][0])) {
        $uploadDir = '../uploads/';
        foreach ($_FILES['adjuntos']['tmp_name'] as $key => $tmp_name) {
            $filename = time() . "_" . basename($_FILES['adjuntos']['name'][$key]);
            $path = $uploadDir . $filename;
            if (move_uploaded_file($tmp_name, $path)) {
                $stmt = $pdo->prepare("INSERT INTO ticket_adjuntos (ticket_id, archivo) VALUES (?, ?)");
                $stmt->execute([$ticket_id, 'uploads/' . $filename]);
            }
        }
    }

    header("Location: ../ver_ticket.php?id=$ticket_id");
    exit;
}
?>
