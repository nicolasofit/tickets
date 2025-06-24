<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/upbar.php';

// ── MÉTRICAS ─────────────────────────────────────────────
$total_nuevos    = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'abierto'")->fetchColumn();
$total_urgentes  = $pdo->query("SELECT COUNT(*) FROM tickets WHERE prioridad = 'alta' AND estado != 'cerrado'")->fetchColumn();
$total_resueltos = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'cerrado'")->fetchColumn();
$total_activos   = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado != 'cerrado'")->fetchColumn();

// ── LISTA DE TICKETS SEGÚN ROL ───────────────────────────
$usuario_id = $_SESSION['usuario_id'];
$rol        = $_SESSION['usuario_rol'];

if ($rol === 'admin') {
    $stmt = $pdo->query("
        SELECT t.id, t.titulo, t.prioridad, t.estado, u.nombre AS asignado, t.fecha_creacion
        FROM tickets t
        LEFT JOIN usuarios u ON u.id = t.asignado_a
        ORDER BY t.fecha_creacion DESC
        LIMIT 5
    ");
} elseif ($rol === 'soporte') {
    $stmt = $pdo->prepare("
        SELECT t.id, t.titulo, t.prioridad, t.estado, u.nombre AS asignado, t.fecha_creacion
        FROM tickets t
        LEFT JOIN usuarios u ON u.id = t.asignado_a
        WHERE t.asignado_a = ?
        ORDER BY t.fecha_creacion DESC
        LIMIT 5
    ");
    $stmt->execute([$usuario_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT t.id, t.titulo, t.prioridad, t.estado, u.nombre AS asignado, t.fecha_creacion
        FROM tickets t
        LEFT JOIN usuarios u ON u.id = t.asignado_a
        WHERE t.usuario_id = ?
        ORDER BY t.fecha_creacion DESC
        LIMIT 5
    ");
    $stmt->execute([$usuario_id]);
}

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Tickets</title>
    <link rel="stylesheet" href="styles/layout.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 20px;
        }
        .card {
            background: #1e1e2f;
            color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card h3 { margin: 0; font-size: 2em; }
        .card span { font-size: 1em; color: #aaa; }

        .ticket-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px auto;
            width: 90%;
        }
        .ticket-card {
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
            transition: background 0.3s, transform 0.2s;
            cursor: pointer;
            position: relative;
        }
        .ticket-card:hover {
            transform: translateY(-3px);
        }
        .ticket-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .ticket-meta {
            font-size: 0.9em;
            color: #eee;
        }
        .ticket-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .estado-abierto     { background-color: #2b6cb0; }
        .estado-en_proceso  { background-color: #d69e2e; }
        .estado-cerrado     { background-color: #38a169; }
        .status-abierto     { background-color: #3182ce; color: white; }
        .status-en_proceso  { background-color: #ecc94b; color: #1a202c; }
        .status-cerrado     { background-color: #48bb78; color: white; }

        #modal-ticket {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        #modal-content {
            background: #fff;
            color: #000;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            position: relative;
        }
        #modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">📊 Dashboard de Gestión de Tickets</h2>

<div class="dashboard-grid">
    <div class="card"><h3><?= $total_nuevos ?></h3><span>Nuevos tickets</span></div>
    <div class="card"><h3><?= $total_urgentes ?></h3><span>Tickets urgentes</span></div>
    <div class="card"><h3><?= $total_resueltos ?></h3><span>Tickets resueltos</span></div>
    <div class="card"><h3><?= $total_activos ?></h3><span>Tickets activos</span></div>
</div>

<h3 style="text-align:center; margin-top: 40px;">📋 Últimos Tickets</h3>
<div class="ticket-cards-container">
<?php foreach ($tickets as $t):
    $estadoClase = 'estado-' . strtolower($t['estado']);
    $statusBadge = 'status-' . strtolower($t['estado']);
    $estado_legible = ucwords(str_replace('_',' ', strtolower($t['estado'])));
?>
    <div class="ticket-card <?= $estadoClase ?>" onclick="abrirTicketModal(<?= $t['id'] ?>)">
        <span class="ticket-status <?= $statusBadge ?>"><?= $estado_legible ?></span>
        <div class="ticket-title">#<?= $t['id'] ?> - <?= htmlspecialchars($t['titulo']) ?></div>
        <div class="ticket-meta">
            Prioridad: <strong><?= ucfirst($t['prioridad']) ?></strong><br>
            Asignado: <?= $t['asignado'] ?? '–' ?><br>
            Creado: <?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div style="width:90%; margin: 50px auto; display: flex; gap: 40px; flex-wrap: wrap; justify-content: center;">
    <canvas id="chartEstado" width="400"></canvas>
    <canvas id="chartPrioridad" width="400"></canvas>
</div>

<!-- Modal -->
<div id="modal-ticket">
    <div id="modal-content">
        <span id="modal-close" onclick="cerrarModal()">&times;</span>
        <div id="modal-body">Cargando...</div>
    </div>
</div>

<script>
function abrirTicketModal(id){
    const modal = document.getElementById('modal-ticket');
    const body = document.getElementById('modal-body');
    modal.style.display = 'flex';
    body.innerHTML = 'Cargando...';
    fetch(`ver_ticket_modal.php?id=${id}`)
        .then(res => res.text())
        .then(html => body.innerHTML = html)
        .catch(err => body.innerHTML = 'Error al cargar el ticket');
}
function cerrarModal() {
    document.getElementById('modal-ticket').style.display = 'none';
}

fetch('controllers/chart_data.php')
    .then(res => res.json())
    .then(data => {
        new Chart(document.getElementById('chartEstado'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.estado),
                datasets: [{ data: Object.values(data.estado), borderWidth: 1 }]
            },
            options: { responsive: true }
        });

        new Chart(document.getElementById('chartPrioridad'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.prioridad),
                datasets: [{
                    label: 'Tickets',
                    data: Object.values(data.prioridad),
                    borderWidth: 1
                }]
            },
            options: { responsive: true }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
