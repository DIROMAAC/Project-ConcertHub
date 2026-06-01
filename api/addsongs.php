<?php
require_once __DIR__ . "/CAD.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();
$mensaje = "";

// Verificar si el idArtista se pasa a través de la URL
if (!isset($_GET['idArtista'])) {
    $mensaje = "No se ha seleccionado un artista.";
    exit; // Detener la ejecución si no se pasa el idArtista
}

$idArtista = $_GET['idArtista'];

// Procesar la adición de una nueva canción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_FILES['audio'])) {
    $titulo = trim($_POST['titulo']);
    $audio = $_FILES['audio'];

    // Validar los datos
    if (empty($titulo)) {
        $mensaje = "Por favor, completa el título de la canción.";
    } else {
        // Comprobar si el archivo de audio se subió correctamente
        if ($audio['error'] !== UPLOAD_ERR_OK) {
            $mensaje = "Error al cargar el archivo de audio. Código de error: " . $audio['error'];
        } else {
            // Verificar el tipo de archivo de audio (aceptamos mp3, wav, etc.)
            $audioType = mime_content_type($audio['tmp_name']);
            if (strpos($audioType, 'audio/') === false) {
                $mensaje = "Por favor, sube un archivo de audio válido (mp3, wav, etc.). Tipo detectado: $audioType";
            } else {
                // Obtener la ruta absoluta de la carpeta 'songsart'
                $audioPath = $_SERVER['DOCUMENT_ROOT'] . '/bd/PROYECTO/songsart/' . basename($audio['name']);

                // Verificar si la carpeta 'songsart' existe
                if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/bd/PROYECTO/songsart')) {
                    mkdir($_SERVER['DOCUMENT_ROOT'] . '/bd/PROYECTO/songsart', 0777, true); // Crear la carpeta si no existe
                }

                // Subir el archivo de audio
                if (move_uploaded_file($audio['tmp_name'], $audioPath)) {
                    // Usar el método de la clase CAD para agregar la canción
                    if ($cad->agregarCancion($idArtista, $titulo, 'songsart/' . basename($audio['name']))) {
                        $mensaje = "Canción agregada correctamente.";
                    } else {
                        $mensaje = "Error al agregar la canción a la base de datos.";
                    }
                } else {
                    $mensaje = "Error al subir el archivo de audio.";
                }
            }
        }
    }
}

// Mostrar las canciones del artista seleccionado
$canciones = $cad->obtenerCancionesPorArtista($idArtista);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/addsongs.css">
    <title>Agregar Canción</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <h2>Agregar Canción</h2>

            <?php if (!empty($mensaje)): ?>
                <p style="color: <?php echo $mensaje === 'Canción agregada correctamente.' ? 'green' : 'red'; ?>;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>

            <!-- Formulario para agregar canción -->
            <form action="addsongs.php?idArtista=<?php echo $idArtista; ?>" method="POST" enctype="multipart/form-data" class="add-code-form">
                <input type="text" name="titulo" placeholder="Título de la canción" required>
                <input type="file" name="audio" accept="audio/*" required>
                <button type="submit" class="add-button">Agregar Canción</button>
            </form>

           <!-- Mostrar canciones del artista seleccionado -->
            <?php if (!empty($canciones)): ?>
                <h3>Canciones de este artista:</h3>
                <ul>
                    <?php foreach ($canciones as $cancion): ?>
                        <li>
                            <a href="<?php echo $cancion['archivo_audio']; ?>" target="_blank"><?php echo htmlspecialchars($cancion['titulo']); ?></a>
                            <!-- Botón para eliminar la canción -->
                            <form action="eliminar_cancion.php" method="POST" style="display:inline;">
                                <input type="hidden" name="idCancion" value="<?php echo $cancion['idCancion']; ?>">
                                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar esta canción?');" class="delete-button">Eliminar</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Botón de regresar -->
            <div class="back-button-container">
                <a href="artistas.php" class="back-button">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
