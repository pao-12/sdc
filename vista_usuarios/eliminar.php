<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
include("conexion.php");

if (isset($_GET['id'])) {
    $idEmpleado = (int) $_SESSION['id_usuario'];
    $idViatico  = (int) $_GET['id'];

    // Verificar que el viático pertenece al empleado logueado
    $check = mysqli_query($conect, "SELECT IDViatico FROM viatico WHERE IDViatico='$idViatico' AND IDEmpleado='$idEmpleado'");
    if (mysqli_num_rows($check) == 0) {
        header("Location: principal.php");
        exit();
    }

    // Borrar archivos físicos
    $sqlArchivos = "SELECT Comprobante FROM gasto WHERE IDViatico = '$idViatico'";
    $resArchivos = mysqli_query($conect, $sqlArchivos);
    while ($gasto = mysqli_fetch_assoc($resArchivos)) {
        if (!empty($gasto['Comprobante'])) {
            $ruta = "uploads/" . $gasto['Comprobante'];
            if (file_exists($ruta)) unlink($ruta);
        }
    }

    mysqli_query($conect, "DELETE FROM gasto   WHERE IDViatico = '$idViatico'");
    mysqli_query($conect, "DELETE FROM viatico WHERE IDViatico = '$idViatico'");

    header("Location: principal.php");
    exit();
}

header("Location: principal.php");
exit();
