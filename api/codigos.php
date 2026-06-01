<?php
require_once "cad.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();
$mensaje = "";



// Procesar la adición de un nuevo código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo'], $_POST['descuento'])) {
    $codigo = trim($_POST['codigo']);
    $descuento = floatval($_POST['descuento']);

    if (!empty($codigo) && $descuento > 0) {
        $conexion = (new Conexion())->conectar(); // Usar la clase Conexion para conectar
        $query = $conexion->prepare("INSERT INTO codigos_d (codigo, descuento) VALUES (?, ?)");
        $query->execute([$codigo, $descuento]);
        $mensaje = "Código agregado con éxito.";
    } else {
        $mensaje = "Por favor, ingresa un código válido y un descuento positivo.";
    }
}

// Procesar la eliminación de un código
if (isset($_GET['idCodigo']) && is_numeric($_GET['idCodigo'])) {
    $idCodigo = intval($_GET['idCodigo']);
    $conexion = (new Conexion())->conectar(); // Usar la clase Conexion para conectar
    $query = $conexion->prepare("DELETE FROM codigos_d WHERE id = ?");
    $query->execute([$idCodigo]);
    $mensaje = "Código eliminado con éxito.";
}

// Obtener todos los códigos de descuento
$conexion = (new Conexion())->conectar(); // Usar la clase Conexion para conectar
$query = $conexion->query("SELECT * FROM codigos_d");
$codigos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/codigos.css">
    <title>Gestor de Códigos de Descuento</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <h2>Gestor de Códigos de Descuento</h2>

            <?php if (!empty($mensaje)): ?>
                <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <!-- Formulario para agregar código -->
            <form action="codigos.php" method="POST" class="add-code-form">
                <input type="text" name="codigo" placeholder="Código" required>
                <input type="number" name="descuento" step="0.01" placeholder="Descuento (%)" required>
                <button type="submit" class="add-button">Agregar Código</button>
            </form>

            <!-- Mostrar tabla de códigos -->
            <?php if ($codigos): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Descuento</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($codigos as $codigo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($codigo['id']); ?></td>
                                <td><?php echo htmlspecialchars($codigo['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($codigo['descuento']); ?>%</td>
                                <td>
                                    <a href="codigos.php?idCodigo=<?php echo $codigo['id']; ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este código?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay códigos registrados.</p>
            <?php endif; ?>

            <!-- Botón de regresar -->
            <div class="back-button-container">
                <a href="conciertos.php" class="back-button">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
