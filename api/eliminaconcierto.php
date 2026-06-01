<?php
require_once "cad.php";
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene permisos de administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['Rol'] != 1) {
    header("Location: logreg.php");
    exit();
}

$cad = new CAD();

if (isset($_GET['idConcierto'])) {
    if ($cad->eliminaConcierto($_GET['idConcierto'])) {
        header("Location: conciertos.php?msg=Concierto eliminado con éxito");
        exit();
    } else {
        $errorMsg = "Error al eliminar el concierto.";
    }
}
?>
