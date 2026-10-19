<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Productos';
$paginaAdmin = 'productos';

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conexion->query("UPDATE producto SET disponible = IF(disponible=1,0,1) WHERE id_producto = $id");
    flash('ok', 'Disponibilidad actualizada.');
    redirect('productos.php');
}

if (isset($_POST['accion']) && $_POST['accion'] === 'precio') {
    $id = (int)($_POST['id_producto'] ?? 0);
    $precio = (float)($_POST['precio'] ?? -1);
    if ($id <= 0 || $precio < 0) {
        flash('error', 'Precio no válido: debe ser un número mayor o igual a 0.');
    } else {
        $stmt = $conexion->prepare('UPDATE producto SET precio = ? WHERE id_producto = ?');
        $stmt->bind_param('di', $precio, $id);
        $stmt->execute();
        $prod = obtenerProducto($conexion, $id);
        flash('ok', 'Precio actualizado: ' . ($prod['nombre'] ?? 'producto') . ' ahora cuesta ' . formatearPrecio($precio) . '. Ya se ve en el catálogo.');
    }
    redirect('productos.php');
}

if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = (int)($_POST['id_producto'] ?? 0);
    try {
        $stmt = $conexion->prepare('DELETE FROM producto WHERE id_producto = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        flash('ok', 'Producto eliminado.');
    } catch (Throwable $e) {
        flash('error', 'No se puede eliminar: tiene pedidos asociados.');
    }
    redirect('productos.php');
}

$productos = $conexion->query(
    'SELECT p.*, c.nombre AS categoria_nombre
     FROM producto p JOIN categoria c ON c.id_categoria = p.id_categoria
     ORDER BY p.nombre'
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="toolbar">
    <div>
        <h2>Gestión de productos</h2>
        <p class="meta">Modifica el precio al antojo en la misma tabla y guarda: se refleja de una en el catálogo y la bolsa. Los pedidos anteriores conservan su precio.</p>
    </div>
    <a class="btn btn-primary" href="producto_form.php">+ Nuevo</a>
</div>

<div class="tabla-wrap">
<table>
    <thead>
        <tr><th></th><th>Nombre</th><th>Talla</th><th>Categoría</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($productos as $p): ?>
        <tr>
            <td><img class="thumb" src="../<?= e(fotoProducto($p['foto_url'])) ?>" alt=""></td>
            <td><?= e($p['nombre']) ?></td>
            <td><?= e($p['talla']) ?></td>
            <td><?= e($p['categoria_nombre']) ?></td>
            <td>
                <form class="precio-form" method="post" title="Cambia el precio y guarda: se refleja ya en el catálogo">
                    <input type="hidden" name="accion" value="precio">
                    <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                    <span aria-hidden="true">$</span>
                    <input type="number" name="precio" min="0" step="100" required
                           value="<?= (int)$p['precio'] ?>" aria-label="Precio de <?= e($p['nombre']) ?>">
                    <button class="btn btn-sm btn-primary" type="submit">Guardar precio</button>
                </form>
                <small class="meta">Actual: <?= e(formatearPrecio($p['precio'])) ?></small>
            </td>
            <td>
                <a href="?toggle=<?= (int)$p['id_producto'] ?>">
                    <span class="<?= (int)$p['disponible'] ? 'badge-ok' : 'badge-off' ?>">
                        <?= (int)$p['disponible'] ? 'Disponible' : 'No' ?>
                    </span>
                </a>
            </td>
            <td class="acciones">
                <a class="btn btn-sm btn-primary" href="producto_form.php?id=<?= (int)$p['id_producto'] ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
