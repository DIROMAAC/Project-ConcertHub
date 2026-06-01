<?php
require_once __DIR__ . "/CAD.php";  // Incluir la clase CAD
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

// Verificar si se recibió el ID de la canción y si no está vacío
if (isset($_POST['idCancion']) && !empty($_POST['idCancion'])) {
    $idCancion = (int) $_POST['idCancion'];  // Asegurarse de que el ID sea un número entero
    
    // Crear una instancia de la clase CAD
    $cad = new CAD();
    
    // Obtener los datos de la canción
    $cancion = $cad->traerCancionPorId($idCancion);
    
    if ($cancion) {
        // Eliminar el archivo de audio del servidor si la canción existe
        $archivoAudio = $_SERVER['DOCUMENT_ROOT'] . '/' . $cancion['archivo_audio'];
        if (file_exists($archivoAudio)) {
            if (unlink($archivoAudio)) {
                // Si el archivo se eliminó correctamente
                echo "Archivo de audio eliminado.";
            } else {
                // Si no se puede eliminar el archivo
                echo "No se pudo eliminar el archivo de audio.";
            }
        }

        // Eliminar la canción de la base de datos
        if ($cad->eliminarCancion($idCancion)) {
            // Redirigir de vuelta a la página de canciones del artista
            header("Location: addsongs.php?idArtista=" . $cancion['idartista']);
            exit;  // Asegurarse de que el script termine después de la redirección
        } else {
            echo "Error al eliminar la canción de la base de datos.";
        }
    } else {
        echo "Canción no encontrada con ID: " . htmlspecialchars($idCancion);
    }
} else {
    echo "No se recibió el ID de la canción.";
}
?>
