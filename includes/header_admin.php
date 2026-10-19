<?php
declare(strict_types=1);
if (!defined('APP_NOMBRE')) {
    require_once __DIR__ . '/funciones.php';
}
$paginaAdmin = $paginaAdmin ?? '';
$admin = adminActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($titulo ?? 'Panel') ?> · Admin VitaSoporte</title>
    <meta name="theme-color" content="#3f1226">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="container nav-bar">
        <a class="brand brand-admin" href="index.php">
            <div class="brand-mark"><span>V</span></div>
            <div class="brand-text">
                <strong>Panel VitaSoporte</strong>
                <small><?= e($admin['nombre'] ?? 'Admin') ?></small>
            </div>
        </a>
        <input type="checkbox" id="nav-check" aria-hidden="true">
        <label class="nav-toggle" for="nav-check" aria-label="Abrir menú">
            <span></span><span></span><span></span>
        </label>
        <nav class="nav-links admin-nav" aria-label="Admin">
            <a href="index.php" class="<?= $paginaAdmin === 'panel' ? 'is-active' : '' ?>">Panel</a>
            <a href="categorias.php" class="<?= $paginaAdmin === 'categorias' ? 'is-active' : '' ?>">Categorías</a>
            <a href="productos.php" class="<?= $paginaAdmin === 'productos' ? 'is-active' : '' ?>">Productos</a>
            <a href="pedidos.php" class="<?= $paginaAdmin === 'pedidos' ? 'is-active' : '' ?>">Pedidos</a>
            <a href="logout.php" class="nav-salir">Salir</a>
        </nav>
    </div>
</header>
<main class="container">
<?php
$flash = getFlash();
if ($flash):
    $clase = $flash['tipo'] === 'ok' ? 'alert-ok' : ($flash['tipo'] === 'error' ? 'alert-error' : 'alert-info');
?>
    <div class="alert <?= e($clase) ?>"><?= e($flash['mensaje']) ?></div>
<?php endif; ?>
