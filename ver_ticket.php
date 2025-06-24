<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$ticket_id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];
$rol        = $_SESSION['usuario_rol'];

// ──────────────────────────────── 1. DATOS DEL TICKET
$stmt = $pdo->prepare("
    SELECT  t.*, 
            u.nombre                     AS creador,
            ua.nombre                    AS asignado_nombre
    FROM    tickets t
    JOIN    usuarios u  ON u.id  = t.usuario_id
    LEFT JOIN usuarios ua ON ua.id = t.asignado_a
    WHERE   t.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket)                   die("Ticket no encontrado.");
if ($rol === 'usuario' && $ticket['usuario_id'] != $usuario_id) die("No tenés permiso.");

// Adjuntos del ticket
$stmtAdjuntos = $pdo->prepare("SELECT * FROM ticket_adjuntos WHERE ticket_id = ?");
$stmtAdjuntos->execute([$ticket_id]);
$adjuntos = $stmtAdjuntos->fetchAll();


// Respuestas
$stmt = $pdo->prepare("
    SELECT  r.*, u.nombre, u.rol AS autor_rol
    FROM    respuestas r
    JOIN    usuarios u ON u.id = r.autor_id
    WHERE   r.ticket_id = ?
    ORDER BY r.fecha ASC
");
$stmt->execute([$ticket_id]);
$respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $ticket_id ?></title>
    <link rel="stylesheet" href="styles/layout.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- tipografía moderna -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --azul:#2b6cb0;--verde:#38a169;--amarillo:#d69e2e;--rojo:#e53e3e;
            --gris:#f7fafc;--gris-borde:#e2e8f0;--gris-texto:#4a5568;
        }
        body{font-family:'Inter',sans-serif;background:#f0f2f5;color:#1a202c;margin:0}
        h2{margin:30px 0;text-align:center;color:var(--azul)}
        .contenedor{max-width:1000px;margin:0 auto;padding:0 15px}
        /* ───── Tarjeta principal ───── */
        .ticket-card{
            background:#fff;border:1px solid var(--gris-borde);border-radius:12px;
            padding:24px 28px;box-shadow:0 4px 12px rgba(0,0,0,.08)
        }
        .ticket-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        .estado-badge{
            padding:4px 10px;border-radius:20px;font-size:.8em;font-weight:600;color:#fff
        }
        .estado-abierto{background:var(--azul)}
        .estado-en_proceso{background:var(--amarillo);color:#000}
        .estado-cerrado{background:var(--verde)}
        .fila{display:flex;justify-content:space-between;background:var(--gris);padding:10px 14px;
              border:1px solid var(--gris-borde);border-radius:8px;margin-top:6px;font-size:.93em}
        .fila span:first-child{font-weight:600;color:#2d3748}
        .descripcion{white-space:pre-wrap;background:#f0f4f8;border:1px solid var(--gris-borde);
                     padding:14px;border-radius:8px;margin-top:10px}
        /* ───── Chat ───── */
        .chat{margin-top:40px}
        .msg-wrap{display:flex;gap:10px;margin-bottom:20px}
        .msg{max-width:70%;padding:14px;border-radius:12px;font-size:.9em;position:relative}
        .msg.usuario{background:rgba(43,108,176,.15);align-self:flex-start}
        .msg.soporte{background:rgba(56,161,105,.15);align-self:flex-end}
        .msg.interna{background:rgba(229,62,62,.15);border:1px solid var(--rojo)}
        .msg small{display:block;margin-top:6px;color:var(--gris-texto);font-size:.75em}
        .avatar{width:36px;height:36px;background:var(--azul);color:#fff;border-radius:50%;
                display:flex;align-items:center;justify-content:center;font-weight:600}
        /* ───── Formulario respuesta ───── */
        .form-resp{margin-top:30px;background:#fff;border:1px solid var(--gris-borde);
                  border-radius:12px;padding:20px}
        textarea{width:100%;min-height:90px;border:1px solid var(--gris-borde);border-radius:8px;padding:10px}
        button{background:var(--azul);color:#fff;border:none;padding:10px 18px;
               border-radius:8px;font-weight:600;cursor:pointer}
        button.interna{background:var(--amarillo);color:#000}
        button:hover{filter:brightness(.9)}
        /* ───── Miniaturas adjuntas ───── */
        .miniatura{width:70px;height:70px;object-fit:cover;border-radius:6px;margin:4px;border:1px solid var(--gris-borde)}
        .preview-container{display:flex;flex-wrap:wrap;margin-top:8px}
        /* ───── Lightbox ───── */
        .lightbox{position:fixed;inset:0;background:rgba(0,0,0,.8);display:none;align-items:center;justify-content:center;z-index:9999}
        .lightbox img{max-width:90%;max-height:90%;border-radius:8px}
        /* Responsive */
        @media(max-width:600px){.fila{flex-direction:column;gap:3px}}
    </style>
</head>
<body>
<?php include 'includes/upbar.php'; ?>

<h2>🎫 Ticket #<?= $ticket['id'] ?> — <?= htmlspecialchars($ticket['titulo']) ?></h2>

<div class="contenedor">
    <!-- ───── Tarjeta principal ───── -->
    <div class="ticket-card">
        <div class="ticket-header">
            <h3 style="margin:0">Detalles</h3>
            <span class="estado-badge estado-<?= strtolower($ticket['estado']) ?>">
                <?= ucwords(str_replace('_',' ', $ticket['estado'])) ?>
            </span>
        </div>

        <div class="fila"><span>⚡ Prioridad:</span><span><?= ucfirst($ticket['prioridad']) ?></span></div>
        <div class="fila"><span>📂 Categoría:</span><span><?= htmlspecialchars($ticket['categoria']) ?></span></div>
        <div class="fila"><span>🙋 Solicitante:</span><span><?= $ticket['creador'] ?></span></div>
        <div class="fila"><span>👨‍💼 Asignado a:</span><span><?= $ticket['asignado_nombre'] ?? '–' ?></span></div>
        <div class="fila"><span>📅 Creado:</span><span><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span></div>
        <?php if ($ticket['fecha_actualizacion']): ?>
        <div class="fila"><span>🕓 Últ. actualización:</span><span><?= date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])) ?></span></div>
        <?php endif; ?>

        <p style="margin-top:14px;font-weight:600">📝 Descripción:</p>
        <div class="descripcion"><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></div>

        <?php if (!empty($adjuntos)): ?>
            <p style="margin-top:16px;font-weight:600">📎 Adjuntos:</p>
            <?php foreach ($adjuntos as $a):
                $file = 'uploads/' . basename($a['archivo']);
                $isImg = preg_match('/\.(jpe?g|png|gif|webp)$/i', $file);
            ?>
                <?php if ($isImg && file_exists($file)): ?>
                    <img src="<?= $file ?>" class="miniatura" onclick="abrirLightbox('<?= $file ?>')" alt="">
                <?php else: ?>
                    <a href="<?= $file ?>" target="_blank"><?= basename($file) ?></a><br>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Acciones de soporte -->
        <?php if (in_array($rol, ['soporte','admin'])): ?>
            <form action="controllers/cambiar_estado_ticket.php" method="POST" style="margin-top:20px">
                <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
                <label><strong>Cambiar estado:</strong></label>
                <select name="nuevo_estado" required>
                    <?php foreach (['abierto'=>'Abierto','en_proceso'=>'En Proceso','cerrado'=>'Cerrado'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= $ticket['estado']===$k?'selected':''?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
                <button style="margin-left:10px">Actualizar</button>
            </form>

            <form action="controllers/asignar_ticket.php" method="POST" style="margin-top:16px">
                <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
                <label><strong>Asignar a:</strong></label>
                <select name="asignado_a" required>
                    <option value="">-- Seleccionar --</option>
                    <?php
                    $usuarios = $pdo->query("SELECT id,nombre FROM usuarios WHERE rol IN ('soporte','admin') ORDER BY nombre")->fetchAll();
                    foreach($usuarios as $u):
                    ?>
                        <option value="<?= $u['id'] ?>" <?= $ticket['asignado_a']==$u['id']?'selected':'' ?>>
                            <?= htmlspecialchars($u['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button style="margin-left:10px">Asignar</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- ───── Chat de respuestas ───── -->
    <div class="chat">
        <h3>💬 Conversación</h3>
        <?php foreach ($respuestas as $r):
            if ($r['es_interna'] && !in_array($rol,['soporte','admin'])) continue;
            $esSoporte = in_array($r['autor_rol'],['soporte','admin']);
            $classes   = 'msg ' . ($esSoporte?'soporte':'usuario') . ($r['es_interna']?' interna':'');
            $nomPila   = explode(' ', $r['nombre'])[0];
            $avatar    = strtoupper($nomPila[0]);
        ?>
            <div class="msg-wrap">
                <div class="avatar" style="background:<?= $esSoporte?'var(--verde)':'var(--azul)'?>"><?= $avatar ?></div>
                <div class="<?= $classes ?>">
                    <strong><?= htmlspecialchars($nomPila) ?></strong>
                    <p><?= nl2br(htmlspecialchars($r['mensaje'])) ?></p>
                    <?php if ($r['es_interna']): ?><small>🛡️ Nota interna</small><?php endif; ?>
                    <small><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></small>
                    <!-- Adjuntos de la respuesta -->
                    <?php
                    $adjs = $pdo->prepare("SELECT * FROM respuesta_adjuntos WHERE respuesta_id=?");
                    $adjs->execute([$r['id']]); $aR=$adjs->fetchAll();
                    if($aR):
                        echo '<div style="margin-top:6px">';
                        foreach($aR as $ad):
                            $f='uploads/'.basename($ad['archivo']);
                            $img=preg_match('/\.(jpe?g|png|gif|webp)$/i',$f);
                            if($img && file_exists($f))
                                echo "<img src='$f' class='miniatura' onclick=\"abrirLightbox('$f')\">";
                            else
                                echo "<a href='$f' target='_blank'>".basename($f)."</a><br>";
                        endforeach;
                        echo '</div>';
                    endif;
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ───── Formulario de respuesta ───── -->
    <div class="form-resp">
        <form action="controllers/respuesta_controller.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
            <textarea name="mensaje" placeholder="Escribí tu respuesta..." required></textarea>
            <input type="file" name="adjuntos[]" id="adjuntos" multiple accept="image/*,application/pdf">
            <div id="preview-container" class="preview-container"></div>
            <?php if(in_array($rol,['soporte','admin'])): ?>
                <button type="submit" name="accion" value="publica">Enviar respuesta</button>
                <button class="interna" type="submit" name="accion" value="interna" style="margin-left:10px">Nota interna</button>
            <?php else: ?>
                <button type="submit">Enviar respuesta</button>
            <?php endif; ?>
        </form>
    </div>

    <div style="margin:30px 0;text-align:center">
        <a href="dashboard.php" class="button-link">⬅ Volver al Dashboard</a>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="cerrarLightbox()">
    <img id="lightbox-img" src="" alt="">
</div>

<script>
function abrirLightbox(src){document.getElementById('lightbox-img').src=src;document.getElementById('lightbox').style.display='flex'}
function cerrarLightbox(){document.getElementById('lightbox').style.display='none'}

document.getElementById("adjuntos").addEventListener("change", e=>{
  const cont=document.getElementById("preview-container");cont.innerHTML='';
  [...e.target.files].forEach(f=>{
    const ext=f.name.split('.').pop().toLowerCase();
    const img=['jpg','jpeg','png','gif','webp'].includes(ext);
    if(img){
      const rd=new FileReader();
      rd.onload=ev=>{
        const i=document.createElement('img');i.src=ev.target.result;i.className='miniatura';cont.appendChild(i)
      };rd.readAsDataURL(f);
    }else{
      const p=document.createElement('p');p.textContent='📎 '+f.name;cont.appendChild(p);
    }
  })
});
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>
