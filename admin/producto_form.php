<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Formulario producto';
$paginaAdmin = 'productos';
$error = '';
$id = (int)($_GET['id'] ?? $_POST['id_producto'] ?? 0);
$producto = $id ? obtenerProducto($conexion, $id) : null;
$categorias = obtenerCategorias($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $talla = trim($_POST['talla'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $foto = trim($_POST['foto_url'] ?? '');
    $idCat = (int)($_POST['id_categoria'] ?? 0);
    $id = (int)($_POST['id_producto'] ?? 0);

    if ($nombre === '' || $talla === '' || !$idCat) {
        $error = 'Nombre, talla y categoría son obligatorios.';
    } elseif ($id > 0) {
        $stmt = $conexion->prepare(
            'UPDATE producto SET nombre=?, talla=?, precio=?, descripcion=?, disponible=?, foto_url=?, id_categoria=? WHERE id_producto=?'
        );
        $stmt->bind_param('ssdsisii', $nombre, $talla, $precio, $descripcion, $disponible, $foto, $idCat, $id);
        $stmt->execute();
        flash('ok', 'Producto actualizado.');
        redirect('productos.php');
    } else {
        $stmt = $conexion->prepare(
            'INSERT INTO producto (nombre, talla, precio, descripcion, disponible, foto_url, id_categoria) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->bind_param('ssdsisi', $nombre, $talla, $precio, $descripcion, $disponible, $foto, $idCat);
        $stmt->execute();
        flash('ok', 'Producto creado.');
        redirect('productos.php');
    }
    $producto = [
        'id_producto' => $id,
        'nombre' => $nombre,
        'talla' => $talla,
        'precio' => $precio,
        'descripcion' => $descripcion,
        'disponible' => $disponible,
        'foto_url' => $foto,
        'id_categoria' => $idCat,
    ];
}

require __DIR__ . '/../includes/header_admin.php';
?>
<p><a href="productos.php">‹ Volver a productos</a></p>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="form form-narrow">
    <h2><?= $producto && !empty($producto['id_producto']) ? 'Editar' : 'Nuevo' ?> producto</h2>
    <?php if ($producto && !empty($producto['id_producto'])): ?>
        <input type="hidden" name="id_producto" value="<?= (int)$producto['id_producto'] ?>">
    <?php endif; ?>
    <label>Nombre <input name="nombre" required value="<?= e($producto['nombre'] ?? '') ?>"></label>
    <label>Talla <input name="talla" required value="<?= e($producto['talla'] ?? '') ?>"></label>
    <label>Precio <input type="number" step="100" min="0" name="precio" required value="<?= e((string)($producto['precio'] ?? '0')) ?>"></label>
    <label>Descripción <textarea name="descripcion" rows="3"><?= e($producto['descripcion'] ?? '') ?></textarea></label>
    <label>Ruta foto (ej. img/prod-faja-lumbar.jpg)
        <input name="foto_url" placeholder="img/nombre.jpg" value="<?= e($producto['foto_url'] ?? '') ?>">
    </label>
    <?php if (!empty($producto['foto_url'])): ?>
        <p class="meta">Vista previa:</p>
        <img class="thumb" src="../<?= e(fotoProducto($producto['foto_url'])) ?>" alt="" style="width:120px;height:120px;object-fit:cover;border-radius:12px">
    <?php endif; ?>
    <label>Categoría
        <select name="id_categoria" required>
            <option value="">Seleccionar</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)($producto['id_categoria'] ?? 0) === (int)$c['id_categoria']) ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label><input type="checkbox" name="disponible" <?= !isset($producto) || (int)($producto['disponible'] ?? 1) === 1 ? 'checked' : '' ?>> Disponible</label>
    <button class="btn btn-primary" type="submit">Guardar</button>
</form>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
