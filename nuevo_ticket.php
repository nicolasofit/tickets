<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Ticket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/nuevo_ticket.css">
    <link rel="stylesheet" href="styles/layout.css">
</head>
<body>

<?php include 'includes/upbar.php'; ?>

<div class="form-wrapper">
    <h2>📩 Crear Nuevo Ticket</h2>

    <form action="controllers/ticket_nuevo_controller.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="crear_ticket" value="1">

        <div class="form-group">
            <label for="titulo">Título del problema</label>
            <input type="text" name="titulo" id="titulo" placeholder="Ej: No puedo acceder al sistema" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" placeholder="Describí el problema con el mayor detalle posible" required></textarea>
        </div>

        <div class="form-group">
    <label for="categoria">Categoría</label>
    <select name="categoria" id="categoria" required>
        <option value="">Seleccioná una categoría</option>
        <?php
        require_once 'includes/db.php';
        $stmt = $pdo->query("SELECT id, nombre FROM categorias WHERE activa = 1 ORDER BY nombre ASC");
        while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value=\"{$cat['nombre']}\">" . htmlspecialchars($cat['nombre']) . "</option>";
        }
        ?>
    </select>
</div>


        <div class="form-group">
            <label for="prioridad">Prioridad</label>
            <select name="prioridad" id="prioridad">
                <option value="baja">🟢 Baja</option>
                <option value="media" selected>🟡 Media</option>
                <option value="alta">🔴 Alta</option>
            </select>
        </div>

        <div class="form-group">
            <label for="adjuntos">Adjuntar archivos (imágenes o documentos)</label>
            <input type="file" name="adjuntos[]" id="adjuntos" multiple>
        </div>

        <button type="submit" name="crear_ticket">🚀 Crear Ticket</button>
    </form>

    <div class="back-link">
        <a href="dashboard.php" class="button-link">⬅ Volver al Dashboard</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
