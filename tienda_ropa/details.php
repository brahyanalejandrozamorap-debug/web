<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id_producto = isset($_GET['id_producto']) ? $_GET['id_producto'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id_producto == '' || $token == '') {
    echo "Error al procesar la petición";
    exit;
}

$token_tmp = hash_hmac('sha1', $id_producto, KEY_TOKEN);
if ($token != $token_tmp) {
    echo "Token inválido";
    exit;
}

// Obtener información del producto
$sql = $con->prepare("SELECT * FROM producto WHERE id_producto=? AND activo=1 LIMIT 1");
$sql->execute([$id_producto]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Producto no encontrado";
    exit;
}

// Datos del producto
$nombre = $row['nombre'];
$descripcion = $row['descripcion'];
$precio = $row['precio'];
$descuento = $row['descuento'];
$precio_desc = $precio - (($precio * $descuento) / 100);
$tallas = explode(',', $row['talla']);
$color = $row['color'];
$genero = $row['genero'];

// Imagen principal
$dir_images = 'images/productos/' . $id_producto . '/';
$rutaImg = $dir_images . 'principal.png';
if (!file_exists($rutaImg))
    $rutaImg = 'images/no-photo.jpg';

// Imágenes secundarias usando scandir
$imagenes = [];
if (is_dir($dir_images)) {
    $archivos = scandir($dir_images);
    foreach ($archivos as $archivo) {
        if ($archivo != 'principal.png' && preg_match('/\.(png|jpg)$/i', $archivo)) {
            $imagenes[] = $dir_images . $archivo;
        }
    }
}

// Número de productos en carrito
$num_cart = 0;
if (!empty($_SESSION['id_carrito']['producto']) && is_array($_SESSION['id_carrito']['producto'])) {
    $num_cart = count($_SESSION['id_carrito']['producto']);
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $nombre; ?></title>
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

                <ul class="navbar-nav ms-auto d-flex align-items-center">
                    <?php if (isset($_SESSION['id_cliente'])): ?>
                        <li class="nav-item me-2">
                            <span class="navbar-text text-white">
                                Hola, <?php echo $_SESSION['nombre_cliente']; ?>
                            </span>
                        </li>
                        <li class="nav-item me-2">
                            <a href="loginout.php" class="btn btn-secondary">Salir</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a href="login.php" class="btn btn-black">👤</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a href="checkout.php" class="btn btn-black">🛒 <span id="num_cart"
                                class="badge bg-secondary"><?php echo $num_cart; ?></span></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <div class="container mt-5">
            <div class="row g-4">

                <!-- Columna del carrusel -->
                <div class="col-md-6">
                    <div id="carouselImages" class="carousel slide" data-bs-ride="true">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="<?php echo $rutaImg; ?>" class="d-block w-100">
                            </div>

                            <?php foreach ($imagenes as $img) { ?>
                                <div class="carousel-item">
                                    <img src="<?php echo $img; ?>" class="d-block w-100">
                                </div>
                            <?php } ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselImages"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                <!-- Columna de información -->
                <div class="col-md-6">
                    <h2><?php echo $nombre; ?></h2>
                    <p><?php echo $descripcion; ?></p>
                    <p>Precio: <?php echo MONEDA . number_format($precio_desc, 2); ?>
                        <?php if ($descuento > 0) { ?>
                            <del><?php echo MONEDA . number_format($precio, 2); ?></del>
                            <span class="badge bg-danger"><?php echo $descuento; ?>%</span>
                        <?php } ?>
                    </p>
                    <p>Tallas:
                        <?php foreach ($tallas as $t) { ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 talla-btn"
                                onclick="seleccionarTalla(this)">
                                <?php echo $t; ?>
                            </button>
                        <?php } ?>
                    </p>

                    <p>Genero: <?php echo $genero; ?></p>
                    <p>Color: <?php echo $color; ?></p>

                    <button class="btn btn-light"
                        onclick="addProducto(<?php echo $id_producto; ?>, '<?php echo $token_tmp; ?>')">Agregar al
                        Carrito</button>
                    <button class="btn btn-danger"
                        onclick="addProducto(<?php echo $id_producto; ?>, '<?php echo $token_tmp; ?>')">Comprar</button>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-6">
                    <h5>Contáctanos</h5>
                    <p>
                        Dirección: Carrera 5, Ibagué, Colombia<br>
                        Teléfono: +57 300 123 4567<br>
                        Email: contacto@tienda.com
                    </p>
                </div>

                <div class="col-md-6">
                    <h5>Síguenos</h5>
                    <a href="#" class="text-white me-3">Facebook</a><br>
                    <a href="#" class="text-white me-3">Instagram</a><br>
                    <a href="#" class="text-white">WhatsApp</a>
                </div>
            </div>

            <hr class="bg-white">
            <p class="text-center mb-0">&copy; 2025 Tienda Ropa. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let tallaSeleccionada = '';

        function seleccionarTalla(el) {
            document.querySelectorAll('.talla-btn').forEach(b => b.classList.remove('btn-primary'));
            document.querySelectorAll('.talla-btn').forEach(b => b.classList.add('btn-outline-secondary'));

            el.classList.remove('btn-outline-secondary');
            el.classList.add('btn-primary');

            tallaSeleccionada = el.textContent;
        }

        function addProducto(id_producto, token) {

            if (!tallaSeleccionada) {
                alert('Selecciona una talla antes de agregar al carrito');
                return;
            }

            let formData = new FormData();
            formData.append('id_producto', id_producto);
            formData.append('token', token);
            formData.append('talla', tallaSeleccionada);
            formData.append('cantidad', 1); // ← IMPORTANTE

            fetch('clases/carrito.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        document.getElementById('num_cart').innerHTML = data.numero;
                        alert('Producto agregado al carrito');
                    } else {
                        alert(data.mensaje);
                        if (data.mensaje.includes('iniciar sesión')) {
                            window.open('login.php', '_blank');
                        }
                    }
                });
        }
    </script>

</body>

</html>