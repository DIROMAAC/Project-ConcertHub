<?php
require_once "cad.php";
session_start(); // Iniciar sesión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: logreg.php"); // Redirigir al login si no está autenticado
    exit();
}

// Validar si el rol está definido en la sesión
if (!isset($_SESSION['Rol'])) {
    echo "Error: No tienes permisos para acceder a esta página.";
    exit();
}

$cad = new CAD();

// Inicializar variables de filtros
$filtros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['ubicacion'])) {
        $filtros['lugar'] = $_POST['ubicacion'];
    }
    if (!empty($_POST['fecha'])) {
        $filtros['fecha'] = $_POST['fecha'];
    }
    if (!empty($_POST['artista'])) {
        $filtros['artista'] = $_POST['artista'];
    }
}

// Obtener conciertos filtrados o todos si no hay filtros
$conciertos = $cad->traeConciertos();
if (!empty($filtros)) {
    $conciertos = array_filter($conciertos, function ($concierto) use ($filtros) {
        foreach ($filtros as $key => $value) {
            if (stripos($concierto[$key], $value) === false) {
                return false;
            }
        }
        return true;
    });
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/showconcerts.css">
    <title>ConcertHub - Conciertos</title>
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

        <!-- Filtros -->
        <div class="filter-container">
            <h2>Filtrar Conciertos</h2>
            <form action="conciertos.php" method="POST">
                <div class="filter-group">
                    <input type="text" name="ubicacion" placeholder="Filtrar por ubicación">
                    <input type="date" name="fecha">
                    <input type="text" name="artista" placeholder="Filtrar por artista">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-button">Filtrar</button>
                    <a href="conciertos.php" class="clear-filters">Limpiar Filtros</a>
                </div>
            </form>
        </div>

        <!-- Botón para agregar concierto (solo admin) -->
        <?php if ($_SESSION['Rol'] == 1): ?>
            <div class="add-concert">
                <a href="agrega_concert.php" class="add-concert-button">Agregar Concierto</a>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['Rol'] == 1): ?>
            <div class="add-code">
                <a href="codigos.php" class="add-code-button">Codigos Descuentos</a>
            </div>
        <?php endif; ?>

        <!-- Contenido principal -->
        <div class="contenido">
            <?php if (!empty($conciertos)): ?>
                <?php foreach ($conciertos as $concierto): ?>
                    <div class="concert">
                        <p><?php echo htmlspecialchars($concierto['fecha']); ?></p>
                        <p><?php echo htmlspecialchars($concierto['hora']); ?></p>
                        <p><?php echo htmlspecialchars($concierto['artista']); ?></p>
                        <p><?php echo htmlspecialchars($concierto['lugar']); ?></p>
                        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($concierto['precio']); ?></p>
                        <div class="actions">
                            <?php if ($_SESSION['Rol'] == 1): ?>
                                <a href="modificaconcierto.php?idConcierto=<?php echo $concierto['idConcierto']; ?>" class="edit-button">Modificar</a>
                                <a href="eliminaconcierto.php?idConcierto=<?php echo $concierto['idConcierto']; ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este concierto?');">Eliminar</a>
                            <?php endif; ?>
                            <a href="compras.php?idConcierto=<?php echo $concierto['idConcierto']; ?>" class="buy-button">Comprar Entradas</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay conciertos disponibles.</p>
            <?php endif; ?>
        </div>

        <!-- Pie -->
        <div class="pie">
            <video autoplay muted loop id="pie-video">
                <source src="../vid/fondo.mp4" type="video/mp4">
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