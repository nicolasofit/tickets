<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

// Crear nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_categoria'])) {
    $nombre = trim($_POST['nombre']);
    if (!empty($nombre)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
    }
    header("Location: admin_categorias.php");
    exit;
}

// Actualizar estado o nombre
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $pdo->query("UPDATE categorias SET activa = NOT activa WHERE id = $id");
    header("Location: admin_categorias.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $id = intval($_POST['editar_id']);
    $nombre = trim($_POST['editar_nombre']);
    if (!empty($nombre)) {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        $stmt->execute([$nombre, $id]);
    }
    header("Location: admin_categorias.php");
    exit;
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Administrar Categorías</title>
    <link rel="stylesheet" href="styles/layout.css">
    <style>
        .cat-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .cat-container h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #fff;
        }

        .badge.activa {
            background-color: #2ecc71;
        }

        .badge.inactiva {
            background-color: #e74c3c;
        }

        form {
            margin-top: 20px;
        }

        input[type="text"] {
            width: calc(100% - 130px);
            padding: 8px;
            margin-right: 10px;
        }

        button {
            padding: 8px 16px;
            border: none;
            background: #3498db;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        .acciones a {
            font-size: 14px;
            margin-right: 10px;
            color: #3498db;
        }

        .acciones a:hover {
            text-decoration: underline;
        }

        .editar-form {
            display: flex;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<?php include 'includes/upbar.php'; ?>

<div class="cat-container">
    <h2>Administrar Categorías de Tickets</h2>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categorias as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                <td>
                    <span class="badge <?= $cat['activa'] ? 'activa' : 'inactiva' ?>">
                        <?= $cat['activa'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                </td>
                <td class="acciones">
                    <a href="?toggle=<?= $cat['id'] ?>"><?= $cat['activa'] ? 'Desactivar' : 'Activar' ?></a>
                    <form method="POST" class="editar-form">
                        <input type="hidden" name="editar_id" value="<?= $cat['id'] ?>">
                        <input type="text" name="editar_nombre" value="<?= htmlspecialchars($cat['nombre']) ?>">
                        <button type="submit">Guardar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Agregar nueva categoría</h3>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre de la categoría" required>
        <button type="submit" name="nueva_categoria">Agregar</button>
    </form>
</div>

</body>
</html>
