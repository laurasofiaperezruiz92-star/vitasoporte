<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Pedido';
$paginaActual = 'pedido';
$error = '';

// Compatibilidad: enlaces antiguos "Pedir este" suman el producto a la bolsa.
if (isset($_GET['producto'])) {
    $idProducto = (int) $_GET['producto'];
    $producto = $idProducto ? obtenerProducto($conexion, $idProducto) : null;
    if ($producto && (int) $producto['disponible']) {
        $tallas = tallasDeProducto($producto);
        bolsaAgregar($idProducto, $tallas[0], 1);
    }
    redirect('pedido.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_cliente'] ?? '');
    $telefono = trim($_POST['telefono_cliente'] ?? '');
    $direccion = trim($_POST['direccion_entrega'] ?? '');
    $detalle = bolsaDetalle($conexion);

    if ($nombre === '' || $telefono === '' || $direccion === '') {
        $error = 'Completa todos tus datos: nombre, teléfono y dirección de entrega.';
    } elseif (!$detalle['items']) {
        $error = 'Tu bolsa está vacía. Agrega productos desde el catálogo.';
    } else {
        $total = $detalle['total'];
        $idPedido = 0;

        try {
            $conexion->begin_transaction();

            $stmt = $conexion->prepare(
                'INSERT INTO pedido (nombre_cliente, telefono_cliente, direccion_entrega, fecha, total)
                 VALUES (?,?,?,NOW(),?)'
            );
            $stmt->bind_param('sssd', $nombre, $telefono, $direccion, $total);
            $stmt->execute();
            $idPedido = (int) $conexion->insert_id;

            $stmtDet = $conexion->prepare(
                'INSERT INTO pedido_detalle (id_pedido, id_producto, talla, cantidad, precio_unitario)
                 VALUES (?,?,?,?,?)'
            );
            foreach ($detalle['items'] as $it) {
                $stmtDet->bind_param(
                    'iisid',
                    $idPedido,
                    $it['id_producto'],
                    $it['talla'],
                    $it['cantidad'],
                    $it['precio']
                );
                $stmtDet->execute();
            }

            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            $error = 'No se pudo registrar el pedido. Intenta de nuevo.';
        }

        if ($error === '') {
            $pedidoCliente = [
                'nombre_cliente' => $nombre,
                'telefono_cliente' => $telefono,
                'direccion_entrega' => $direccion,
            ];
            $wa = whatsappUrl(mensajeWhatsAppBolsa($pedidoCliente, $detalle['items'], $total));
            bolsaVaciar();

            require __DIR__ . '/includes/header.php';
            echo '<div class="success-panel">';
            echo '<h2>¡Pedido listo! #' . $idPedido . '</h2>';
            echo '<p>Registramos tu pedido, <strong>' . e($nombre) . '</strong>:</p>';
            echo '<ul class="success-items">';
            foreach ($detalle['items'] as $it) {
                echo '<li>' . e($it['nombre']) . ' (talla ' . e($it['talla']) . ') × ' . (int) $it['cantidad']
                    . ' — ' . e(formatearPrecio($it['subtotal'])) . '</li>';
            }
            echo '</ul>';
            echo '<p><strong>Total: ' . e(formatearPrecio($total)) . '</strong></p>';
            echo '<p>Tus datos quedan en el panel de la tienda. Abre WhatsApp para confirmar disponibilidad y pago.</p>';
            echo '<p class="meta">También puedes recoger en tienda: ' . e(TIENDA_UBICACION) . '.</p>';
            echo '<div class="success-actions">';
            echo '<a class="btn btn-wa" href="' . e($wa) . '" target="_blank" rel="noopener">Abrir WhatsApp</a>';
            echo '<a class="btn btn-ghost" href="catalogo.php">Volver al catálogo</a>';
            echo '</div></div>';
            require __DIR__ . '/includes/footer.php';
            exit;
        }
    }
}

$detalle = bolsaDetalle($conexion);
$items = $detalle['items'];
$total = $detalle['total'];

require __DIR__ . '/includes/header.php';
?>
<section class="toolbar">
    <div>
        <h2>Confirmar pedido</h2>
        <p class="meta">Revisa tu bolsa, deja tus datos y te llevamos a WhatsApp.</p>
        <p class="step-hint">
            <span>1 · Bolsa</span>
            <span>2 · Tus datos</span>
            <span>3 · WhatsApp</span>
        </p>
    </div>
    <a class="btn btn-ghost btn-sm" href="bolsa.php">← Editar bolsa</a>
</section>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<?php if (!$items): ?>
    <p class="empty-state">Tu bolsa está vacía. <a href="catalogo.php">Ver el catálogo</a> para agregar productos.</p>
<?php else: ?>
<div class="pedido-layout">
    <aside class="pedido-preview">
        <div class="pedido-preview-body">
            <h3>Resumen de tu bolsa</h3>
            <?php foreach ($items as $it): ?>
                <p class="resumen-linea">
                    <span><?= e($it['nombre']) ?> <em>(talla <?= e($it['talla']) ?>) × <?= (int) $it['cantidad'] ?></em></span>
                    <strong><?= e(formatearPrecio($it['subtotal'])) ?></strong>
                </p>
            <?php endforeach; ?>
            <p class="resumen-linea resumen-total">
                <span>Total</span>
                <strong><?= e(formatearPrecio($total)) ?></strong>
            </p>
            <p class="meta">¿Falta algo? <a href="bolsa.php">Edita tu bolsa</a> o <a href="catalogo.php">sigue en el catálogo</a>.</p>
        </div>
    </aside>

    <form method="post" class="form">
        <h3 style="color:var(--cafe-deep)">Datos del cliente</h3>
        <label>Nombre completo
            <input name="nombre_cliente" required autocomplete="name" value="<?= e($_POST['nombre_cliente'] ?? '') ?>">
        </label>
        <label>Teléfono / WhatsApp
            <input name="telefono_cliente" required autocomplete="tel" inputmode="tel" value="<?= e($_POST['telefono_cliente'] ?? '') ?>">
        </label>
        <label>Dirección de entrega
            <input name="direccion_entrega" required autocomplete="street-address" value="<?= e($_POST['direccion_entrega'] ?? '') ?>">
        </label>
        <p class="meta">Estos son los datos que verá la tienda en su panel para preparar tu pedido. O recoge en tienda: <?= e(TIENDA_UBICACION) ?>.</p>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Enviar y abrir WhatsApp</button>
            <a class="btn btn-ghost" href="bolsa.php">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
