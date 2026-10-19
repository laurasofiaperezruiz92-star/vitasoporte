<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Pedidos';
$paginaAdmin = 'pedidos';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $idPedido = (int) ($_POST['id_pedido'] ?? 0);
    if ($idPedido > 0) {
        // pedido_detalle se borra en cascada (ON DELETE CASCADE)
        $stmt = $conexion->prepare('DELETE FROM pedido WHERE id_pedido = ?');
        $stmt->bind_param('i', $idPedido);
        $stmt->execute();
        flash('ok', 'Pedido #' . $idPedido . ' eliminado.');
    }
    redirect('pedidos.php');
}

$pedidos = $conexion->query(
    'SELECT * FROM pedido ORDER BY fecha DESC, id_pedido DESC'
)->fetch_all(MYSQLI_ASSOC);

$detallesPorPedido = [];
if ($pedidos) {
    $detalles = $conexion->query(
        'SELECT d.id_pedido, d.talla, d.cantidad, d.precio_unitario, pr.nombre AS producto
         FROM pedido_detalle d
         JOIN producto pr ON pr.id_producto = d.id_producto
         ORDER BY d.id_detalle'
    )->fetch_all(MYSQLI_ASSOC);
    foreach ($detalles as $d) {
        $detallesPorPedido[(int) $d['id_pedido']][] = $d;
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="toolbar">
    <h2>Bandeja de pedidos</h2>
    <p class="meta"><?= count($pedidos) ?> pedido<?= count($pedidos) === 1 ? '' : 's' ?> · Aquí ves todo lo que el cliente escribió en el checkout.</p>
</div>

<div class="tabla-wrap">
<table>
    <thead>
        <tr><th>Pedido</th><th>Fecha</th><th>Datos del cliente</th><th>Productos (talla × cantidad)</th><th>Total</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pedidos as $p): ?>
        <tr>
            <td><strong>#<?= (int) $p['id_pedido'] ?></strong></td>
            <td><?= e($p['fecha']) ?></td>
            <td>
                <strong><?= e($p['nombre_cliente']) ?></strong><br>
                Tel: <?= e($p['telefono_cliente']) ?>
                (<a href="<?= e(whatsappUrl('Hola ' . $p['nombre_cliente'] . ', te escribimos de VitaSoporte por tu pedido #' . (int) $p['id_pedido'] . '.')) ?>" target="_blank" rel="noopener">WhatsApp</a>)<br>
                Dirección: <?= e($p['direccion_entrega']) ?>
            </td>
            <td>
                <?php foreach ($detallesPorPedido[(int) $p['id_pedido']] ?? [] as $d): ?>
                    <?= e($d['producto']) ?> · talla <?= e($d['talla']) ?> × <?= (int) $d['cantidad'] ?>
                    — <?= e(formatearPrecio((float) $d['precio_unitario'] * (int) $d['cantidad'])) ?><br>
                <?php endforeach; ?>
            </td>
            <td><strong><?= e(formatearPrecio($p['total'])) ?></strong></td>
            <td class="acciones">
                <form method="post" onsubmit="return confirm('¿Eliminar el pedido #<?= (int) $p['id_pedido'] ?>? Esta acción no se puede deshacer.');">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_pedido" value="<?= (int) $p['id_pedido'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Eliminar pedido</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$pedidos): ?>
        <tr><td colspan="6">Sin pedidos aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
