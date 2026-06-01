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

// Procesar la adición de un nuevo artista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['biografia'], $_FILES['foto'])) {
    $nombre = trim($_POST['nombre']);
    $biografia = trim($_POST['biografia']);
    $foto = $_FILES['foto'];

    // Validar los datos
    if (!empty($nombre) && !empty($biografia) && $foto['error'] === 0) {
        // Obtener la ruta absoluta de la carpeta 'imgart'
        $fotoPath = $_SERVER['DOCUMENT_ROOT'] . '/bd/PROYECTO/imgart/' . basename($foto['name']);

        // Verificar si la carpeta 'imgart' existe
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/bd/PROYECTO/imgart')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/bd/PROYECTO/imgart', 0777, true); // Crear la carpeta si no existe
        }

        // Subir la foto
        if (move_uploaded_file($foto['tmp_name'], $fotoPath)) {
            // Guardar el artista en la base de datos usando CAD
            $resultado = CAD::agregaArtista($nombre, $biografia, 'imgart/' . basename($foto['name']));

            if ($resultado === true) {
                // Mensaje de éxito
                $mensaje = "Artista agregado correctamente.";
            } else {
                $mensaje = "Error al agregar el artista: " . $resultado;
            }
        } else {
            $mensaje = "Error al subir la foto del artista.";
        }
    } else {
        $mensaje = "Por favor, completa todos los campos y sube una foto válida.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/codigos.css">
    <title>Agregar Artista</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <h2>Agregar Artista</h2>

            <?php if (!empty($mensaje)): ?>
                <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <!-- Formulario para agregar artista -->
            <form action="addartist.php" method="POST" enctype="multipart/form-data" class="add-code-form">
                <input type="text" name="nombre" placeholder="Nombre del artista" required>
                <textarea name="biografia" placeholder="Biografía del artista" required></textarea>
                <input type="file" name="foto" accept="image/*" required>
                <button type="submit" class="add-button">Agregar Artista</button>
            </form>

            <!-- Botón de regresar -->
            <div class="back-button-container">
                <a href="artistas.php" class="back-button">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
