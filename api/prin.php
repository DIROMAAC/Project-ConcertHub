<?php
require_once __DIR__ . "/CAD.php";
session_start(); // Iniciar la sesión

$cad = new CAD();
$proximosConciertos = array_slice($cad->traeConciertos(), 0, 5); // Obtener los próximos conciertos
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="../js/funcprin.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/estprin.css">
    <title>ConcertHub</title>
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
                <a href="prin.php">HOME</a>
                <a href="artistas.php">ARTISTAS</a>
                <a href="conciertos.php">CONCIERTOS</a>
                <a href="actualiza.php">PERFIL</a>
            </div>
            <div class="icons">
                <a href="search.php"><img src="https://img.icons8.com/?size=100&id=7695&format=png&color=FFFFFF"></a>
                <a href="compras.php"><img src="https://img.icons8.com/?size=100&id=59997&format=png&color=FFFFFF"></a>
                <a href="logout.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=FFFFFF"></a>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="contenido">
            <div class="banner">
                <img src="../img/part.jpg">
            </div>
            <div class="newalbum">
                <div class="spotify-player">
                    <iframe id="spotifyIframe" frameborder="0" allowtransparency="true" allow="encrypted-media" src="https://open.spotify.com/embed/album/7kfPf285KnlWUTbqaB1jnI?si=XbRbFN1YTZi1hB1cxuMdsw"></iframe>
                </div>
            </div>
            <div class="calendar">
                <h1>PRÓXIMOS CONCIERTOS</h1>
                <?php foreach ($proximosConciertos as $concierto): ?>
                    <div class="concert">
                        <p class="concert-date"><?php echo htmlspecialchars($concierto['fecha']); ?></p>
                        <p class="concert-time"><?php echo htmlspecialchars($concierto['hora']); ?></p>
                        <p class="concert-artist"><?php echo htmlspecialchars($concierto['artista']); ?></p>
                        <p class="concert-place"><?php echo htmlspecialchars($concierto['lugar']); ?></p>
                        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($concierto['precio']); ?></p>
                        <div>
                            <a href="compras.php?idConcierto=<?php echo $concierto['idConcierto']; ?>" class="buy-button">Comprar Entradas</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="show-more">
                    <button onclick="window.location.href='conciertos.php';" class="show-more-button">Mostrar Más</button>
                </div>
            </div>
            <div class="artists-container">
                <div class="artists">
                    <!-- Las imágenes de los artistas se cargarán dinámicamente aquí -->
                </div>
            </div>
        </div>
        <!-- Pie -->
        <div class="pie">
            <video autoplay muted loop id="pie-video">
                <source src="../vid/fondo.mp4" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
            <div class="pie-content">
                <div class="pie-brand">ConcertHub</div>
                <div class="pie-socials">
                    <a href="#" target="_blank"><img src="https://img.icons8.com/?size=100&id=32292&format=png&color=FFFFFF" alt="Facebook"></a>
                    <a href="#" target="_blank"><img src="https://img.icons8.com/?size=100&id=435&format=png&color=FFFFFF" alt="Twitter"></a>
                    <a href="#" target="_blank"><img src="https://img.icons8.com/?size=100&id=111056&format=png&color=FFFFFF" alt="Instagram"></a>
                </div>
                <h2>ConcertHub &copy; 2022 Todos los derechos reservados.</h2>
            </div>
        </div>
    </div>
</body>

</html>