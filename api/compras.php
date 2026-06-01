<?php
require_once "cad.php";
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();
$mensaje = "";

// Inicializar el carrito si no existe
if (!isset($_SESSION['compras'])) {
    $_SESSION['compras'] = [];
}

// Obtener información del concierto desde la URL
if (isset($_GET['idConcierto'])) {
    $idConcierto = $_GET['idConcierto'];
    $concierto = $cad->traeConciertoPorId($idConcierto);

    if (!$concierto) {
        $mensaje = "Concierto no encontrado.";
    }
}

// Procesar la cantidad de boletos seleccionada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idConcierto'])) {
    $idConcierto = $_POST['idConcierto'];
    $cantidadBoletos = intval($_POST['cantidadBoletos']);
    $concierto = $cad->traeConciertoPorId($idConcierto);

    if ($concierto && $cantidadBoletos > 0) {
        $precio = floatval($concierto['precio']);
        $total = $cantidadBoletos * $precio;

        // Agregar al carrito
        $_SESSION['compras'][] = [
            'idConcierto' => $idConcierto,
            'artista' => $concierto['artista'],
            'cantidadBoletos' => $cantidadBoletos,
            'total' => $total
        ];

        $mensaje = "Concierto agregado al carrito.";
    } else {
        $mensaje = "Error: Selecciona una cantidad válida.";
    }
}

// Procesar la eliminación de un concierto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarConcierto'])) {
    $idConciertoEliminar = $_POST['eliminarConcierto'];
    foreach ($_SESSION['compras'] as $index => $compra) {
        if ($compra['idConcierto'] == $idConciertoEliminar) {
            unset($_SESSION['compras'][$index]);
            $_SESSION['compras'] = array_values($_SESSION['compras']); // Reindexar el arreglo
            $mensaje = "Concierto eliminado del carrito.";
            break;
        }
    }
}

// Procesar la compra final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizarCompra'])) {
    $idUsuario = $_SESSION['idUsuario'];
    $conexion = (new Conexion())->conectar(); // Instanciar conexión

    foreach ($_SESSION['compras'] as $compra) {
        $query = $conexion->prepare("INSERT INTO compras (idUsuario, idConcierto, cantidadBoletos, total) VALUES (?, ?, ?, ?)");
        $query->execute([$idUsuario, $compra['idConcierto'], $compra['cantidadBoletos'], $compra['total']]);
    }

    // Vaciar el carrito y resetear el descuento aplicado
    $_SESSION['compras'] = [];
    $_SESSION['descuento_aplicado'] = false; // Reset para futuras compras
    $mensaje = "Compra realizada con éxito.";

    // Redirigir a conciertos.php
    header("Location: conciertos.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigoDescuento'])) {
    $codigoDescuento = $_POST['codigoDescuento'];
    $conexion = (new Conexion())->conectar();
    $query = $conexion->prepare("SELECT descuento FROM codigos_d WHERE codigo = ?");
    $query->execute([$codigoDescuento]);
    $resultado = $query->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $descuento = floatval($resultado['descuento']) / 100; // Convertir porcentaje a decimal
        foreach ($_SESSION['compras'] as &$compra) {
            $compra['total'] *= (1 - $descuento); // Aplicar descuento
        }
        $mensaje = "Descuento aplicado con éxito.";
    } else {
        $mensaje = "Código de descuento inválido.";
    }
}

// Procesar la aplicación del código de descuento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigoDescuento'])) {
    if (!isset($_SESSION['descuento_aplicado']) || $_SESSION['descuento_aplicado'] === false) {
        $codigoDescuento = $_POST['codigoDescuento'];
        $conexion = (new Conexion())->conectar();
        $query = $conexion->prepare("SELECT descuento FROM codigos_d WHERE codigo = ?");
        $query->execute([$codigoDescuento]);
        $resultado = $query->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
                $descuento = floatval($resultado['descuento']) / 100; // Convertir porcentaje a decimal
                foreach ($_SESSION['compras'] as &$compra) {
                    $compra['total'] *= (1 - $descuento); // Aplicar descuento
                }
                $_SESSION['descuento_aplicado'] = true; // Marcar descuento como aplicado
                $mensaje = "Descuento aplicado con éxito.";
        } else {
            $mensaje = "Código de descuento inválido.";
        }
    } else {
        $mensaje = "Ya se ha aplicado un descuento en esta compra.";
    }
}

// Calcular el total acumulado
if (!isset($totalAcumulado)) {
    $totalAcumulado = array_reduce($_SESSION['compras'], function ($carry, $item) {
        return $carry + $item['total'];
    }, 0);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/compras.css">
    <title>Comprar Entradas</title>
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/fondo.mp4" type="video/mp4">
    </video>

    <div class="login-register-container">
        <div class="form-wrapper">
            <div class="login-section">
                <h2>Carrito de Compras</h2>
                <?php if (!empty($mensaje)) echo "<p style='color: green;'>$mensaje</p>"; ?>

                <!-- Seleccionar cantidad de boletos -->
                <?php if (isset($concierto)): ?>
                    <h3><?php echo htmlspecialchars($concierto['artista']); ?></h3>
                    <p>Fecha: <?php echo htmlspecialchars($concierto['fecha']); ?></p>
                    <p>Hora: <?php echo htmlspecialchars($concierto['hora']); ?></p>
                    <p>Lugar: <?php echo htmlspecialchars($concierto['lugar']); ?></p>
                    <p>Precio: $<?php echo htmlspecialchars($concierto['precio']); ?></p>
                    <form action="compras.php" method="POST">
                        <input type="hidden" name="idConcierto" value="<?php echo $idConcierto; ?>">
                        <label for="cantidadBoletos">Cantidad de Boletos:</label>
                        <input type="number" id="cantidadBoletos" name="cantidadBoletos" min="1" value="1" required>
                        <button type="submit">Agregar al Carrito</button>
                    </form>
                <?php endif; ?>

                <!-- Mostrar el carrito -->
                <?php if (!empty($_SESSION['compras'])): ?>
                    <ul>
                        <?php foreach ($_SESSION['compras'] as $compra): ?>
                            <li>
                                <?php echo htmlspecialchars($compra['artista']); ?> - 
                                <?php echo $compra['cantidadBoletos']; ?> boletos 
                                (Total: $<?php echo $compra['total']; ?>)
                                <form action="compras.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="eliminarConcierto" value="<?php echo $compra['idConcierto']; ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <h3>Total Acumulado: $<?php echo number_format($totalAcumulado, 2); ?></h3>
                <?php else: ?>
                    <p>No hay elementos en el carrito.</p>
                <?php endif; ?>

                <!-- Campo para código de descuento -->
                <form action="compras.php" method="POST">
                    <label for="codigoDescuento">Código de Descuento:</label>
                    <input type="text" id="codigoDescuento" name="codigoDescuento" placeholder="Ingresa tu código aquí">
                    <button type="submit" name="aplicarDescuento">Aplicar Descuento</button>
                </form>

                <!-- Botones -->
                <form action="compras.php" method="POST">
                    <button type="submit" name="finalizarCompra">Finalizar Compra</button>
                    <a href="conciertos.php">Seguir Comprando</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
