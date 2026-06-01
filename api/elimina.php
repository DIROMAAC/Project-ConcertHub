<?php
require_once __DIR__ . "/CAD.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();
$datos = $cad->traeUsuarios();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/elimina_usuarios.css">
    <title>Eliminar Usuarios</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <h2>Eliminar Usuarios</h2>
            <?php if (!empty($_GET['mensaje'])): ?>
                <p style="color: green; text-align: center; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
            <?php endif; ?>
            <?php if ($datos): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Contraseña</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datos as $registro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($registro['idUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($registro['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($registro['correo']); ?></td>
                                <td><?php echo htmlspecialchars($registro['contrasena']); ?></td>
                                <td>
                                    <a href="elimina_usuarios.php?idUsuario=<?php echo $registro['idUsuario']; ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay usuarios registrados.</p>
            <?php endif; ?>
            <!-- Botón de regresar -->
            <div class="back-button-container">
                <a href="logreg.php" class="back-button">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
