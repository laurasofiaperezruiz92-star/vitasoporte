<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Catálogo';
$paginaActual = 'catalogo';
$categorias = obtenerCategorias($conexion);

$idCat = (int)($_GET['categoria'] ?? 0);
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT p.*, c.nombre AS categoria_nombre
        FROM producto p
        JOIN categoria c ON c.id_categoria = p.id_categoria
        WHERE p.disponible = 1';
$types = '';
$params = [];
if ($idCat > 0) {
    $sql .= ' AND p.id_categoria = ?';
    $types .= 'i';
    $params[] = $idCat;
}
if ($q !== '') {
    $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ?)';
    $types .= 'ss';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY p.nombre';

$stmt = $conexion->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$catNombre = '';
if ($idCat > 0) {
    foreach ($categorias as $c) {
        if ((int)$c['id_categoria'] === $idCat) {
            $catNombre = $c['nombre'];
            break;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="toolbar">
    <div>
        <h2>Catálogo</h2>
        <?php if ($catNombre): ?>
            <span class="filtro-activo">Función: <?= e($catNombre) ?></span>
        <?php else: ?>
            <p class="meta">Elige un producto y continúa al pedido. Un artículo por vez.</p>
        <?php endif; ?>
    </div>
</section>

<form class="search-bar" method="get">
    <input type="search" name="q" placeholder="Buscar por nombre o descripción…" value="<?= e($q) ?>">
    <select name="categoria" aria-label="Filtrar por función">
        <option value="0">Todas las funciones</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id_categoria'] ?>" <?= $idCat === (int)$c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit">Filtrar</button>
</form>

<div class="grid">
<?php foreach ($productos as $i => $p): ?>
    <article class="product" style="animation-delay: <?= min(0.4, 0.04 * $i) ?>s">
        <div class="product-media">
            <img src="<?= e(fotoProducto($p['foto_url'])) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
        </div>
        <div class="product-body">
            <h3><?= e($p['nombre']) ?></h3>
            <p class="meta"><?= e($p['categoria_nombre']) ?> · Talla <?= e($p['talla']) ?></p>
            <p class="precio"><?= e(formatearPrecio($p['precio'])) ?></p>
            <p class="meta"><?= e(mb_strimwidth((string)$p['descripcion'], 0, 95, '…')) ?></p>
            <div class="product-actions">
                <a class="btn btn-primary btn-sm" href="pedido.php?producto=<?= (int)$p['id_producto'] ?>">Pedir este</a>
                <a class="btn btn-wa btn-sm" href="<?= e(whatsappUrl(mensajeWhatsAppProducto($p))) ?>" target="_blank" rel="noopener">WhatsApp</a>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$productos): ?>
    <p class="empty-state">No hay productos disponibles con ese filtro. <a href="catalogo.php">Ver todo</a></p>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
