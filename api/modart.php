<?php
require_once "cad.php";
session_start();

// Verifica si el usuario está logueado y tiene el rol adecuado
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();
$errorMsg = "";
$artista = null;

// Verifica si se pasa el id del artista
if (isset($_GET['idArtista'])) {
    $idArtista = $_GET['idArtista'];
    $artista = $cad->traeArtistaPorId($idArtista);
}

// Si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idArtista = $_POST['idArtista'];
    $datosModificar = [];

    if (!empty($_POST['nombre'])) {
        $datosModificar['nombre'] = $_POST['nombre'];
    }
    if (!empty($_POST['biografia'])) {
        $datosModificar['biografia'] = $_POST['biografia'];
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        // Se sube una nueva imagen
        $targetDir = "../imgart/";
        $fileName = basename($_FILES['imagen']['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFilePath)) {
            $datosModificar['imagen'] = $fileName;
        } else {
            $errorMsg = "Error al subir la imagen.";
        }
    }

    if (!empty($datosModificar)) {
        if ($cad->modificaArtista($datosModificar, $idArtista)) {
            header("Location: artistas.php?msg=Artista modificado con éxito");
            exit();
        } else {
            $errorMsg = "Error al modificar el artista.";
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
    <link rel="stylesheet" href="../css/actualiza2.css">
    <title>Modificar Artista</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <div class="update-section">
                <h2>Modificar Artista</h2>
                <?php if (!empty($errorMsg)) echo "<p style='color: red;'>$errorMsg</p>"; ?>
                <?php if ($artista): ?>
                    <form action="modart.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="idArtista" value="<?= htmlspecialchars($artista['idArtista']) ?>">
                        <input type="text" name="nombre" value="<?= htmlspecialchars($artista['nombre']) ?>" placeholder="Nuevo nombre">
                        <textarea name="biografia" placeholder="Nueva biografía"><?= htmlspecialchars($artista['biografia']) ?></textarea>
                        <input type="file" name="imagen">
                        <button type="submit">Modificar</button>
                    </form>
                <?php else: ?>
                    <p>Artista no encontrado.</p>
                <?php endif; ?>
                <a href="artistas.php" class="forgot-password">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
