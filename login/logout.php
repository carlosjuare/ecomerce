<?php
session_start();
session_unset(); // Limpia todas las variables de sesión
session_destroy(); // Destruye la sesión actual

// Redirige al inicio
header("Location: ../vistageneral/presentacion.php");
exit();