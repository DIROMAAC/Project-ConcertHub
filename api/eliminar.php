<?php
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: ../PROYECTO/php/logreg.php");
    exit();
}

// Incluir el archivo CAD para usar la clase
require_once 'cad.php';

if (isset($_GET['idArtista'])) {
    $idArtista = $_GET['idArtista'];

    // Llamar al método eliminaArtista de la clase CAD para eliminar el artista
    $resultado = CAD::eliminaArtista($idArtista);

    // Redirigir de vuelta a la página de artistas con el mensaje de resultado
    header("Location: artistas.php?mensaje=" . urlencode($resultado));
    exit();
} else {
    // Si no se pasa el idArtista, redirigir
    header("Location: artistas.php");
    exit();
}
?>
