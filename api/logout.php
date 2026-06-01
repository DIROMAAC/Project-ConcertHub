<?php
session_start();

// Vaciar variables de sesion
$_SESSION = [];

// Eliminar cookie de sesion si existe
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir sesion y limpiar cookie de sesion personalizada
if (isset($_COOKIE['ch_user_session'])) {
    setcookie('ch_user_session', '', time() - 3600, '/');
}
session_destroy();
header('Location: logreg.php');
exit();
