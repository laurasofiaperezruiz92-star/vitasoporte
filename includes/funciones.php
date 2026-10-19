<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conexion.php';

const WHATSAPP_NUMERO = '573105701412';
const APP_NOMBRE = 'VitaSoporte';
const APP_SLOGAN = 'Compresión consciente para tu bienestar diario.';
const TIENDA_UBICACION = 'Centro Comercial San Victorino · Punto Once';
const TIENDA_MAPS_URL = 'https://www.google.com/maps/search/?api=1&query=' . 'Centro+Comercial+San+Victorino+Punto+Once+Bogot%C3%A1';

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
    return "Hola, me interesa *{$nombre}* (tallas disponibles: {$talla}) de VitaSoporte. Precio: {$precio}. ¿Está disponible?";
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
    $result = $conexion->query('SELECT id_categoria, nombre, imagen FROM categoria ORDER BY nombre ASC');
    return $result->fetch_all(MYSQLI_ASSOC);
}

function imagenCategoria(?string $imagen): string
{
    $foto = trim((string) $imagen);
    if ($foto === '') {
        return 'img/hero.jpg';
    }
    if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://') || str_starts_with($foto, 'img/')) {
        return $foto;
    }
    return 'img/' . ltrim($foto, '/');
}

/** Tallas disponibles de un producto: "S,M,L,XL" -> ['S','M','L','XL']. */
function tallasDeProducto(array $producto): array
{
    $tallas = array_filter(array_map('trim', explode(',', (string) ($producto['talla'] ?? 'Única'))));
    return $tallas ?: ['Única'];
}

/* ——— Bolsa de compras en sesión: clave "id|talla" => cantidad ——— */

function bolsaItems(): array
{
    $bolsa = $_SESSION['bolsa'] ?? [];
    if (!is_array($bolsa)) {
        return [];
    }
    $limpia = [];
    foreach ($bolsa as $clave => $cantidad) {
        $partes = explode('|', (string) $clave, 2);
        $id = (int) ($partes[0] ?? 0);
        $talla = trim((string) ($partes[1] ?? 'Única'));
        $cantidad = (int) $cantidad;
        if ($id > 0 && $cantidad > 0) {
            $limpia[$id . '|' . ($talla === '' ? 'Única' : $talla)] = min($cantidad, 99);
        }
    }
    return $limpia;
}

function bolsaCantidadTotal(): int
{
    return array_sum(bolsaItems());
}

function bolsaAgregar(int $idProducto, string $talla, int $cantidad = 1): void
{
    if ($idProducto <= 0) {
        return;
    }
    $bolsa = bolsaItems();
    $clave = $idProducto . '|' . ($talla === '' ? 'Única' : $talla);
    $bolsa[$clave] = min(($bolsa[$clave] ?? 0) + max(1, $cantidad), 99);
    $_SESSION['bolsa'] = $bolsa;
}

function bolsaFijar(string $clave, int $cantidad): void
{
    $bolsa = bolsaItems();
    if ($cantidad <= 0) {
        unset($bolsa[$clave]);
    } else {
        $bolsa[$clave] = min($cantidad, 99);
    }
    $_SESSION['bolsa'] = $bolsa;
}

function bolsaVaciar(): void
{
    unset($_SESSION['bolsa']);
}

/**
 * Ítems de la bolsa con datos actuales de la BD.
 * Devuelve ['items' => [...con talla, cantidad y subtotal...], 'total' => float].
 */
function bolsaDetalle(mysqli $conexion): array
{
    $bolsa = bolsaItems();
    if (!$bolsa) {
        return ['items' => [], 'total' => 0.0];
    }

    $items = [];
    $total = 0.0;
    $vigente = [];
    foreach ($bolsa as $clave => $cantidad) {
        [$id, $talla] = array_pad(explode('|', $clave, 2), 2, 'Única');
        $producto = obtenerProducto($conexion, (int) $id);
        if (!$producto || !(int) $producto['disponible']) {
            continue;
        }
        $subtotal = (float) $producto['precio'] * $cantidad;
        $items[] = [
            'clave' => $clave,
            'id_producto' => (int) $id,
            'nombre' => $producto['nombre'],
            'categoria_nombre' => $producto['categoria_nombre'] ?? '',
            'foto_url' => $producto['foto_url'] ?? null,
            'precio' => (float) $producto['precio'],
            'talla' => $talla,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
        $vigente[$clave] = $cantidad;
    }
    $_SESSION['bolsa'] = $vigente;

    return ['items' => $items, 'total' => $total];
}

function mensajeWhatsAppBolsa(array $pedido, array $items, float $total): string
{
    $lineas = [
        'Hola, acabo de solicitar un pedido en VitaSoporte.',
        'Cliente: ' . ($pedido['nombre_cliente'] ?? ''),
        'Tel: ' . ($pedido['telefono_cliente'] ?? ''),
        'Dirección: ' . ($pedido['direccion_entrega'] ?? ''),
        'Productos:',
    ];
    foreach ($items as $it) {
        $lineas[] = "- {$it['nombre']} (talla {$it['talla']}) x{$it['cantidad']} — " . formatearPrecio($it['subtotal']);
    }
    $lineas[] = 'Total: ' . formatearPrecio($total);
    $lineas[] = 'Tienda: ' . TIENDA_UBICACION;
    return implode("\n", $lineas);
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
