<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
$db = new Database();
$con = $db->conectar();

/* ============================================================
    ELIMINAR PRODUCTO (AJAX)
============================================================ */
if (isset($_POST['eliminar']) && isset($_POST['clave'])) {

    $clave = $_POST['clave'];

    if (isset($_SESSION['id_carrito']['producto'][$clave])) {

        unset($_SESSION['id_carrito']['producto'][$clave]);

        // Recalcular total
        $productos = $_SESSION['id_carrito']['producto'] ?? [];
        $total = 0;

        foreach ($productos as $c => $item) {

            $parts = explode('_', $c);
            $id_producto = $parts[0];

            $sql = $con->prepare("SELECT precio, descuento FROM producto WHERE id_producto=? AND activo=1");
            $sql->execute([$id_producto]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $precio_desc = $row['precio'] - (($row['precio'] * $row['descuento']) / 100);
                $total += $item['cantidad'] * $precio_desc;
            }
        }

        // Contar items reales
        $num_items = 0;
        foreach ($productos as $p) {
            $num_items += $p['cantidad'];
        }

        echo json_encode([
            'ok' => true,
            'total' => $total,
            'total_formateado' => MONEDA . number_format($total, 2, ',', '.'),
            'num_cart' => $num_items
        ]);
    } else {
        echo json_encode(['ok' => false, 'mensaje' => 'Producto no encontrado']);
    }
    exit;
}

/* ============================================================
    ACTUALIZAR CANTIDAD (AJAX)
============================================================ */
if (isset($_POST['actualizar']) && isset($_POST['clave']) && isset($_POST['cantidad'])) {

    $clave = $_POST['clave'];
    $cantidad = intval($_POST['cantidad']);

    if ($cantidad < 1)
        $cantidad = 1;
    if ($cantidad > 10)
        $cantidad = 10;

    if (isset($_SESSION['id_carrito']['producto'][$clave])) {

        $_SESSION['id_carrito']['producto'][$clave]['cantidad'] = $cantidad;

        // Recalcular total
        $productos = $_SESSION['id_carrito']['producto'];
        $total = 0;

        foreach ($productos as $c => $item) {

            $parts = explode('_', $c);
            $id_producto = $parts[0];

            $sql = $con->prepare("SELECT precio, descuento FROM producto WHERE id_producto=? AND activo=1");
            $sql->execute([$id_producto]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $precio_desc = $row['precio'] - (($row['precio'] * $row['descuento']) / 100);
                $total += $item['cantidad'] * $precio_desc;
            }
        }

        // Contar items reales
        $num_items = 0;
        foreach ($productos as $p) {
            $num_items += $p['cantidad'];
        }

        echo json_encode([
            'ok' => true,
            'total' => $total,
            'total_formateado' => MONEDA . number_format($total, 2, ',', '.'),
            'num_cart' => $num_items
        ]);
        exit;
    } else {
        echo json_encode(['ok' => false, 'mensaje' => 'Clave no encontrada']);
        exit;
    }
}

/* ============================================================
    MOSTRAR CARRITO
============================================================ */
$productos = $_SESSION['id_carrito']['producto'] ?? [];
$lista_carrito = [];

