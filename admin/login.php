<?php
require_once __DIR__ . '/../includes/funciones.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('index.php');
}

$error = '';
$flash = getFlash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';
    if ($correo === '' || $clave === '') {
        $error = 'Correo y contraseña obligatorios.';
    } else {
        $stmt = $conexion->prepare('SELECT id_admin, nombre, correo, password_hash FROM administrador WHERE correo = ? LIMIT 1');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        if (!$admin || !password_verify($clave, $admin['password_hash'])) {
            $error = 'Credenciales incorrectas.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id_admin'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_correo'] = $admin['correo'];
            flash('ok', 'Sesión iniciada.');
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#302722">
    <title>Login · VitaSoporte</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="login-page">
<div class="login-box">
    <h1>VitaSoporte</h1>
    <p class="sub">Panel de administración</p>
    <?php if ($flash): ?>
        <?php $claseFlash = $flash['tipo'] === 'ok' ? 'alert-ok' : ($flash['tipo'] === 'error' ? 'alert-error' : 'alert-info'); ?>
        <div class="alert <?= e($claseFlash) ?>"><?= e($flash['mensaje']) ?></div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form login-form">
        <label>Correo <input type="email" name="correo" required value="<?= e($_POST['correo'] ?? 'vitasoporte4@gmail.com') ?>"></label>
        <label>Contraseña <input type="password" name="clave" required value="<?= $_SERVER['REQUEST_METHOD'] === 'POST' ? '' : 'vitasoporte2008' ?>"></label>
        <button class="btn btn-primary" type="submit">Ingresar</button>
        <p class="meta">Acceso: vitasoporte4@gmail.com / vitasoporte2008</p>
        <p><a href="../index.php">← Volver al sitio</a></p>
    </form>
</div>
</body>
</html>
