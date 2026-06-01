<?php
require_once "cad.php";
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: logreg.php");
    exit();
}

$datosModificar = "";
$bandContr = false;
$bandCorreo = false;
$bandNombre = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosModificar = [];

    if (!empty($_POST['nombre'])) {
        $datosModificar['nombre'] = trim($_POST['nombre']);
    }

    if (!empty($_POST['correo'])) {
        $datosModificar['correo'] = trim($_POST['correo']);
    }

    if (!empty($_POST['contrasena'])) {
        $datosModificar['contrasena'] = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    }

    if (!empty($datosModificar)) {
        $cad = new CAD();
        if ($cad->modificaUsuario($datosModificar, $_SESSION['idUsuario'])) {
            header("Location: logreg.php");
            exit();
        } else {
            $errorMsg = "Error al actualizar los datos.";
        }
    } else {
        $errorMsg = "No se han ingresado datos para actualizar.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/actualiza.css">
    <title>Actualizar Datos</title>
</head>

<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <div class="update-section">
                <h2>Actualizar Datos</h2>
                <?php if (!empty($errorMsg)) echo "<p class='error-msg'>$errorMsg</p>"; ?>
                <form action="actualiza.php" method="POST">
                    <input type="text" name="nombre" placeholder="Nuevo Nombre" value="">
                    <input type="email" name="correo" placeholder="Nuevo Correo" value="">
                    <input type="password" name="contrasena" placeholder="Nueva Contraseña" value="">
                    <button type="submit" class="btn-submit">Actualizar</button>
                </form>
                
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
                <a href="prin.php" class="forgot-password">Regresar</a>
            </div>
        </div>
    </div>
</body>

</html>