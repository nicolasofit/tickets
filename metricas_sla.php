<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/upbar.php';

$stmt = $pdo->query("
    SELECT 
        t.id AS ticket_id,
        t.titulo,
        t.estado,
        t.prioridad,
        t.fecha_creacion,
        t.fecha_actualizacion,

        -- Tiempo de resolución (si cerrado)
        CASE 
            WHEN t.estado = 'cerrado' THEN 
                TIMESTAMPDIFF(HOUR, t.fecha_creacion, t.fecha_actualizacion)
            ELSE NULL
        END AS horas_resolucion,

        -- Tiempo hasta la primera respuesta
        (
            SELECT TIMESTAMPDIFF(MINUTE, t.fecha_creacion, MIN(r.fecha))
            FROM respuestas r
            WHERE r.ticket_id = t.id AND r.es_interna = 0
        ) AS minutos_primera_respuesta

    FROM tickets t
    ORDER BY t.fecha_creacion DESC
");

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener promedios de respuesta
$promedios = $pdo->query("
    SELECT r1.ticket_id, ROUND(AVG(TIMESTAMPDIFF(MINUTE, r2.fecha, r1.fecha)), 2) AS promedio_respuesta
    FROM respuestas r1
    JOIN respuestas r2 ON r1.ticket_id = r2.ticket_id AND r2.fecha < r1.fecha
    WHERE r1.es_interna = 0 AND r2.es_interna = 0
    GROUP BY r1.ticket_id
")->fetchAll(PDO::FETCH_KEY_PAIR);


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Métricas SLA</title>
    <link rel="stylesheet" href="styles/layout.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .sla-ok { color: green; font-weight: bold; }
        .sla-fail { color: red; font-weight: bold; }
        table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #222;
            color: #fff;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">📈 Métricas SLA de Tickets</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Prioridad</th>
            <th>F. Creación</th>
            <th>F. Cierre</th>
            <th>⏱ Resolución</th>
            <th>📥 1.ª Respuesta</th>
            <th>🔁 Prom. Respuesta</th>
            <th>✅ SLA</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tickets as $t): 
            // Tiempo máximo según prioridad
            $max_sla = match ($t['prioridad']) {
                'alta' => 24,
                'media' => 48,
                'baja' => 72,
                default => null
            };

            $cumple_sla = $t['estado'] === 'cerrado' && $t['horas_resolucion'] !== null && $max_sla !== null
                ? ($t['horas_resolucion'] <= $max_sla)
                : null;

            $promedio_respuesta = $promedios[$t['ticket_id']] ?? '–';
        ?>
        <tr>
            <td><?= $t['ticket_id'] ?></td>
            <td><?= htmlspecialchars($t['titulo']) ?></td>
            <td><?= ucfirst($t['estado']) ?></td>
            <td><?= ucfirst($t['prioridad']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
            <td><?= $t['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($t['fecha_actualizacion'])) : '–' ?></td>
            <td><?= $t['horas_resolucion'] !== null ? $t['horas_resolucion'] . ' hs' : '–' ?></td>
            <td><?= $t['minutos_primera_respuesta'] !== null ? $t['minutos_primera_respuesta'] . ' min' : '–' ?></td>
            <td><?= is_numeric($promedio_respuesta) ? $promedio_respuesta . ' min' : '–' ?></td>
            <td>
                <?php
                if ($cumple_sla === true) echo '<span class="sla-ok">✔ Cumple</span>';
                elseif ($cumple_sla === false) echo '<span class="sla-fail">✘ No cumple</span>';
                else echo '–';
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>

</body>
</html>
