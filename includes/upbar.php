<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['usuario_rol'];
?>

<header class="main-header">
    <link rel="stylesheet" href="styles/layout.css">

    <div class="logo">🎫 Ticketera</div>

    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

    <nav class="main-nav" id="mainNav">
        <a href="dashboard.php">Inicio</a>
        <a href="nuevo_ticket.php">Nuevo Ticket</a>

        <?php if ($rol === 'admin'): ?>
            <a href="admin_usuarios.php">Usuarios</a>
            <a href="admin_categorias.php">Categorías</a>
            <a href="metricas_sla.php">Métricas</a>
        <?php endif; ?>

        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <script>
        function toggleMenu() {
            const nav = document.getElementById("mainNav");
            nav.classList.toggle("open");
        }
    </script>
</header>
