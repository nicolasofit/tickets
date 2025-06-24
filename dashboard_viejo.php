<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'];

// Traer tickets según el rol
if ($rol === 'usuario') {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               u.nombre AS nombre_solicitante, 
               ua.nombre AS nombre_asignado
        FROM tickets t 
        JOIN usuarios u ON t.usuario_id = u.id 
        LEFT JOIN usuarios ua ON t.asignado_a = ua.id
        WHERE t.usuario_id = ? 
        ORDER BY t.fecha_creacion DESC
    ");
    $stmt->execute([$usuario_id]);
} else {
    $stmt = $pdo->query("
        SELECT t.*, 
               u.nombre AS nombre_solicitante, 
               ua.nombre AS nombre_asignado
        FROM tickets t 
        JOIN usuarios u ON t.usuario_id = u.id 
        LEFT JOIN usuarios ua ON t.asignado_a = ua.id
        ORDER BY t.fecha_creacion DESC
    ");
}

$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Tickets</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/layout.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php include 'includes/upbar.php'; ?>

<h2>Panel de Tickets</h2>

<div class="dashboard-container">
    <?php if (count($tickets) > 0): ?>
        <?php foreach ($tickets as $ticket): ?>
            <div class="ticket-card">
                <h3><?= htmlspecialchars($ticket['titulo']) ?></h3>
                <div class="ticket-meta">
                    <?= htmlspecialchars($ticket['nombre_solicitante']) ?> · 
                    Categoría: <?= htmlspecialchars($ticket['categoria']) ?> · 
                    Prioridad: <?= ucfirst($ticket['prioridad']) ?> · 
                    <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?> <br>
                    <?php if (!empty($ticket['nombre_asignado'])): ?>
                        <span>Asignado a: <strong><?= htmlspecialchars($ticket['nombre_asignado']) ?></strong></span>
                    <?php else: ?>
                        <span style="color: gray;">No asignado aún</span>
                    <?php endif; ?>
                </div>

                <span class="ticket-estado <?= $ticket['estado'] ?>">
                    <?= ucwords(str_replace('_', ' ', $ticket['estado'])) ?>
                </span>

                <div class="ticket-acciones">
                    <a href="ver_ticket.php?id=<?= $ticket['id'] ?>">Ver</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center;">No hay tickets para mostrar.</p>
    <?php endif; ?>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="nuevo_ticket.php" class="button-link">➕ Crear nuevo ticket</a>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
