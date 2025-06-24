<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin_usuarios.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Usuario no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/layout.css">
    <link rel="stylesheet" href="styles/editar_usuario.css">
</head>
<body>

<?php include 'includes/upbar.php'; ?>

<main class="editar-contenido">
    <h2>Editar Usuario</h2>
    <form action="controllers/actualizar_usuario.php" method="POST" class="form-editar">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <label>Nombre completo:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

        <label>Rol:</label>
        <select name="rol">
            <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
            <option value="soporte" <?= $usuario['rol'] === 'soporte' ? 'selected' : '' ?>>Soporte</option>
            <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select>

        <button type="submit">Guardar Cambios</button>
        <a href="admin_usuarios.php" class="cancelar-link">Cancelar</a>
    </form>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
