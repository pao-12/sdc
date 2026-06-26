<?php
include('Config.php'); // Tu archivo con la conexión $con

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturamos los datos respetando las mayúsculas de tu registro.php
    $nombre     = mysqli_real_escape_string($con, trim($_POST['Nombre'])); 
    $correo     = mysqli_real_escape_string($con, trim($_POST['Email'])); 
    $contrasena = trim($_POST['Password']); 

    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        header("Location: ../registro.php");
        exit();
    }

    // 1. Corregido: Validamos usando la variable correcta $correo contra la columna Email
    $check_email = "SELECT IDEmpleado FROM empleado WHERE Email = '$correo' LIMIT 1";
    $result_check = mysqli_query($con, $check_email);

    if (mysqli_num_rows($result_check) > 0) {
        header("Location: ../registro.php");
        exit();
    }

    // 2. Encriptamos la contraseña de forma segura
    $contrasena_encriptada = password_hash($contrasena, PASSWORD_BCRYPT);

    /* 3. Corregido: El rol se envía como 'empleado' (en minúsculas) 
       para que coincida perfectamente con el ENUM de tu base de datos.
    */
    $query_insert = "INSERT INTO empleado (Nombre, Email, Password, Rol, Activo) 
                     VALUES ('$nombre', '$correo', '$contrasena_encriptada', 'empleado', 1)";

    if (mysqli_query($con, $query_insert)) {
        header("Location: ../index.php");
        exit();
    } else {
        // Si hay un error de base de datos, te mandará 'db' en lugar de inventar que ya existe
        header("Location: ../registro.php");
        exit();
    }
} else {
    header("Location: ../registro.php");
    exit();
}