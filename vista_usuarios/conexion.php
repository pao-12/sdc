<?php
$BD       = "sdc";
$Servidor = "localhost";
$Usuario  = "root";
$pass     = "";

$conect = mysqli_connect($Servidor, $Usuario, $pass, $BD);

if ($conect->connect_error) {
    die("Conexión fallida: " . mysqli_connect_error());
}
