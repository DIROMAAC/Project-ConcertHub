<?php
require_once "cad.php";
session_start();

$loginError = "";
$loginMessage = "";

const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 300;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['register_success'])) {
    $loginMessage = (string) $_SESSION['register_success'];
    unset($_SESSION['register_success']);
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
    // Longitud minima recomendada para contrasenas.
    return mb_strlen($contrasena) >= 8;
}

function obtenerIpCliente()
{
    return $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
}

function obtenerClaveIntentos($correo)
{
    $ip = obtenerIpCliente();
    $correoNormalizado = mb_strtolower(limpiarTexto($correo));
    return hash('sha256', $ip . '|' . $correoNormalizado);
}

function obtenerEstadoIntentos($clave)
{
    if (!isset($_SESSION['login_attempts'][$clave])) {
        $_SESSION['login_attempts'][$clave] = [
            'count' => 0,
            'locked_until' => 0,
        ];
    }

    return $_SESSION['login_attempts'][$clave];
}

function registrarFalloLogin($clave)
{
    $estado = obtenerEstadoIntentos($clave);
    $estado['count']++;

    if ($estado['count'] >= MAX_LOGIN_ATTEMPTS) {
        $estado['locked_until'] = time() + LOGIN_LOCK_SECONDS;
        $estado['count'] = 0;
    }

    $_SESSION['login_attempts'][$clave] = $estado;
}

function limpiarIntentosLogin($clave)
{
    unset($_SESSION['login_attempts'][$clave]);
}

function loginBloqueado($clave)
{
    $estado = obtenerEstadoIntentos($clave);
    return $estado['locked_until'] > time();
}

function segundosBloqueoRestante($clave)
{
    $estado = obtenerEstadoIntentos($clave);
    $restante = $estado['locked_until'] - time();
    return $restante > 0 ? $restante : 0;
}

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $loginError = "Solicitud inválida. Recarga la página e inténtalo de nuevo.";
    }

    $cad = new CAD();
    $action = $_POST['action'] ?? '';

    // Procesar Login
    if ($action === 'login' && empty($loginError)) {
        $correo = limpiarTexto($_POST['correo'] ?? '');
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $claveIntentos = obtenerClaveIntentos($correo);

        if (loginBloqueado($claveIntentos)) {
            $segundos = segundosBloqueoRestante($claveIntentos);
            $minutos = (int) ceil($segundos / 60);
            $loginError = "Demasiados intentos fallidos. Intenta de nuevo en {$minutos} minuto(s).";
        }

        if (!empty($correo) && !empty($contrasena) && empty($loginError)) {
            if (!validarCorreo($correo)) {
                $loginError = "El correo no tiene un formato válido.";
            } else {
                $usuario = $cad->verificaUsuario($correo, $contrasena);

                if (is_array($usuario) && !empty($usuario)) {
                    limpiarIntentosLogin($claveIntentos);
                    session_regenerate_id(true);
                    $_SESSION['idUsuario'] = $usuario['idUsuario'];
                    $_SESSION['correo'] = $usuario['correo'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['Rol'] = $usuario['Rol'];
                    header("Location: prin.php");
                    exit();
                } else {
                    registrarFalloLogin($claveIntentos);
                    $loginError = "Correo o contraseña incorrectos.";
                }
            }
        } elseif (empty($loginError)) {
            $loginError = "Por favor, completa todos los campos.";
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
    <title>Login y Registro</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper single-auth">
            <!-- Login Section -->
            <div class="login-section">
                <h2>Inicio de Sesión</h2>
                <?php if (!empty($loginMessage)): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if (!empty($loginError)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <form action="logreg.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="email" id="correo-login" name="correo" placeholder="Correo Electrónico" required>
                    <input type="password" id="password-login" name="contrasena" placeholder="Contraseña" required>
                    <button type="submit">Iniciar Sesión</button>
                </form>
                <a href="registro.php" class="forgot-password">Crear una cuenta</a>
            </div>
        </div>
    </div>
</body>
</html>
