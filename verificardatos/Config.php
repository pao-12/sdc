<?php
$Usuario="root";
$Password="";
$Servidor="localhost";
$BD="sdc";

$con=mysqli_connect($Servidor, $Usuario, $Password) or die
("NO SE PUEDO CONECTAR SL SERVIDOR");
$basedatos=mysqli_select_db($con,$BD)or die ("No se puedo conecta a la base de datos");
?>
