<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conexion.php';

const WHATSAPP_NUMERO = '573001112233';
const APP_NOMBRE = 'VitaSoporte';
const APP_SLOGAN = 'Compresión consciente para tu bienestar diario.';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function formatearPrecio(float|int|string $precio): string
{
    return '$' . number_format((float) $precio, 0, ',', '.');
}

function whatsappUrl(string $mensaje): string
{
    return 'https://wa.me/' . WHATSAPP_NUMERO . '?text=' . rawurlencode($mensaje);
}

function mensajeWhatsAppProducto(array $producto): string
{
    $nombre = $producto['nombre'] ?? 'producto';
    $talla = $producto['talla'] ?? '';
    $precio = isset($producto['precio']) ? formatearPrecio($producto['precio']) : '';
    return "Hola, me interesa *{$nombre}* (talla {$talla}) de VitaSoporte. Precio: {$precio}. ¿Está disponible?";
}

function mensajeWhatsAppPedido(array $pedido, array $producto): string
{
    $cliente = $pedido['nombre_cliente'] ?? '';
    $tel = $pedido['telefono_cliente'] ?? '';
    $dir = $pedido['direccion_entrega'] ?? '';
    $prod = $producto['nombre'] ?? 'producto';
    $talla = $producto['talla'] ?? '';
    $precio = isset($producto['precio']) ? formatearPrecio($producto['precio']) : '';
    return "Hola, acabo de solicitar un pedido en VitaSoporte.\n"
        . "Cliente: {$cliente}\n"
        . "Tel: {$tel}\n"
        . "Dirección: {$dir}\n"
        . "Producto: {$prod} (talla {$talla}) — {$precio}";
}

function fotoProducto(?string $fotoUrl): string
{
    $foto = trim((string) $fotoUrl);
    if ($foto === '') {
        return 'img/hero.jpg';
    }
    if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://') || str_starts_with($foto, 'img/')) {
        return $foto;
    }
    return 'img/' . ltrim($foto, '/');
}

function requiereAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        flash('error', 'Debes iniciar sesión como administrador.');
        header('Location: login.php');
        exit;
    }
}

function adminActual(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return [
        'id_admin' => (int) $_SESSION['admin_id'],
        'nombre' => (string) ($_SESSION['admin_nombre'] ?? ''),
        'correo' => (string) ($_SESSION['admin_correo'] ?? ''),
    ];
}

function obtenerCategorias(mysqli $conexion): array
{
    $result = $conexion->query('SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC');
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtenerProducto(mysqli $conexion, int $id): ?array
{
    $stmt = $conexion->prepare(
        'SELECT p.*, c.nombre AS categoria_nombre
         FROM producto p
         INNER JOIN categoria c ON c.id_categoria = p.id_categoria
         WHERE p.id_producto = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
