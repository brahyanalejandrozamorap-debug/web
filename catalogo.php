<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';

$db = new Database();
$conexion = $db->conectar();

// --- Recoger filtros desde GET (limpiarlos) ---
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$color = isset($_GET['color']) ? trim($_GET['color']) : '';
$genero = isset($_GET['genero']) ? trim($_GET['genero']) : '';
$talla = isset($_GET['talla']) ? trim($_GET['talla']) : '';
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (float) $_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (float) $_GET['max'] : null;

// --- Construir la consulta de forma segura ---
$sql = "SELECT * FROM producto WHERE activo = 1";
$params = [];

if ($q !== '') {
    $sql .= " AND nombre LIKE :q";
    $params[':q'] = "%$q%";
}
if ($color !== '') {
    $sql .= " AND color = :color";
    $params[':color'] = $color;
}
if ($genero !== '') {
    $sql .= " AND genero = :genero";
    $params[':genero'] = $genero;
}
if ($talla !== '') {
    $sql .= " AND FIND_IN_SET(:talla, talla)";
    $params[':talla'] = $talla;
}
if ($min !== null) {
    $sql .= " AND precio >= :min";
    $params[':min'] = $min;
}
if ($max !== null) {
    $sql .= " AND precio <= :max";
    $params[':max'] = $max;
}

$sql .= " ORDER BY id_producto DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Catálogo</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>

<body class="catalogo-bg">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Tienda de Ropa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader"
                aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarHeader">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
                </ul>

                <ul class="navbar-nav ms-auto d-flex align-items-center">
                    <?php if (isset($_SESSION['id_cliente'])): ?>
                        <li class="nav-item me-2">
                            <span class="navbar-text text-white">Hola, <?php echo $_SESSION['nombre_cliente']; ?></span>
                        </li>
                        <li class="nav-item me-2"><a href="loginout.php" class="btn btn-secondary btn-sm">Salir</a></li>
                    <?php else: ?>
                        <li class="nav-item me-2"><a href="login.php" class="btn btn-black btn-sm">👤</a></li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a href="checkout.php" class="btn btn-black btn-sm">🛒 <span id="num_cart"
                                class="badge bg-secondary"><?php echo $num_cart ?? 0; ?></span></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- FILTROS -->
            <aside class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold">Filtros</div>
                    <div class="card-body">
                        <form id="filterForm" action="" method="GET">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Buscar por nombre</label>
                                <input type="text" name="q" class="form-control" placeholder="Buscar producto..."
                                    value="<?= htmlspecialchars($q ?? '') ?>">
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Color</label>
                                <select class="form-select" name="color">
                                    <option value="">Todos</option>
                                    <option value="rojo" <?= ($color === 'rojo') ? 'selected' : '' ?>>Rojo</option>
                                    <option value="azul" <?= ($color === 'azul') ? 'selected' : '' ?>>Azul</option>
                                    <option value="negro" <?= ($color === 'negro') ? 'selected' : '' ?>>Negro</option>
                                    <option value="blanco" <?= ($color === 'blanco') ? 'selected' : '' ?>>Blanco</option>
                                    <option value="verde" <?= ($color === 'verde') ? 'selected' : '' ?>>Verde</option>
                                </select>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Género</label>
                                <select class="form-select" name="genero">
                                    <option value="">Todos</option>
                                    <option value="Masculino" <?= ($genero === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                                    <option value="Femenino" <?= ($genero === 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                                    <option value="Unisex" <?= ($genero === 'Unisex') ? 'selected' : '' ?>>Unisex</option>
                                </select>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Talla</label>
                                <select class="form-select" name="talla">
                                    <option value="">Todas</option>
                                    <option value="S" <?= ($talla === 'S') ? 'selected' : '' ?>>S</option>
                                    <option value="M" <?= ($talla === 'M') ? 'selected' : '' ?>>M</option>
                                    <option value="L" <?= ($talla === 'L') ? 'selected' : '' ?>>L</option>
                                    <option value="XL" <?= ($talla === 'XL') ? 'selected' : '' ?>>XL</option>
                                </select>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-danger fw-bold">Aplicar filtros</button>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- LISTADO DE PRODUCTOS -->
            <section class="col-md-9">
                <h4 class="mb-3">Resultados</h4>
                <div class="row g-3">
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <div class="col-md-4">
                                <div class="card h-100 shadow-sm">
                                    <?php
                                    $id = $p['id_producto'];
                                    $imagen = "images/productos/$id/principal.png";
                                    if (!file_exists($imagen))
                                        $imagen = "images/no-photo.jpg";
                                    ?>
                                    <img src="<?php echo $imagen; ?>" class="card-img-top"
                                        alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($p['nombre']); ?></h5>
                                        <h5 class="text-danger fw-bold">$<?php echo number_format($p['precio']); ?> COP</h5>
                                    </div>
                                    <?php $token = hash_hmac('sha1', $p['id_producto'], KEY_TOKEN); ?>
                                    <div class="card-footer bg-light">
                                        <a href="details.php?id_producto=<?php echo $p['id_producto']; ?>&token=<?php echo $token; ?>"
                                            class="btn btn-danger w-100">Ver detalles</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning text-center fw-bold">
                                El producto no existe
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>


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

            <!-- scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>