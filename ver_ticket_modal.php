<?php
require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Error: ID inválido.</p>";
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT t.*, u.nombre AS solicitante, ua.nombre AS asignado
    FROM tickets t
    LEFT JOIN usuarios u ON t.usuario_id = u.id
    LEFT JOIN usuarios ua ON t.asignado_a = ua.id
    WHERE t.id = ?
");
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "<p>Ticket no encontrado.</p>";
    exit;
}

$estado_legible = ucwords(str_replace('_', ' ', $ticket['estado']));
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    #modal-content {
        font-family: 'Inter', sans-serif;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        max-width: 650px;
        animation: fadeIn 0.2s ease-out;
    }

    #modal-content h3 {
        font-size: 1.6em;
        margin-bottom: 16px;
        color: #2b6cb0;
        font-weight: 700;
        border-bottom: 2px solid #edf2f7;
        padding-bottom: 8px;
    }

    .ticket-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        border-radius: 8px;
        margin-bottom: 6px;
        font-size: 0.94em;
    }

    .ticket-row span:first-child {
        font-weight: 600;
        color: #2d3748;
    }

    .ticket-row span:last-child {
        color: #4a5568;
        text-align: right;
    }

    .descripcion {
        background: #f0f4f8;
        border: 1px solid #cbd5e0;
        padding: 12px 14px;
        margin-top: 8px;
        border-radius: 8px;
        font-size: 0.95em;
        color: #2a2e35;
        white-space: pre-wrap;
    }

    .boton-editar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
        background: #2b6cb0;
        color: #fff;
        padding: 10px 16px;
        font-size: 0.9em;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    .boton-editar:hover {
        background: #2c5282;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .boton-editar i {
        font-style: normal;
        background: #ffffff22;
        padding: 4px 6px;
        border-radius: 4px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<h3>🎫 Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['titulo']) ?></h3>

<div class="ticket-row"><span>🟡 <strong>Estado:</strong></span><span><?= $estado_legible ?></span></div>
<div class="ticket-row"><span>⚡ <strong>Prioridad:</strong></span><span><?= ucfirst($ticket['prioridad']) ?></span></div>
<div class="ticket-row"><span>📂 <strong>Categoría:</strong></span><span><?= htmlspecialchars($ticket['categoria']) ?></span></div>
<div class="ticket-row"><span>🙋 <strong>Solicitante:</strong></span><span><?= $ticket['solicitante'] ?></span></div>
<div class="ticket-row"><span>👨‍💼 <strong>Asignado a:</strong></span><span><?= $ticket['asignado'] ?? '–' ?></span></div>
<div class="ticket-row"><span>📅 <strong>Fecha de creación:</strong></span><span><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span></div>
<?php if ($ticket['fecha_actualizacion']): ?>
    <div class="ticket-row"><span>🕓 <strong>Última actualización:</strong></span><span><?= date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])) ?></span></div>
<?php endif; ?>

<p><strong>📝 Descripción:</strong></p>
<div class="descripcion"><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></div>

<a class="boton-editar" href="ver_ticket.php?id=<?= $ticket['id'] ?>" target="_blank">
    <i>✏️</i> Editar Ticket
</a>
