<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEmpleado   = (int) $_SESSION['id_usuario'];
    $idViatico    = (int) $_POST['IDViatico'];
    $nombreSalida = mysqli_real_escape_string($conect, trim($_POST['NombreSalida']));
    $fecha        = mysqli_real_escape_string($conect, $_POST['Fecha']);
    $hora         = mysqli_real_escape_string($conect, $_POST['Hora']);

    // Verificar que el viático es del empleado logueado
    $check = mysqli_query($conect, "SELECT IDViatico FROM viatico WHERE IDViatico='$idViatico' AND IDEmpleado='$idEmpleado'");
    if (mysqli_num_rows($check) == 0) {
        header("Location: principal.php");
        exit();
    }

    $sqlUpViatico = "UPDATE viatico SET NombreSalida='$nombreSalida', Fecha='$fecha', Hora='$hora' WHERE IDViatico='$idViatico'";

    if (mysqli_query($conect, $sqlUpViatico)) {
        // Borrar gastos viejos e insertar los nuevos
        mysqli_query($conect, "DELETE FROM gasto WHERE IDViatico='$idViatico'");

        $nombresGastos       = $_POST['NombreGasto']       ?? [];
        $montos              = $_POST['Monto']             ?? [];
        $comprobantesActuales = $_POST['ComprobanteActual'] ?? [];

        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        for ($i = 0; $i < count($nombresGastos); $i++) {
            $nombreGasto     = mysqli_real_escape_string($conect, trim($nombresGastos[$i]));
            $monto           = mysqli_real_escape_string($conect, $montos[$i]);
            $nombreArchivoDB = $comprobantesActuales[$i] ?? "";

            if (isset($_FILES['Comprobante']['name'][$i]) && $_FILES['Comprobante']['error'][$i] == 0) {
                $fileName       = time() . "_" . $idEmpleado . "_" . basename($_FILES['Comprobante']['name'][$i]);
                $targetFilePath = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['Comprobante']['tmp_name'][$i], $targetFilePath)) {
                    $nombreArchivoDB = $fileName;
                }
            }

            $sqlGasto = "INSERT INTO gasto (IDViatico, NombreGasto, Monto, Comprobante, FechaGasto)
                         VALUES ('$idViatico', '$nombreGasto', '$monto', '$nombreArchivoDB', '$fecha')";
            mysqli_query($conect, $sqlGasto);
        }

        header("Location: principal.php");
        exit();
    } else {
        echo "Error al actualizar: " . mysqli_error($conect);
    }
}
mysqli_close($conect);
