<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEmpleado   = (int) $_SESSION['id_usuario'];
    $nombreSalida = mysqli_real_escape_string($conect, trim($_POST['NombreSalida']));
    $fecha        = mysqli_real_escape_string($conect, $_POST['Fecha']);
    $hora         = mysqli_real_escape_string($conect, $_POST['Hora']);

    if (empty($nombreSalida) || empty($fecha) || empty($hora)) {
        echo "<script>alert('Por favor completa todos los campos.'); window.history.back();</script>";
        exit();
    }

    $sqlViatico = "INSERT INTO viatico (IDEmpleado, NombreSalida, Fecha, Hora, Estado, FechaRegistro) 
                   VALUES ('$idEmpleado', '$nombreSalida', '$fecha', '$hora', 'pendiente', NOW())";

    if (mysqli_query($conect, $sqlViatico)) {
        $idViatico = mysqli_insert_id($conect);

        $nombresGastos = $_POST['NombreGasto'] ?? [];
        $montos        = $_POST['Monto']       ?? [];

        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        for ($i = 0; $i < count($nombresGastos); $i++) {
            $nombreGasto    = mysqli_real_escape_string($conect, trim($nombresGastos[$i]));
            $monto          = mysqli_real_escape_string($conect, $montos[$i]);
            $nombreArchivoDB = "";

            if (isset($_FILES['Comprobante']['name'][$i]) && $_FILES['Comprobante']['error'][$i] == 0) {
                $fileName       = time() . "_" . $idEmpleado . "_" . basename($_FILES['Comprobante']['name'][$i]);
                $targetFilePath = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['Comprobante']['tmp_name'][$i], $targetFilePath)) {
                    $nombreArchivoDB = $fileName;
                }
            }

            $sqlGasto = "INSERT INTO gasto (IDViatico, NombreGasto, Monto, Comprobante, FechaGasto) 
                         VALUES ('$idViatico', '$nombreGasto', '$monto', '$nombreArchivoDB', NOW())";
            mysqli_query($conect, $sqlGasto);
        }

        header("Location: ../principal.php");
        exit();
    } else {
        echo "Error al registrar: " . mysqli_error($conect);
    }
}
mysqli_close($conect);
