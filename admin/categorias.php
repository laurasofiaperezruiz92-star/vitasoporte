<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo = 'Categorías';
$paginaAdmin = 'categorias';
$error = '';

/**
 * Guarda la foto subida de una categoría en img/categorias/.
 * Devuelve la ruta (img/categorias/xxx) o null si no se subió archivo.
 * Lanza RuntimeException con el mensaje de error si el archivo no es válido.
 */
function subirImagenCategoria(): ?string
{
    if (empty($_FILES['imagen']) || ($_FILES['imagen']['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la imagen. Intenta de nuevo.');
    }
    if (($_FILES['imagen']['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('La imagen no puede superar 2 MB.');
    }

    $permitidas = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['imagen']['tmp_name']);
    if (!isset($permitidas[$tipo])) {
        throw new RuntimeException('Solo se aceptan imágenes JPG, PNG o WebP.');
    }

    $carpeta = __DIR__ . '/../img/categorias';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }
    $nombreArchivo = 'cat-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $permitidas[$tipo];
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
        throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
    }
    return 'img/categorias/' . $nombreArchivo;
}

function borrarArchivoImagen(?string $ruta): void
{
    $ruta = trim((string) $ruta);
    if (str_starts_with($ruta, 'img/categorias/')) {
        $archivo = __DIR__ . '/../' . $ruta;
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $id = (int) ($_POST['id_categoria'] ?? 0);

    if ($accion === 'crear' || $accion === 'editar') {
        if ($nombre === '') {
            $error = 'El nombre es obligatorio.';
        } else {
            try {
                $nuevaImagen = subirImagenCategoria();
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
                $nuevaImagen = null;
            }

            if ($error === '') {
                $imagenFinal = $nuevaImagen;
                $actual = null;
                if ($accion === 'editar' && $id > 0) {
                    $stmt = $conexion->prepare('SELECT imagen FROM categoria WHERE id_categoria = ?');
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $actual = $stmt->get_result()->fetch_assoc();
                    $imagenFinal = $nuevaImagen ?? ($actual['imagen'] ?? null);

                    if (!empty($_POST['quitar_imagen'])) {
                        borrarArchivoImagen($actual['imagen'] ?? null);
                        $imagenFinal = null;
                    } elseif ($nuevaImagen !== null) {
                        borrarArchivoImagen($actual['imagen'] ?? null);
                    }
                }
                // Ruta escrita a mano solo si no se subió archivo ni se pidió quitar.
                if ($imagenFinal === null && $nuevaImagen === null && empty($_POST['quitar_imagen'])) {
                    $manual = trim($_POST['imagen_manual'] ?? '');
                    $imagenFinal = $manual !== '' ? $manual : ($actual['imagen'] ?? null);
                }

                if ($accion === 'crear') {
                    $stmt = $conexion->prepare('INSERT INTO categoria (nombre, imagen) VALUES (?, ?)');
                    $stmt->bind_param('ss', $nombre, $imagenFinal);
                    $stmt->execute();
                    flash('ok', 'Categoría creada.');
                } else {
                    $stmt = $conexion->prepare('UPDATE categoria SET nombre = ?, imagen = ? WHERE id_categoria = ?');
                    $stmt->bind_param('ssi', $nombre, $imagenFinal, $id);
                    $stmt->execute();
                    flash('ok', 'Categoría actualizada.');
                }
                redirect('categorias.php');
            }
        }
    }

    if ($accion === 'quitar_imagen' && $id > 0) {
        $stmt = $conexion->prepare('SELECT imagen FROM categoria WHERE id_categoria = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $cat = $stmt->get_result()->fetch_assoc();
        borrarArchivoImagen($cat['imagen'] ?? null);
        $stmt = $conexion->prepare('UPDATE categoria SET imagen = NULL WHERE id_categoria = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        flash('ok', 'Imagen de la categoría eliminada.');
        redirect('categorias.php');
    }

    if ($accion === 'eliminar') {
        try {
            $stmt = $conexion->prepare('DELETE FROM categoria WHERE id_categoria = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            flash('ok', 'Categoría eliminada.');
            redirect('categorias.php');
        } catch (Throwable $e) {
            $error = 'No se puede eliminar: tiene productos asociados.';
        }
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $stmt = $conexion->prepare('SELECT * FROM categoria WHERE id_categoria = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

$categorias = obtenerCategorias($conexion);
require __DIR__ . '/../includes/header_admin.php';
?>
<div class="toolbar"><h2>Gestión de categorías</h2></div>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form" style="margin-bottom:1.5rem">
    <h3><?= $editar ? 'Editar' : 'Nueva' ?> categoría</h3>
    <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
    <?php if ($editar): ?><input type="hidden" name="id_categoria" value="<?= (int) $editar['id_categoria'] ?>"><?php endif; ?>
    <label>Nombre <input name="nombre" required value="<?= e($editar['nombre'] ?? '') ?>"></label>

    <?php if (!empty($editar['imagen'])): ?>
        <p class="meta">Imagen actual:</p>
        <img class="thumb" src="../<?= e(imagenCategoria($editar['imagen'])) ?>" alt="" style="width:140px;height:100px;object-fit:cover;border-radius:12px">
        <label><input type="checkbox" name="quitar_imagen" value="1"> Quitar esta imagen al guardar</label>
    <?php endif; ?>

    <label>Subir imagen nueva (JPG, PNG o WebP, máx. 2 MB)
        <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
    </label>
    <label>…o ruta/URL de la imagen
        <input name="imagen_manual" placeholder="img/cat-postura.jpg" value="<?= e($editar['imagen'] ?? '') ?>">
    </label>
    <button class="btn btn-primary" type="submit">Guardar</button>
    <?php if ($editar): ?><a href="categorias.php">Cancelar</a><?php endif; ?>
</form>

<div class="tabla-wrap">
<table>
    <thead><tr><th>Imagen</th><th>Nombre</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($categorias as $c): ?>
        <tr>
            <td>
                <?php if (!empty($c['imagen'])): ?>
                    <img class="cat-thumb" src="../<?= e(imagenCategoria($c['imagen'])) ?>" alt="<?= e($c['nombre']) ?>">
                <?php else: ?>
                    <span class="cat-sin-img">Sin imagen</span>
                <?php endif; ?>
            </td>
            <td><?= e($c['nombre']) ?></td>
            <td class="acciones">
                <a class="btn btn-sm btn-primary" href="?editar=<?= (int) $c['id_categoria'] ?>">Editar / imagen</a>
                <?php if (!empty($c['imagen'])): ?>
                    <form method="post" onsubmit="return confirm('¿Quitar la imagen de esta categoría?')">
                        <input type="hidden" name="accion" value="quitar_imagen">
                        <input type="hidden" name="id_categoria" value="<?= (int) $c['id_categoria'] ?>">
                        <button class="btn btn-sm btn-ghost" type="submit">Quitar imagen</button>
                    </form>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('¿Eliminar la categoría?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_categoria" value="<?= (int) $c['id_categoria'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer_admin.php'; ?>
