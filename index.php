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
        <p class="hero-lead"><?= e(APP_SLOGAN) ?> Elige tus prendas y tallas, agrégalas a la bolsa y coordina el pago por WhatsApp.</p>
        <div class="hero-cta">
            <a class="btn btn-light" href="catalogo.php">Ver catálogo</a>
            <a class="btn btn-hero-ghost" href="bolsa.php">Ver mi bolsa</a>
        </div>
    </div>
</section>

<main class="container">
<section class="section">
    <div class="section-head">
        <h2>Elige por función</h2>
        <p>Cuatro líneas de soporte. Arma tu bolsa con varios productos y pide una sola vez.</p>
    </div>
    <div class="funciones">
        <?php foreach ($categorias as $cat): ?>
            <?php $nombre = $cat['nombre']; ?>
            <a class="funcion" href="catalogo.php?categoria=<?= (int)$cat['id_categoria'] ?>">
                <img class="funcion-img" src="<?= e(imagenCategoria($cat['imagen'] ?? null)) ?>" alt="<?= e($nombre) ?>" loading="lazy">
                <h3><?= e($nombre) ?></h3>
                <p><?= e($descripciones[$nombre] ?? 'Soporte y compresión wellness.') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Destacados</h2>
        <p>Prendas con mejor presencia en el catálogo. Elige talla, agrega a la bolsa y confirma por WhatsApp.</p>
        <p class="step-hint">
            <span>1 · Catálogo</span>
            <span>2 · Bolsa</span>
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
                    <p class="meta"><?= e($p['categoria_nombre']) ?></p>
                    <p class="precio"><?= e(formatearPrecio($p['precio'])) ?></p>
                    <form class="talla-form" method="post" action="bolsa.php">
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                        <input type="hidden" name="volver" value="index.php">
                        <label class="talla-label">Talla
                            <span class="talla-opciones" role="radiogroup" aria-label="Talla de <?= e($p['nombre']) ?>">
                                <?php foreach (tallasDeProducto($p) as $iT => $t): ?>
                                    <label class="talla-chip">
                                        <input type="radio" name="talla" value="<?= e($t) ?>" <?= $iT === 0 ? 'checked' : '' ?>>
                                        <span><?= e($t) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </span>
                        </label>
                        <div class="product-actions">
                            <button class="btn btn-primary btn-sm" type="submit">Agregar a la bolsa</button>
                            <a class="btn btn-ghost btn-sm" href="catalogo.php">Ver más</a>
                        </div>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section ubicacion">
    <div class="section-head">
        <h2>Visítanos en tienda</h2>
        <p>También puedes comprar y recoger directamente en nuestra tienda física.</p>
    </div>
    <div class="ubicacion-card">
        <div>
            <strong><?= e(TIENDA_UBICACION) ?></strong>
            <p>Bogotá · Pregunta por VitaSoporte en Punto Once. Te atendemos también por WhatsApp para confirmar tallas y disponibilidad.</p>
        </div>
        <div class="ubicacion-acciones">
            <a class="btn btn-primary" href="<?= e(TIENDA_MAPS_URL) ?>" target="_blank" rel="noopener">Cómo llegar</a>
            <a class="btn btn-wa" href="<?= e(whatsappUrl('Hola VitaSoporte, quiero información de la tienda en ' . TIENDA_UBICACION . '.')) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
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
