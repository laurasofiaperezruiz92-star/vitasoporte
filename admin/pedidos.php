<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Pedidos';
$paginaAdmin = 'pedidos';

$pedidos = $conexion->query(
    'SELECT pe.*, pr.nombre AS producto, pr.talla, pr.precio
     FROM pedido pe
     JOIN producto pr ON pr.id_producto = pe.id_producto
     ORDER BY pe.fecha DESC'
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="toolbar"><h2>Bandeja de pedidos</h2></div>

<div class="tabla-wrap">
<table>
    <thead>
        <tr><th>Fecha</th><th>Cliente</th><th>Teléfono</th><th>Dirección</th><th>Producto</th><th>Precio</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pedidos as $p): ?>
        <tr>
            <td><?= e($p['fecha']) ?></td>
            <td><?= e($p['nombre_cliente']) ?></td>
            <td><?= e($p['telefono_cliente']) ?></td>
            <td><?= e($p['direccion_entrega']) ?></td>
            <td><?= e($p['producto']) ?> (<?= e($p['talla']) ?>)</td>
            <td><?= e(formatearPrecio($p['precio'])) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$pedidos): ?>
        <tr><td colspan="6">Sin pedidos aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
