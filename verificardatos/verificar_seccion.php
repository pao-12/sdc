<?php
session_start();

// Conexión directa (no depende de Config.php)
$con = mysqli_connect("localhost", "root", "", "sdc");
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

// ── CAMINO A: GOOGLE (GET) ─────────────────────────────────────────────
if (isset($_GET['google_auth']) && $_GET['google_auth'] == 'true') {
    $nombre = mysqli_real_escape_string($con, $_GET['fullname']);
    $email  = mysqli_real_escape_string($con, $_GET['email']);

    $result = mysqli_query($con, "SELECT * FROM empleado WHERE Email='$email' LIMIT 1");

    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);
        if ($usuario['Activo'] != 1) {
            header("Location: ../index.php"); exit();
        }
    } else {
        mysqli_query($con, "INSERT INTO empleado(Nombre,Email,Rol,Activo) VALUES('$nombre','$email','empleado',1)");
        $id = mysqli_insert_id($con);
        $usuario = ['IDEmpleado'=>$id,'Nombre'=>$nombre,'Email'=>$email,'Rol'=>'empleado'];
    }

    $_SESSION['id_usuario'] = $usuario['IDEmpleado'];
    $_SESSION['nombre']     = $usuario['Nombre'];
    $_SESSION['email']      = $usuario['Email'];
    $_SESSION['rol']        = $usuario['Rol'];

    if ($usuario['Rol'] === 'admin') {
        header("Location: ../vista_admin/dashboard.php");
    } else {
        header("Location: ../principal.php");
    }
    exit();
}

// ── CAMINO B: MANUAL (POST) ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo     = mysqli_real_escape_string($con, trim($_POST['correo']));
    $contrasena = trim($_POST['contrasena']);

    if (empty($correo) || empty($contrasena)) {
        header("Location: ../index.php"); exit();
    }

    $result = mysqli_query($con, "SELECT * FROM empleado WHERE Email='$correo' LIMIT 1");

    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);

        if ($usuario['Activo'] != 1) {
            header("Location: ../index.php"); exit();
        }

        if (password_verify($contrasena, $usuario['Password'])) {
            $_SESSION['id_usuario'] = $usuario['IDEmpleado'];
            $_SESSION['nombre']     = $usuario['Nombre'];
            $_SESSION['email']      = $usuario['Email'];
            $_SESSION['rol']        = $usuario['Rol'];

            if ($usuario['Rol'] === 'admin') {
                header("Location: ../vista_admin/dashboard.php");
            } else {
                header("Location: ../principal.php");
            }
            exit();
        }
    }

    header("Location: ../index.php");
    exit();
}

header("Location: ../index.php");
exit();
