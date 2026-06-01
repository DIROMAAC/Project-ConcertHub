<?php
session_start();

// Conexión a la base de datos
require_once __DIR__ . "/conexion.php";
$con = new Conexion();
$pdo = $con->conectar();

// Obtener los artistas
$query = $pdo->prepare("SELECT * FROM artistas");
$query->execute();
$artistas = $query->fetchAll(PDO::FETCH_ASSOC);

// Obtener canciones si se ha seleccionado un artista
$songs = [];
$nombreArtista = ""; // Variable para almacenar el nombre del artista

if (isset($_GET['idArtista'])) {
    $idArtista = $_GET['idArtista'];

    // Obtener las canciones del artista seleccionado
    $query = $pdo->prepare("SELECT * FROM canciones WHERE idArtista = ?");
    $query->execute([$idArtista]);
    $songs = $query->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el nombre del artista seleccionado
    $queryArtista = $pdo->prepare("SELECT nombre FROM artistas WHERE idArtista = ?");
    $queryArtista->execute([$idArtista]);
    $artistaSeleccionado = $queryArtista->fetch(PDO::FETCH_ASSOC);
    if ($artistaSeleccionado) {
        $nombreArtista = $artistaSeleccionado['nombre'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/estart.css" />
    <title>ConcertHub - Artistas</title>
</head>

<body>
    <div class="contenedor">
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

        <div class="contenido">
            <!-- Botón de agregar artista encima del contenido -->
            <?php if (isset($_SESSION['Rol']) && $_SESSION['Rol'] == 1): ?>
                <div class="add-artist-button">
                    <a href="addartist.php" class="btn-add-artist">Agregar Artista</a>
                </div>
            <?php endif; ?>
            <div id="artist-container" class="artist-grid">
                <?php foreach ($artistas as $artista): ?>
                    <div class="artist">
                        <a href="?idArtista=<?= $artista['idArtista'] ?>" class="artist-link">
                            <div class="artist-info">
                                <img src="../imgart/<?= basename($artista['imagen']) ?>" alt="<?= htmlspecialchars($artista['nombre']) ?>" class="artist-img">
                                <div class="artist-bio">
                                    <p class="artist-name"><?= htmlspecialchars($artista['nombre']) ?></p>
                                    <p class="artist-description"><?= htmlspecialchars($artista['biografia']) ?></p>
                                </div>
                            </div>
                        </a>

                        <!-- Botones solo para el administrador -->
                        <?php if (isset($_SESSION['Rol']) && $_SESSION['Rol'] == 1): ?>
                            <div class="admin-buttons">
                                <a href="modart.php?idArtista=<?= $artista['idArtista'] ?>" class="modify-button">Modificar</a>
                                <a href="eliminar.php?idArtista=<?= $artista['idArtista'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este artista?');">Eliminar</a>
                                <a href="addsongs.php?idArtista=<?= $artista['idArtista'] ?>" class="add-songs-button">Agregar Canciones</a>
                            </div>
                        <?php endif; ?>

                        <!-- Mostrar las canciones solo si este es el artista seleccionado -->
                        <?php if (isset($_GET['idArtista']) && $_GET['idArtista'] == $artista['idArtista']): ?>
                            <div id="songs-container">
                                <h2>Canciones de <?= htmlspecialchars($artista['nombre']) ?></h2>
                                <div id="songs-list">
                                    <?php
                                    // Obtener las canciones de este artista
                                    $queryCanciones = $pdo->prepare("SELECT * FROM canciones WHERE idArtista = ?");
                                    $queryCanciones->execute([$artista['idArtista']]);
                                    $songs = $queryCanciones->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($songs as $song): ?>
                                        <div class="song">
                                            <p><?= htmlspecialchars($song['titulo']) ?></p>
                                            <audio controls>
                                                <source src="../<?= htmlspecialchars($song['archivo_audio']) ?>" type="audio/mp3">
                                                Tu navegador no soporta el elemento de audio.
                                            </audio>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

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