<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Panel';
$paginaAdmin = 'panel';
$totales = [
    'categorias' => (int)$conexion->query('SELECT COUNT(*) c FROM categoria')->fetch_assoc()['c'],
    'productos' => (int)$conexion->query('SELECT COUNT(*) c FROM producto')->fetch_assoc()['c'],
    'pedidos' => (int)$conexion->query('SELECT COUNT(*) c FROM pedido')->fetch_assoc()['c'],
];

$recientes = $conexion->query(
    'SELECT pe.fecha, pe.nombre_cliente, pe.total,
            GROUP_CONCAT(CONCAT(pr.nombre, " (", d.talla, ") x", d.cantidad) SEPARATOR ", ") AS productos
     FROM pedido pe
     JOIN pedido_detalle d ON d.id_pedido = pe.id_pedido
     JOIN producto pr ON pr.id_producto = d.id_producto
     GROUP BY pe.id_pedido
     ORDER BY pe.fecha DESC
     LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../includes/header_admin.php';
?>
<section class="toolbar">
    <div>
        <h2>Panel</h2>
        <p class="meta">Resumen rápido de catálogo y pedidos.</p>
    </div>
</section>
<div class="metricas">
    <div class="metrica"><div class="label">Funciones</div><div class="valor"><?= $totales['categorias'] ?></div></div>
    <div class="metrica"><div class="label">Productos</div><div class="valor"><?= $totales['productos'] ?></div></div>
    <div class="metrica"><div class="label">Pedidos</div><div class="valor"><?= $totales['pedidos'] ?></div></div>
</div>
<p class="panel-actions">
    <a class="btn btn-primary" href="productos.php">Productos</a>
    <a class="btn btn-ghost" href="pedidos.php">Pedidos</a>
    <a class="btn btn-ghost" href="categorias.php">Categorías</a>
</p>

<div class="toolbar"><h3>Últimos pedidos</h3></div>
<div class="tabla-wrap">
<table>
    <thead><tr><th>Fecha</th><th>Cliente</th><th>Productos</th><th>Total</th></tr></thead>
    <tbody>
    <?php foreach ($recientes as $r): ?>
        <tr>
            <td><?= e($r['fecha']) ?></td>
            <td><?= e($r['nombre_cliente']) ?></td>
            <td><?= e($r['productos']) ?></td>
            <td><?= e(formatearPrecio($r['total'])) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$recientes): ?>
        <tr><td colspan="4">Sin pedidos aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
