<?php
require_once __DIR__ . "/CAD.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idConcierto = $_POST['idConcierto'];
    $datosModificar = [];

    if (!empty($_POST['fecha'])) {
        $datosModificar['fecha'] = $_POST['fecha'];
    }
    if (!empty($_POST['hora'])) {
        $datosModificar['hora'] = $_POST['hora'];
    }
    if (!empty($_POST['artista'])) {
        $datosModificar['artista'] = $_POST['artista'];
    }
    if (!empty($_POST['lugar'])) {
        $datosModificar['lugar'] = $_POST['lugar'];
    }

    if (!empty($datosModificar)) {
        if ($cad->modificaConcierto($datosModificar, $idConcierto)) {
            header("Location: conciertos.php?msg=Concierto modificado con éxito");
            exit();
        } else {
            $errorMsg = "Error al modificar el concierto.";
        }
    } else {
        $errorMsg = "No se han ingresado datos para actualizar.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/actualiza2.css">
    <title>Modificar Concierto</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <div class="update-section">
                <h2>Modificar Concierto</h2>
                <?php if (!empty($errorMsg)) echo "<p style='color: red;'>$errorMsg</p>"; ?>
                <form action="modificaconcierto.php" method="POST">
                    <input type="hidden" name="idConcierto" value="<?php echo htmlspecialchars($_GET['idConcierto']); ?>">
                    <input type="date" name="fecha" placeholder="Nueva Fecha">
                    <input type="time" name="hora" placeholder="Nueva Hora">
                    <input type="text" name="artista" placeholder="Nuevo Artista">
                    <input type="text" name="lugar" placeholder="Nuevo Lugar">
                    <button type="submit">Modificar</button>
                </form>
                <a href="conciertos.php" class="forgot-password">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
