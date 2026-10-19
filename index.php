<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Inicio';
$paginaActual = 'inicio';
$fullBleed = true;
$categorias = obtenerCategorias($conexion);

$descripciones = [
    'Postura' => 'Alineación y soporte lumbar para el día a día.',
    'Circulación' => 'Compresión graduada para piernas activas.',
    'Deportivas' => 'Estabilidad ligera en entrenamiento.',
    'Postparto' => 'Recuperación suave y cómoda.',
];

$destacados = $conexion->query(
    "SELECT p.*, c.nombre AS categoria_nombre
     FROM producto p
     JOIN categoria c ON c.id_categoria = p.id_categoria
     WHERE p.disponible = 1
     ORDER BY p.precio DESC
     LIMIT 4"
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<section class="hero-bleed" aria-label="Presentación VitaSoporte">
    <div class="hero-inner">
        <h1 class="hero-brand">VitaSoporte</h1>
        <p class="hero-lead"><?= e(APP_SLOGAN) ?> Elige una prenda, pide en un paso y coordina el pago por WhatsApp.</p>
        <div class="hero-cta">
            <a class="btn btn-light" href="catalogo.php">Ver catálogo</a>
            <a class="btn btn-hero-ghost" href="pedido.php">Hacer un pedido</a>
        </div>
    </div>
</section>

<main class="container">
<section class="section">
    <div class="section-head">
        <h2>Elige por función</h2>
        <p>Cuatro líneas de soporte. Un producto por pedido, sin carrito.</p>
    </div>
    <div class="funciones">
        <?php foreach ($categorias as $cat): ?>
            <?php $nombre = $cat['nombre']; ?>
            <a class="funcion" href="catalogo.php?categoria=<?= (int)$cat['id_categoria'] ?>">
                <h3><?= e($nombre) ?></h3>
                <p><?= e($descripciones[$nombre] ?? 'Soporte y compresión wellness.') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Destacados</h2>
        <p>Prendas con mejor presencia en el catálogo. Pedido individual y confirmación por WhatsApp.</p>
        <p class="step-hint">
            <span>1 · Catálogo</span>
            <span>2 · Un producto</span>
            <span>3 · WhatsApp</span>
        </p>
    </div>
    <div class="grid">
        <?php foreach ($destacados as $i => $p): ?>
            <article class="product" style="animation-delay: <?= 0.05 * $i ?>s">
                <div class="product-media">
                    <img src="<?= e(fotoProducto($p['foto_url'])) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
                </div>
                <div class="product-body">
                    <h3><?= e($p['nombre']) ?></h3>
                    <p class="meta"><?= e($p['categoria_nombre']) ?> · Talla <?= e($p['talla']) ?></p>
                    <p class="precio"><?= e(formatearPrecio($p['precio'])) ?></p>
                    <div class="product-actions">
                        <a class="btn btn-primary btn-sm" href="pedido.php?producto=<?= (int)$p['id_producto'] ?>">Pedir</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="lifestyle-strip section">
    <img src="img/lifestyle-movimiento.jpg" alt="Movimiento y bienestar con prendas de soporte" loading="lazy">
    <div class="lifestyle-copy">
        <h2>Bienestar que se siente</h2>
        <p>Diseño discreto, telas transpirables y compresión pensada para tu rutina: oficina, deporte o recuperación.</p>
        <a class="btn btn-primary" href="catalogo.php">Explorar productos</a>
    </div>
</section>
</main>
<?php
$fullBleed = true; // el main ya se cerró arriba
require __DIR__ . '/includes/footer.php';
?>
