<?php
$globalVar=100;

function mifuncion (){
    global $globalVar;
    echo $globalVar;
}
?>