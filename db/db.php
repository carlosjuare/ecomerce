<?php
// Establecer la posibilidad de utilizar variables de sesión

// Verificar si la sesión no está activa antes de iniciarla
if (session_status() == PHP_SESSION_NONE) {
    // Iniciar la sesión
    session_start();
}


// Definir una constante para usar como salto de línea

//Conexión para el servidor live




$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecomerce1.0";

// Conexión a la base de datos
$DB_conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($DB_conn->connect_error) {
    throw new Exception("Error de conexión a MySQL: " . $DB_conn->connect_error);
}
