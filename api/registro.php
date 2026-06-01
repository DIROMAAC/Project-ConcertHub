<?php
require_once __DIR__ . "/CAD.php";
session_start();

$registerMessage = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function limpiarTexto($valor)
{
    return trim((string) $valor);
}

function validarCorreo($correo)
{
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

function validarContrasena($contrasena)
{
    return mb_strlen($contrasena) >= 8;
}

function validarNombre($nombre)
{
    return mb_strlen($nombre) >= 2 && mb_strlen($nombre) <= 80;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $registerMessage = "Solicitud invalida. Recarga la pagina e intentalo de nuevo.";
    }

    $cad = new CAD();
    $action = $_POST['action'] ?? '';

    if ($action === 'register' && empty($registerMessage)) {
        $nombre = limpiarTexto($_POST['nombre'] ?? '');
        $correo = limpiarTexto($_POST['correo'] ?? '');
        $contrasena = (string) ($_POST['contrasena'] ?? '');

        if (!empty($nombre) && !empty($correo) && !empty($contrasena)) {
            if (!validarNombre($nombre)) {
                $registerMessage = "El nombre debe tener entre 2 y 80 caracteres.";
            } elseif (!validarCorreo($correo)) {
                $registerMessage = "El correo no tiene un formato valido.";
            } elseif (!validarContrasena($contrasena)) {
                $registerMessage = "La contrasena debe tener al menos 8 caracteres.";
            } else {
                $resultado = $cad->agregaUsuario($nombre, $contrasena, $correo);
                if (strpos((string) $resultado, 'correctamente') !== false) {
                    $_SESSION['register_success'] = "Usuario registrado exitosamente. Ahora puedes iniciar sesion.";
                    header("Location: logreg.php");
                    exit();
                }

                $registerMessage = (string) $resultado;
            }
        } else {
            $registerMessage = "Por favor, completa todos los campos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <title>Registro</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper single-auth">
            <div class="register-section">
                <h2>Registro</h2>
                <?php if (!empty($registerMessage)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <form action="registro.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="text" id="nombre-register" name="nombre" placeholder="Nombre" required>
                    <input type="email" id="correo-register" name="correo" placeholder="Correo Electronico" required>
                    <input type="password" id="password-register" name="contrasena" placeholder="Contrasena" required>
                    <button type="submit">Registrar</button>
                </form>
                <a href="logreg.php" class="forgot-password">Ya tengo cuenta, iniciar sesion</a>
            </div>
        </div>
    </div>
</body>
</html>
