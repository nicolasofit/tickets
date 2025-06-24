<?php include 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="styles/register.css">
</head>
<body>
    <div class="register-container">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'correo_existente'): ?>
    <div class="error-msg">⚠️ El correo ya está registrado.</div>
<?php endif; ?>

        <h2>Registro de usuario</h2>
        <form action="controllers/register.php" method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Registrarme</button>
        </form>
        <a href="login.php" class="login-link">Ya tengo cuenta</a>
    </div>
</body>
</html>
