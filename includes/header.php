<?php
declare(strict_types=1);
if (!defined('APP_NOMBRE')) {
    require_once __DIR__ . '/funciones.php';
}
$paginaActual = $paginaActual ?? '';
$fullBleed = !empty($fullBleed);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($titulo ?? APP_NOMBRE) ?> · <?= e(APP_NOMBRE) ?></title>
    <meta name="description" content="<?= e(APP_SLOGAN) ?>">
    <meta name="theme-color" content="#6b2340">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-bar">
        <a class="brand" href="index.php">
            <div class="brand-mark"><span>V</span></div>
            <div class="brand-text">
                <strong>VitaSoporte</strong>
                <small>Wellness &amp; compresión</small>
            </div>
        </a>
        <input type="checkbox" id="nav-check" aria-hidden="true">
        <label class="nav-toggle" for="nav-check" aria-label="Abrir menú">
            <span></span><span></span><span></span>
        </label>
        <nav class="nav-links" aria-label="Principal">
            <a href="index.php" class="<?= $paginaActual === 'inicio' ? 'is-active' : '' ?>">Inicio</a>
            <a href="catalogo.php" class="<?= $paginaActual === 'catalogo' ? 'is-active' : '' ?>">Catálogo</a>
            <a href="pedido.php" class="<?= $paginaActual === 'pedido' ? 'is-active' : '' ?>">Pedir</a>
            <a href="admin/login.php">Admin</a>
        </nav>
    </div>
</header>
<?php if (!$fullBleed): ?><main class="container"><?php endif; ?>
<?php
$flash = getFlash();
if ($flash && !$fullBleed):
    $clase = $flash['tipo'] === 'ok' ? 'alert-ok' : ($flash['tipo'] === 'error' ? 'alert-error' : 'alert-info');
?>
    <div class="alert <?= e($clase) ?>"><?= e($flash['mensaje']) ?></div>
<?php endif; ?>
