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
    'SELECT pe.fecha, pe.nombre_cliente, pr.nombre AS producto
     FROM pedido pe
     JOIN producto pr ON pr.id_producto = pe.id_producto
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
    <thead><tr><th>Fecha</th><th>Cliente</th><th>Producto</th></tr></thead>
    <tbody>
    <?php foreach ($recientes as $r): ?>
        <tr>
            <td><?= e($r['fecha']) ?></td>
            <td><?= e($r['nombre_cliente']) ?></td>
            <td><?= e($r['producto']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$recientes): ?>
        <tr><td colspan="3">Sin pedidos aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
