<?php
session_start();
require_once '../config/config.php';

// Usuario debe estar logueado
if (!isset($_SESSION['id_cliente'])) {
    echo json_encode(['ok' => false, 'mensaje' => 'Debes iniciar sesión para agregar productos al carrito']);
    exit;
}

// Validaciones mínimas
if (!isset($_POST['id_producto']) || !isset($_POST['talla'])) {
    echo json_encode(['ok' => false, 'mensaje' => 'Producto o talla no válidos']);
    exit;
}

$id_producto = $_POST['id_producto'];
$talla = $_POST['talla'];
$token = $_POST['token'] ?? '';
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

// Validar token
$token_tmp = hash_hmac('sha1', $id_producto, KEY_TOKEN);
if ($token !== $token_tmp) {
    echo json_encode(['ok' => false, 'mensaje' => 'Token inválido']);
    exit;
}

// Inicializar carrito si no existe
if (!isset($_SESSION['id_carrito']['producto'])) {
    $_SESSION['id_carrito'] = ['producto' => []];
}

// Identificador único por producto+talla
$key = $id_producto . '_' . $talla;

// Si ya existe → actualizar cantidad
if (isset($_SESSION['id_carrito']['producto'][$key])) {

    // Si desde checkout viene cantidad exacta
    if (isset($_POST['cantidad'])) {
        $_SESSION['id_carrito']['producto'][$key]['cantidad'] = max(1, $cantidad);
    } else {
        // Desde details.php → suma 1
        $_SESSION['id_carrito']['producto'][$key]['cantidad'] += 1;
    }

} else {
    // Crear el ítem
    $_SESSION['id_carrito']['producto'][$key] = [
        'id_producto' => $id_producto,
        'talla'       => $talla,
        'cantidad'    => $cantidad
    ];
}

// Respuesta final
echo json_encode([
    'ok' => true,
    'numero' => count($_SESSION['id_carrito']['producto'])
]);
