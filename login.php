<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Si el usuario ya está logueado, redirigir al index
if (isset($_SESSION['id_cliente'])) {
    header("Location: index.php");
    exit;
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if ($correo == '' || $password == '') {
        $mensaje = "Debe completar todos los campos";
    } else {
        // Buscar usuario en la BD
        $sql = $con->prepare("SELECT id_cliente, usuario, password FROM cliente WHERE correo=? LIMIT 1");
        $sql->execute([$correo]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Contraseña correcta
            $_SESSION['id_cliente'] = $usuario['id_cliente'];
            $_SESSION['nombre_cliente'] = $usuario['usuario'];

            // Redirigir al index
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "Correo o contraseña incorrectos";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Tienda Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Tienda de Ropa</a>
    </div>

    <li class="cantainer">
        <a class="navbar-brand" href="index.php"> Volver al Inicio</a>
    </li>
</nav>


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center mb-3">Iniciar Sesión</h3>

            <?php if ($mensaje != ''): ?>
                <div class="alert alert-danger"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" name="correo" id="correo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger">Iniciar Sesión</button>
                </div>
                <p class="mt-3 text-center">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
            </form>
        </div>
    </div>
</div>

</html>