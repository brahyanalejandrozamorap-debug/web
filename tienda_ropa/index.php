<?php

require_once "config/database.php";
require_once "config/config.php";

$db = new Database();
$conexion = $db->conectar();

// Consulta
$sql = "SELECT * FROM producto";
$stmt = $conexion->query($sql);

// Guardamos todo en un arreglo para usarlo en el HTML
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Número de productos en el carrito
$num_cart = isset($_SESSION['id_carrito']['producto']) ? count($_SESSION['id_carrito']['producto']) : 0;

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda_Ropa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>


<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand" href="#">Tienda de Ropa</a>

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



<main>
    <nav class="navbar navbar-expand-lg p-0"
        style="position: relative; width: 100%; height: 100vh; color: white; overflow: hidden; background: none;">

        <div class="navbar-bg d-flex" style="background: url('images/portada/principal1.png') center center no-repeat;
                   background-size: cover;
                   width: 100%;
                   height: 100%;
                   display: flex;
                   align-items: flex-start;   /* Arriba */
                   justify-content: flex-start; /* Izquierda */
                   padding: 40px;"> <!-- Separación del borde -->

            <div class="text-content" style="max-width: 450px;">
                <h1 class="display-3 fw-bold text-black">DESCUBRE NUEVA TEMPORADA</h1>

                <a href="catalogo.php" class="bg-danger text-white d-inline-block fw-bold px-4 py-2"
                    style="font-size: 1.25rem; text-decoration: none;">
                    Comprar Ahora
                </a>
            </div>

        </div>
    </nav>
</main>


<main>
    <!-- Banner de notificación -->
    <div class="custom-notification text-center py-2 bg-danger text-white" tabindex="-1" style="width:100%;">
        <p class="mb-0">🚚 Envío gratis en compras mayores de $600,000</p>
    </div>

</main>

