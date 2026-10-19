<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Categorías';
$paginaAdmin = 'categorias';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $id = (int)($_POST['id_categoria'] ?? 0);
    if ($accion === 'crear' || $accion === 'editar') {
        if ($nombre === '') {
            $error = 'El nombre es obligatorio.';
        } elseif ($accion === 'crear') {
            $stmt = $conexion->prepare('INSERT INTO categoria (nombre) VALUES (?)');
            $stmt->bind_param('s', $nombre);
            $stmt->execute();
            flash('ok', 'Categoría creada.');
            redirect('categorias.php');
        } else {
            $stmt = $conexion->prepare('UPDATE categoria SET nombre = ? WHERE id_categoria = ?');
            $stmt->bind_param('si', $nombre, $id);
            $stmt->execute();
            flash('ok', 'Categoría actualizada.');
            redirect('categorias.php');
        }
    }
    if ($accion === 'eliminar') {
        try {
            $stmt = $conexion->prepare('DELETE FROM categoria WHERE id_categoria = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            flash('ok', 'Categoría eliminada.');
            redirect('categorias.php');
        } catch (Throwable $e) {
            $error = 'No se puede eliminar: tiene productos asociados.';
        }
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $conexion->prepare('SELECT * FROM categoria WHERE id_categoria = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

$categorias = obtenerCategorias($conexion);
require __DIR__ . '/../includes/header_admin.php';
?>
<div class="toolbar"><h2>Gestión de categorías</h2></div>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="form" style="margin-bottom:1.5rem">
    <h3><?= $editar ? 'Editar' : 'Nueva' ?> categoría</h3>
    <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
    <?php if ($editar): ?><input type="hidden" name="id_categoria" value="<?= (int)$editar['id_categoria'] ?>"><?php endif; ?>
    <label>Nombre <input name="nombre" required value="<?= e($editar['nombre'] ?? '') ?>"></label>
    <button class="btn btn-primary" type="submit">Guardar</button>
    <?php if ($editar): ?><a href="categorias.php">Cancelar</a><?php endif; ?>
</form>

<div class="tabla-wrap">
<table>
    <thead><tr><th>Nombre</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($categorias as $c): ?>
        <tr>
            <td><?= e($c['nombre']) ?></td>
            <td class="acciones">
                <a class="btn btn-sm btn-primary" href="?editar=<?= (int)$c['id_categoria'] ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_categoria" value="<?= (int)$c['id_categoria'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
