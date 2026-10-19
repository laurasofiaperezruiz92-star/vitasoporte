<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Pedido';
$paginaActual = 'pedido';
$error = '';

$productos = $conexion->query(
    'SELECT id_producto, nombre, talla, precio, foto_url, descripcion, disponible
     FROM producto WHERE disponible = 1 ORDER BY nombre'
)->fetch_all(MYSQLI_ASSOC);

$idProducto = (int)($_GET['producto'] ?? $_POST['id_producto'] ?? 0);
$productoSel = $idProducto ? obtenerProducto($conexion, $idProducto) : null;
if ($productoSel && !(int)$productoSel['disponible']) {
    $productoSel = null;
    $idProducto = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_cliente'] ?? '');
    $telefono = trim($_POST['telefono_cliente'] ?? '');
    $direccion = trim($_POST['direccion_entrega'] ?? '');
    $idProducto = (int)($_POST['id_producto'] ?? 0);

    if ($nombre === '' || $telefono === '' || $direccion === '' || !$idProducto) {
        $error = 'Completa todos los campos y elige un producto.';
    } else {
        $producto = obtenerProducto($conexion, $idProducto);
        if (!$producto || !(int)$producto['disponible']) {
            $error = 'Producto no disponible.';
        } else {
            $stmt = $conexion->prepare(
                'INSERT INTO pedido (nombre_cliente, telefono_cliente, direccion_entrega, fecha, id_producto)
                 VALUES (?,?,?,NOW(),?)'
            );
            $stmt->bind_param('sssi', $nombre, $telefono, $direccion, $idProducto);
            $stmt->execute();
            $pedido = [
                'nombre_cliente' => $nombre,
                'telefono_cliente' => $telefono,
                'direccion_entrega' => $direccion,
            ];
            $wa = whatsappUrl(mensajeWhatsAppPedido($pedido, $producto));
            require __DIR__ . '/includes/header.php';
            echo '<div class="success-panel">';
            echo '<h2>¡Pedido listo!</h2>';
            echo '<p>Registramos tu solicitud de <strong>' . e($producto['nombre']) . '</strong> (talla ' . e($producto['talla']) . ').</p>';
            echo '<p>Abre WhatsApp para confirmar disponibilidad y pago. Un producto por pedido.</p>';
            echo '<div class="success-actions">';
            echo '<a class="btn btn-wa" href="' . e($wa) . '" target="_blank" rel="noopener">Abrir WhatsApp</a>';
            echo '<a class="btn btn-ghost" href="catalogo.php">Volver al catálogo</a>';
            echo '</div></div>';
            require __DIR__ . '/includes/footer.php';
            exit;
        }
    }
    $productoSel = $idProducto ? obtenerProducto($conexion, $idProducto) : null;
}

require __DIR__ . '/includes/header.php';
?>
<section class="toolbar">
    <div>
        <h2>Pedido</h2>
        <p class="meta">Un producto por vez. Tras enviar, te llevamos a WhatsApp.</p>
        <p class="step-hint">
            <span>1 · Producto</span>
            <span>2 · Datos</span>
            <span>3 · WhatsApp</span>
        </p>
    </div>
    <a class="btn btn-ghost btn-sm" href="catalogo.php">← Catálogo</a>
</section>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="pedido-layout">
    <aside class="pedido-preview">
        <?php if ($productoSel): ?>
            <img src="<?= e(fotoProducto($productoSel['foto_url'] ?? null)) ?>" alt="<?= e($productoSel['nombre']) ?>">
            <div class="pedido-preview-body">
                <h3><?= e($productoSel['nombre']) ?></h3>
                <p class="meta"><?= e($productoSel['categoria_nombre'] ?? '') ?> · Talla <?= e($productoSel['talla']) ?></p>
                <p class="precio"><?= e(formatearPrecio($productoSel['precio'])) ?></p>
                <p class="meta"><?= e((string)($productoSel['descripcion'] ?? '')) ?></p>
            </div>
        <?php else: ?>
            <img src="img/lifestyle-estudio.jpg" alt="Elige un producto VitaSoporte">
            <div class="pedido-preview-body">
                <h3>Elige tu prenda</h3>
                <p class="meta">Selecciona un producto del listado o vuelve al catálogo y pulsa «Pedir este».</p>
            </div>
        <?php endif; ?>
    </aside>

    <form method="post" class="form">
        <label>Producto
            <select name="id_producto" id="id_producto" required onchange="if(this.value) location='pedido.php?producto='+this.value">
                <option value="">Seleccionar producto</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= (int)$p['id_producto'] ?>" <?= $idProducto === (int)$p['id_producto'] ? 'selected' : '' ?>>
                        <?= e($p['nombre']) ?> · <?= e($p['talla']) ?> · <?= e(formatearPrecio($p['precio'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Nombre completo
            <input name="nombre_cliente" required autocomplete="name" value="<?= e($_POST['nombre_cliente'] ?? '') ?>">
        </label>
        <label>Teléfono / WhatsApp
            <input name="telefono_cliente" required autocomplete="tel" inputmode="tel" value="<?= e($_POST['telefono_cliente'] ?? '') ?>">
        </label>
        <label>Dirección de entrega
            <input name="direccion_entrega" required autocomplete="street-address" value="<?= e($_POST['direccion_entrega'] ?? '') ?>">
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Enviar y abrir WhatsApp</button>
            <a class="btn btn-ghost" href="catalogo.php">Cancelar</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
