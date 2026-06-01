<?php
require_once "cad.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'addconcert') {
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $artista = $_POST['artista'];
        $lugar = $_POST['lugar'];
        $precio = $_POST['precio'];
        
        $cad = new CAD();
        if ($cad->agregaConcierto($fecha, $hora, $artista, $lugar, $precio)) {
            // Redirigir después del registro exitoso
            header("Location: conciertos.php?");
            exit();
        } else {
            $errorMsg = "Error al añadir el concierto.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/addconcert.css">
    <title>Admin - Agregar Conciertos</title>
</head>
<body>
    <div class="contenedor">
        <!-- Menú -->
        <div class="menu">
            <video autoplay muted loop id="background-video">
                <source src="../vid/fondo.mp4" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
            <div class="name">
                <p>ConcertHub</p>
            </div>
            <div class="sections">
                <a href="../php/prin.php">HOME</a>
                <a href="../php/artistas.php">ARTISTAS</a>
                <a href="../php/conciertos.php">CONCIERTOS</a>
                <a href="../php/actualiza.php">PERFIL</a>
            </div>
            <div class="icons">
                <a href="../php/search.php"><img src="https://img.icons8.com/?size=100&id=7695&format=png&color=FFFFFF"></a>
                <a href="../php/compras.php"><img src="https://img.icons8.com/?size=100&id=59997&format=png&color=FFFFFF"></a>
                <a href="../php/logout.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=FFFFFF"></a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="contenido">
            <h1>Agregar Concierto</h1>
            <?php if (!empty($errorMsg)) echo "<p style='color: red;'>$errorMsg</p>"; ?>
            <form action="agrega_concert.php" method="POST">
                <input type="hidden" name="action" value="addconcert">
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <input type="time" id="hora" name="hora" required>
                </div>
                <div class="form-group">
                    <label for="artista">Artista:</label>
                    <input type="text" id="artista" name="artista" required>
                </div>
                <div class="form-group">
                    <label for="lugar">Lugar:</label>
                    <input type="text" id="lugar" name="lugar" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" placeholder="Ej: 99.99" required>
                </div>
                <button type="submit" class="btn-submit">Agregar Concierto</button>
            </form>
        </div>

        <!-- Pie -->
        <div class="pie">
            <video autoplay muted loop id="pie-video">
                <source src="../vid/fondo.mp4" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
            <a href="#"><img src="https://img.icons8.com/?size=100&id=32292&format=png&color=FFFFFF"></a>
            <a href="#"><p>ConcertHub</p></a>
            <a href="#"><img src="https://img.icons8.com/?size=100&id=435&format=png&color=FFFFFF"></a>
            <a href="#"><p>ConcertHub</p></a>
            <a href="#"><img src="https://img.icons8.com/?size=100&id=111056&format=png&color=FFFFFF"></a>
            <a href="#"><p>ConcertHub</p></a>
            <h2>ConcertHub &copy; 2022 Todos los derechos reservados.</h2>
        </div>
    </div>
</body>
</html>
