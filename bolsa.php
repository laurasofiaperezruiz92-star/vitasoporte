<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Bolsa de compras';
$paginaActual = 'bolsa';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $idProducto = (int) ($_POST['id_producto'] ?? 0);
        $talla = trim((string) ($_POST['talla'] ?? ''));
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
        $producto = $idProducto ? obtenerProducto($conexion, $idProducto) : null;

        if (!$producto || !(int) $producto['disponible']) {
            flash('error', 'Ese producto ya no está disponible.');
        } else {
            $tallas = tallasDeProducto($producto);
            if (!in_array($talla, $tallas, true)) {
                $talla = $tallas[0];
            }
            bolsaAgregar($idProducto, $talla, $cantidad);
            flash('ok', 'Agregado a tu bolsa: ' . $producto['nombre'] . ' (talla ' . $talla . ').');
        }

        if (($_POST['destino'] ?? '') === 'bolsa') {
            redirect('bolsa.php');
        }
        $volver = (string) ($_POST['volver'] ?? 'catalogo.php');
        if (!preg_match('#^(catalogo|index)\.php(\?[a-zA-Z0-9=&_%-]*)?$#', $volver)) {
            $volver = 'catalogo.php';
        }
        redirect($volver);
    }

    if ($accion === 'actualizar') {
        $clave = (string) ($_POST['clave'] ?? '');
        bolsaFijar($clave, (int) ($_POST['cantidad'] ?? 1));
        flash('ok', 'Bolsa actualizada.');
        redirect('bolsa.php');
    }

    if ($accion === 'quitar') {
        bolsaFijar((string) ($_POST['clave'] ?? ''), 0);
        flash('ok', 'Producto quitado de la bolsa.');
        redirect('bolsa.php');
    }

    if ($accion === 'vaciar') {
        bolsaVaciar();
        flash('ok', 'Tu bolsa quedó vacía.');
        redirect('catalogo.php');
    }
}

$detalle = bolsaDetalle($conexion);
$items = $detalle['items'];
$total = $detalle['total'];

$disponibles = $conexion->query(
    'SELECT p.*, c.nombre AS categoria_nombre FROM producto p
     JOIN categoria c ON c.id_categoria = p.id_categoria
     WHERE p.disponible = 1 ORDER BY p.nombre'
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/includes/header.php';
?>
<section class="toolbar">
    <div>
        <h2>🛍️ Tu bolsa de compras</h2>
        <p class="meta">Esto es lo que agregaste. Ajusta la cantidad que deseas adquirir de cada prenda.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="catalogo.php">← Seguir en el catálogo</a>
</section>

<?php if (!$items): ?>
    <p class="empty-state">Tu bolsa está vacía. <a href="catalogo.php">Ver el catálogo</a> y agrega tus prendas.</p>
<?php else: ?>
    <div class="bolsa-lista">
        <?php foreach ($items as $it): ?>
            <form class="bolsa-item" method="post">
                <input type="hidden" name="clave" value="<?= e($it['clave']) ?>">
                <img src="<?= e(fotoProducto($it['foto_url'])) ?>" alt="<?= e($it['nombre']) ?>">
                <div class="bolsa-info">
                    <strong><?= e($it['nombre']) ?></strong>
                    <span><?= e($it['categoria_nombre']) ?> · Talla <strong><?= e($it['talla']) ?></strong></span>
                    <span><?= e(formatearPrecio($it['precio'])) ?> c/u</span>
                    <span class="bolsa-subtotal">= <?= e(formatearPrecio($it['subtotal'])) ?></span>
                </div>
                <label class="bolsa-cantidad">Cantidad
                    <input type="number" name="cantidad" value="<?= (int) $it['cantidad'] ?>" min="0" max="99" inputmode="numeric">
                </label>
                <div class="bolsa-acciones">
                    <button class="btn btn-primary btn-sm" type="submit" name="accion" value="actualizar">Actualizar</button>
                    <button class="btn btn-ghost btn-sm" type="submit" name="accion" value="quitar">Quitar</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <div class="bolsa-total">
        <span>Total</span>
        <strong><?= e(formatearPrecio($total)) ?></strong>
    </div>

    <div class="form-actions">
        <a class="btn btn-primary" href="pedido.php">Confirmar pedido (<?= bolsaCantidadTotal() ?> ítem<?= bolsaCantidadTotal() === 1 ? '' : 's' ?>)</a>
        <form method="post" onsubmit="return confirm('¿Vaciar toda la bolsa?');">
            <button class="btn btn-ghost" type="submit" name="accion" value="vaciar">Vaciar bolsa</button>
        </form>
    </div>
<?php endif; ?>

<?php if ($disponibles): ?>
    <section class="section" style="margin-top:2rem;">
        <div class="section-head">
            <h2>Agregar otra prenda</h2>
            <p>Elige producto, talla y cantidad sin salir de tu bolsa.</p>
        </div>
        <form class="form" method="post" style="max-width:560px;">
            <input type="hidden" name="accion" value="agregar">
            <input type="hidden" name="destino" value="bolsa">
            <label>Producto
                <select name="id_producto" id="sel-otro" required>
                    <option value="">Seleccionar…</option>
                    <?php foreach ($disponibles as $p): ?>
                        <option value="<?= (int) $p['id_producto'] ?>" data-tallas="<?= e($p['talla']) ?>">
                            <?= e($p['nombre']) ?> · <?= e(formatearPrecio($p['precio'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Talla
                <select name="talla" id="sel-talla" required></select>
            </label>
            <label>Cantidad
                <input type="number" name="cantidad" value="1" min="1" max="99">
            </label>
            <button class="btn btn-primary" type="submit">Agregar a la bolsa</button>
        </form>
    </section>
    <script>
      const selProd = document.getElementById('sel-otro');
      const selTalla = document.getElementById('sel-talla');
      function llenarTallas() {
        const opt = selProd.options[selProd.selectedIndex];
        const tallas = (opt?.dataset.tallas || 'Única').split(',').map(t => t.trim()).filter(Boolean);
        selTalla.innerHTML = tallas.map(t => `<option value="${t}">${t}</option>`).join('');
      }
      selProd?.addEventListener('change', llenarTallas);
      llenarTallas();
    </script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
