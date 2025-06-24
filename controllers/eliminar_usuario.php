<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        die("ID inválido.");
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: ../admin_usuarios.php?msg=usuario_eliminado");
        exit;
    } catch (PDOException $e) {
        die("Error al eliminar usuario: " . $e->getMessage());
    }
} else {
    die("Método inválido.");
}
