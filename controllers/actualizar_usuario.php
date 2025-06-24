<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    die("⛔ Acceso no autorizado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $rol = $_POST['rol'] ?? '';

    if (!$id || !$nombre || !$email || !in_array($rol, ['usuario', 'soporte', 'admin'])) {
        die("⚠️ Datos inválidos.");
    }

    // Verificamos si el email ya existe en otro usuario
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        header("Location: ../editar_usuario.php?id=$id&error=email_duplicado");
        exit;
    }

    // Actualizar el usuario
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?");
    $stmt->execute([$nombre, $email, $rol, $id]);

    header("Location: ../admin_usuarios.php?msg=usuario_actualizado");
    exit;
}

header("Location: ../admin_usuarios.php");
exit;