<div id="miCarrusel" class="carousel slide mt-5 mb-5" data-bs-ride="carousel">
    <div class="carousel-inner">

        <!-- Primera diapositiva -->
        <div class="carousel-item active">
            <div class="d-flex justify-content-center gap-4 flex-wrap">

                <?php foreach ($resultado as $row) { ?>
                    <div class="text-center p-0" style="width: 260px;">

                        <?php
                        $id_producto = $row["id_producto"];
                        $imagen = "images/productos/$id_producto/principal.png";
                        if (!file_exists($imagen)) {
                            $imagen = "images/no-photo.jpg";
                        }

                        $precio = $row['precio'];
                        $descuento = isset($row['descuento']) ? $row['descuento'] : 0;
                        $precio_final = $precio;

                        if ($descuento > 0) {
                            $precio_final = $precio - ($precio * ($descuento / 100));
                        }
                        ?>

                        <a
                            href="details.php?id_producto=<?php echo $row['id_producto']; ?>&token=<?php echo hash_hmac('sha1', $row['id_producto'], KEY_TOKEN); ?>">
                            <img src="<?php echo $imagen; ?>"
                                style="width:250px; height:250px; object-fit:contain; border-radius: 0;"
                                class="d-block mx-auto">
                        </a>

                        <div class="pt-2">

                            <!-- SI TIENE DESCUENTO -->
                            <?php if ($descuento > 0) { ?>

                                <p class="fw-bold mb-0 text-danger">
                                    <?php echo '$' . number_format($precio_final, 2, '.', ','); ?> COP
                                    <span class="badge bg-danger ms-1">
                                        -<?php echo $descuento; ?>%
                                    </span>
                                </p>

                            <?php } else { ?>

                                <!-- SI NO TIENE DESCUENTO -->
                                <p class="fw-bold mb-0">
                                    <?php echo '$' . number_format($precio, 2, '.', ','); ?> COP
                                </p>

                            <?php } ?>

                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>

    </div>

    <!-- Controles -->
    <button class="carousel-control-prev" type="button" data-bs-target="#miCarrusel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#miCarrusel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>



<!-- SEGUNDA IMAGEN GRANDE DE LA PAGINA -->

<main class="container-fluid px-0">
    <div class="row g-0 align-items-stretch text-center">

        <!-- Imagen izquierda -->
        <div class="col-12 col-md-4">
            <img src="images/portada/1.PNG" class="img-fluid w-100 h-100 object-fit-cover" alt="Producto 1"
                style="object-fit: cover; height: 100%;">
        </div>

        <!-- Cuadro rojo central -->
        <div class="col-12 col-md-4 d-flex justify-content-center align-items-center text-white text-center"
            style="background-color: #dc3545; min-height: 100vh;">
            <div>
                <h1 class="fw-bold" style="font-size: clamp(2rem, 5vw, 4rem);">EL ACCESORIO QUE HABLA POR TI</h1>
                <a href="catalogo.php" class="btn btn-dark btn-lg mt-4 px-5 py-3 fw-bold">
                    Comprar
                </a>
            </div>

        </div>

        <!-- Imagen derecha -->
        <div class="col-12 col-md-4">
            <img src="images/portada/2.PNG" class="img-fluid w-100 h-100 object-fit-cover" alt="Producto 2"
                style="object-fit: cover; height: 100%;">
        </div>

    </div>
</main>


<main>
    <div class="container mt-4">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">

            <?php foreach ($resultado as $row) { ?>

                <?php
                $id_producto = $row["id_producto"];
                $imagen = "images/productos/$id_producto/principal.png";

                if (!file_exists($imagen)) {
                    $imagen = "images/no-photo.jpg";
                }

                // Calcular precio con descuento (si existe)
                $precio = $row['precio'];
                $descuento = $row['descuento'] ?? 0;
                $precioFinal = $descuento > 0 ? $precio - ($precio * ($descuento / 100)) : $precio;
                ?>

                <div class="col">
                    <div class="card shadow-sm border-0">

                        <a
                            href="details.php?id_producto=<?php echo $row['id_producto']; ?>&token=<?php echo hash_hmac('sha1', $row['id_producto'], KEY_TOKEN); ?>">
                            <img src="<?php echo $imagen; ?>" class="card-img-top product-img">
                        </a>

                        <div class="card-body text-center">
                            <h5 class="card-title mb-2"><?php echo $row['nombre']; ?></h5>

                            <!-- Caja con altura fija -->
                            <div class="product-price-box">

                                <?php if ($descuento > 0) { ?>

                                    <span class="text-danger fw-bold">
                                        <?php echo '$' . number_format($precioFinal, 2, '.', ','); ?> COP
                                    </span>

                                    <span class="text-muted" style="text-decoration: line-through; font-size: 0.9rem;">
                                        <?php echo '$' . number_format($precio, 2, '.', ','); ?>
                                    </span>

                                    <span class="badge bg-danger mt-1">-<?php echo $descuento; ?>%</span>

                                <?php } else { ?>

                                    <span class="fw-bold">
                                        <?php echo '$' . number_format($precio, 2, '.', ','); ?> COP
                                    </span>

                                <?php } ?>

                            </div>
                        </div>

                    </div>
                </div>

            <?php } ?>

        </div>
    </div>
</main>





<main>
    <div class="container mt-4">
        <div class="row align-items-center">

            <!-- Columna izquierda: descripción -->
            <div class="col-md-6">
                <h1 class="display-3 fw-bold">PIEZAS PARA</h1>
                <h1 class="display-3 fw-bold">CADA OCASIÓN</h1>
                <a href="catalogo.php" class="btn btn-dark btn-lg mt-4 px-5  fw-bold">
                    Ver mas
                </a>
            </div>

            <!-- Columna derecha: imagen -->
            <div class="col-md-6">
                <img src="images/portada/3.PNG" class="img-fluid rounded" alt="Producto"
                    style="width:100%; height:auto; object-fit:cover;">
            </div>

        </div>
    </div>
</main>



<footer class="bg-dark text-white mt-5">
    <div class="container py-4">
        <div class="row">

            <!-- Sección Contáctanos -->
            <div class="col-md-6">
                <h5>Contáctanos</h5>
                <p>
                    Dirección: Carrera 5, Ibagué, Colombia<br>
                    Teléfono: +57 300 123 4567<br>
                    Email: contacto@tienda.com
                </p>
            </div>

            <!-- Sección Redes Sociales -->
            <div class="col-md-6">
                <h5>Síguenos</h5>
                <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i> Facebook</a><br>
                <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i> Instagram</a><br>
                <a href="#" class="text-white"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            </div>

        </div>

        <hr class="bg-white">
        <p class="text-center mb-0">&copy; 2025 Tienda Ropa. Todos los derechos reservados.</p>
    </div>
</footer>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>



<script>
    function addProducto(id_producto, token) {
        let formData = new FormData();
        formData.append('id_producto', id_producto);
        formData.append('token', token);

        fetch('clases/carrito.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    document.getElementById('num_cart').innerHTML = data.numero;
                } else {
                    alert(data.mensaje);
                    if (data.mensaje.includes('iniciar sesión')) {
                        window.open('login.php', '_blank');
                    }
                }
            });
    }
</script>


</html>