if ($productos) {
    foreach ($productos as $clave => $item) {

        $parts = explode('_', $clave);
        $id_producto = $parts[0];
        $talla = $parts[1] ?? '-';

        $sql = $con->prepare("SELECT id_producto, nombre, precio, descuento 
                              FROM producto 
                              WHERE id_producto=? AND activo=1");
        $sql->execute([$id_producto]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['cantidad'] = $item['cantidad'];
            $row['talla'] = $talla;
            $row['clave'] = $clave;
            $lista_carrito[] = $row;
        }
    }
}

$total = 0;
foreach ($lista_carrito as $p) {
    $precio_desc = $p['precio'] - (($p['precio'] * $p['descuento']) / 100);
    $total += $p['cantidad'] * $precio_desc;
}

$num_cart = 0;
foreach ($productos as $p) {
    $num_cart += $p['cantidad'];
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrito</title>
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

            <!-- SOLO UNA VEZ EL COLLAPSE -->
            <div class="collapse navbar-collapse" id="navbarHeader">

                <!-- Menú izquierdo -->
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
                        <a href="checkout.php" class="btn btn-black btn-sm">
                            🛒 <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart; ?></span>
                        </a>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <!-- CARRITO -->
    <div class="container mt-4">
        <h2>Carrito de Compras</h2>

        <table class="table table-bordered">
            <tr>
                <th>Producto</th>
                <th>Talla</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
            </tr>

            <?php if (!$lista_carrito) { ?>
                <tr>
                    <td colspan="6" class="text-center">Carrito vacío</td>
                </tr>
            <?php } else {
                foreach ($lista_carrito as $p) {
                    $precio_desc = $p['precio'] - (($p['precio'] * $p['descuento']) / 100);
                    $subtotal = $precio_desc * $p['cantidad'];
                    ?>
                    <tr id="fila_<?php echo $p['clave']; ?>">
                        <td><?php echo $p['nombre']; ?></td>
                        <td><?php echo $p['talla']; ?></td>

                        <td>
                            <?php
                            echo MONEDA . number_format($precio_desc, 2, ',', '.');
                            ?>
                        </td>

                        <td>
                            <input type="number" min="1" max="10" value="<?php echo $p['cantidad']; ?>">
                        </td>

                        <td>
                            <?php
                            echo MONEDA . number_format($subtotal, 2, ',', '.');
                            ?>
                        </td>

                        <td>
                            <button class="btn btn-danger btn-sm btn-eliminar"
                                data-clave="<?php echo $p['clave']; ?>">Eliminar</button>
                        </td>
                    </tr>

                <?php }
            } ?>

            <tr id="fila_total">
                <td colspan="4" class="text-end fw-bold">Total:</td>
                <td id="totalCarrito">
                    <?php echo MONEDA . number_format($total, 2, ',', '.'); ?>
                </td>
                <td></td>
            </tr>
        </table>

        <?php if ($lista_carrito) { ?>
            <div class="text-end">
                <a href="pago.php" class="btn btn-primary btn-lg">Realizar Pago</a>
            </div>
        <?php } ?>
    </div>

    <script>

        /* ============================
           ELIMINAR PRODUCTO
        ============================ */
        document.addEventListener('click', e => {
            if (!e.target.classList.contains('btn-eliminar')) return;

            const clave = e.target.dataset.clave;

            const form = new FormData();
            form.append('eliminar', true);
            form.append('clave', clave);

            fetch('checkout.php', { method: "POST", body: form })
                .then(r => r.json())
                .then(data => {

                    if (data.ok) {

                        document.getElementById('fila_' + clave).remove();
                        document.getElementById("totalCarrito").textContent = data.total_formateado;
                        document.getElementById("num_cart").textContent = data.num_cart;

                        if (data.num_cart === 0) {
                            location.reload();
                        }
                    }
                });
        });

        /* ============================
           ACTUALIZAR CANTIDAD
        ============================ */
        document.querySelectorAll("input[type='number']").forEach(input => {

            input.addEventListener("change", function () {

                const fila = this.closest("tr");
                const clave = fila.id.replace("fila_", "");
                const cantidad = this.value;

                const form = new FormData();
                form.append("actualizar", true);
                form.append("clave", clave);
                form.append("cantidad", cantidad);

                fetch("checkout.php", { method: "POST", body: form })
                    .then(r => r.json())
                    .then(data => {

                        if (data.ok) {

                            // Actualizar total general
                            document.getElementById("totalCarrito").textContent = data.total_formateado;

                            // Actualizar badge
                            document.getElementById("num_cart").textContent = data.num_cart;

                            // Tomar precio y limpiar formato
                            let precio = fila.querySelector("td:nth-child(3)").textContent;
                            precio = precio.replace(/[^\d,.-]/g, "");
                            precio = precio.replace(".", "").replace(",", ".");
                            precio = parseFloat(precio);

                            const subtotal = precio * cantidad;

                            // Subtotal formateado CO
                            fila.querySelector("td:nth-child(5)").textContent =
                                "<?php echo MONEDA; ?>" +
                                subtotal.toLocaleString("es-CO", {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                        }
                    });
            });
        });
    </script>

</body>

</html>