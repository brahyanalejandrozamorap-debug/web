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
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    if ($usuario == '' || $correo == '' || $password == '' || $password_confirm == '') {
        $mensaje = "Debe completar todos los campos";
    } elseif ($password !== $password_confirm) {
        $mensaje = "Las contraseñas no coinciden";
    } else {
        // Verificar si el correo ya existe
        $sql = $con->prepare("SELECT id_cliente FROM cliente WHERE correo=? LIMIT 1");
        $sql->execute([$correo]);
        if ($sql->fetch()) {
            $mensaje = "El correo ya está registrado";
        } else {
            // Insertar nuevo usuario con contraseña hasheada
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = $con->prepare("INSERT INTO cliente (usuario, correo, password) VALUES (?, ?, ?)");
            if ($sql->execute([$usuario, $correo, $password_hash])) {
                // Guardar datos en sesión
                $_SESSION['id_cliente'] = $con->lastInsertId();
                $_SESSION['nombre_cliente'] = $usuario;

                // Redirigir al index
                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Error al registrar usuario";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tienda Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>


<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Tienda de Ropa</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center mb-3">Registro de Usuario</h3>

            <?php if ($mensaje != ''): ?>
                <div class="alert alert-danger"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" id="correo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger">Registrarse</button>
                </div>
                <p class="mt-3 text-center">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </form>
        </div>
    </div>
</div>

</html>