<?php
// Finaliza la sesión activa y devuelve al usuario al sitio.
session_start();
session_unset();
session_destroy();
header('Location: /thabathas/login.php');
exit;
