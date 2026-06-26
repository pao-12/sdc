<?php
session_start();
session_unset();
session_destroy();
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $params["path"]);
header("Location: ../index.php");
exit();
