<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, nombre, email, rol FROM usuarios ORDER BY nombre");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuarios</title>
    <link rel="stylesheet" href="styles/layout.css">
    <link rel="stylesheet" href="styles/admin_usuarios.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php include 'includes/upbar.php'; ?>

<h2>Gestión de Usuarios</h2>

<div class="usuarios-container">

    <!-- Vista tabla (desktop) -->
    <table class="usuario-tabla">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td><?= ucfirst($usuario['rol']) ?></td>
                <td>
                    <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn-editar">Editar</a>
                    <form action="controllers/eliminar_usuario.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este usuario?');">
                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Vista tarjetas (mobile) -->
    <?php foreach ($usuarios as $usuario): ?>
        <div class="usuario-card">
            <h3><?= htmlspecialchars($usuario['nombre']) ?></h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
            <p><strong>Rol:</strong> <?= ucfirst($usuario['rol']) ?></p>
            <div class="acciones">
                <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn-editar">Editar</a>
                <form action="controllers/eliminar_usuario.php" method="POST" onsubmit="return confirm('¿Eliminar este usuario?');">
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
