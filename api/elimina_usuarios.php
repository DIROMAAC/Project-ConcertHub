<?php
require_once __DIR__ . "/CAD.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

if (isset($_GET['idUsuario']) && is_numeric($_GET['idUsuario'])) {
    $idUsuario = intval($_GET['idUsuario']);

    // Llamar al método eliminaUsuario de la clase CAD para eliminar el usuario
    $resultado = CAD::eliminaUsuario($idUsuario);

    if ($resultado === 1) {
        $mensaje = "Usuario eliminado correctamente.";
    } else {
        $mensaje = "Error al eliminar el usuario.";
    }

    // Redirigir de vuelta a la página de eliminar usuarios con el mensaje
    header("Location: elimina.php?mensaje=" . urlencode($mensaje));
    exit();
} else {
    // Si no se pasa el idUsuario, redirigir
    header("Location: elimina.php");
    exit();
}
?>
