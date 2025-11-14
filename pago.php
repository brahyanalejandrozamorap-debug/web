<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Obtener productos del carrito
$productos = isset($_SESSION['id_carrito']['producto']) ? $_SESSION['id_carrito']['producto'] : null;

if (!$productos) {
    header("Location: index.php");
    exit;
}

$lista_carrito = [];

foreach ($productos as $clave => $item) {
    // $item = ['cantidad' => X]

    $parts = explode('_', $clave);
    $id_producto = $parts[0];
    $talla = $parts[1] ?? '-';

    $sql = $con->prepare("SELECT id_producto, nombre, precio, descuento 
                          FROM producto 
                          WHERE id_producto=? AND activo=1");
    $sql->execute([$id_producto]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $row['cantidad'] = isset($item['cantidad']) ? (int) $item['cantidad'] : 1;
        $row['talla'] = $talla;
        $lista_carrito[] = $row;
    }
}


// Calcular total
$total = 0;
foreach ($lista_carrito as $p) {
    $precio_desc = $p['precio'] - (($p['precio'] * $p['descuento']) / 100);
    $cantidad = isset($p['cantidad']) ? (int) $p['cantidad'] : 1;
    $subtotal = $cantidad * $precio_desc;
    $total += $subtotal;
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Tienda de Ropa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader"
                aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarHeader">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="catalogo.php">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
                </ul>

                <!-- Menú derecho -->
                <ul class="navbar-nav ms-auto d-flex align-items-center">

                    <?php if (isset($_SESSION['id_cliente'])): ?>
                        <li class="nav-item me-2">
                            <span class="navbar-text text-white">
                                Hola, <?php echo $_SESSION['nombre_cliente']; ?>
                            </span>
                        </li>

                        <li class="nav-item me-2">
                            <a href="loginout.php" class="btn btn-secondary btn-sm">Salir</a>
                        </li>

                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a href="login.php" class="btn btn-black btn-sm">👤</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a href="checkout.php" class="btn btn-danger btn-sm">
                            Volver al 🛒</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Resumen de Pago</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>

                <?php foreach ($lista_carrito as $p):
                    $precio_desc = $p['precio'] - (($p['precio'] * $p['descuento']) / 100);
                    $cantidad = $p['cantidad'];
                    $subtotal = $cantidad * $precio_desc;
                    ?>
                    <tr>
                        <td><?php echo $p['nombre']; ?></td>
                        <td><?php echo $p['talla']; ?></td>
                        <td><?php echo $cantidad; ?></td>
                        <td><?php echo MONEDA . number_format($subtotal, 2); ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="3" class="text-end"><b>Total:</b></td>
                    <td><?php echo MONEDA . number_format($total, 2); ?></td>
                </tr>
            </table>


        </div>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-3">
                <button class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalEnvio">
                    Confirmar Pago
                </button>

            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>


            <!-- MODAL DATOS DE ENVÍO -->
            <div class="modal fade" id="modalEnvio" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title">Datos de Envío - Colombia</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <form action="procesar_pago.php" method="POST">

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" class="form-control" name="ciudad" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Número de identificación (CC)</label>
                                    <input type="number" class="form-control" name="cedula" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Celular</label>
                                    <input type="number" class="form-control" name="celular" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Departamento</label>
                                    <select class="form-select" name="departamento" required>
                                        <option value="">Seleccione...</option>
                                        <option>Amazonas</option>
                                        <option>Antioquia</option>
                                        <option>Arauca</option>
                                        <option>Atlántico</option>
                                        <option>Bolívar</option>
                                        <option>Boyacá</option>
                                        <option>Caldas</option>
                                        <option>Caquetá</option>
                                        <option>Casanare</option>
                                        <option>Cauca</option>
                                        <option>Cesar</option>
                                        <option>Chocó</option>
                                        <option>Córdoba</option>
                                        <option>Cundinamarca</option>
                                        <option>Guainía</option>
                                        <option>Guaviare</option>
                                        <option>Huila</option>
                                        <option>La Guajira</option>
                                        <option>Magdalena</option>
                                        <option>Meta</option>
                                        <option>Nariño</option>
                                        <option>Norte de Santander</option>
                                        <option>Putumayo</option>
                                        <option>Quindío</option>
                                        <option>Risaralda</option>
                                        <option>San Andrés</option>
                                        <option>Santander</option>
                                        <option>Sucre</option>
                                        <option>Tolima</option>
                                        <option>Valle del Cauca</option>
                                        <option>Vaupés</option>
                                        <option>Vichada</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" name="ciudad" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Dirección completa</label>
                                    <input type="text" class="form-control" name="direccion" required>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-bold">Método de pago</label>
                                    <select class="form-select" name="metodo_pago" required>
                                        <option value="">Seleccione...</option>
                                        <option value="contraentrega">Pago contraentrega</option>
                                        <option value="nequi">Nequi</option>
                                        <option value="daviplata">Daviplata</option>
                                        <option value="transferencia">Transferencia Bancaria</option>
                                    </select>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success w-100">Finalizar Pedido</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>


</body>

</html>