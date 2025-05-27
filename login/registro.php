<?php
include('../db/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar datos del formulario
    $nombre    = $DB_conn->real_escape_string($_POST['nombre']);
    $apellido  = $DB_conn->real_escape_string($_POST['apellido']);
    $clave     = password_hash($_POST['clave'], PASSWORD_BCRYPT); // Hasheamos la clave por seguridad
    $gmail     = $DB_conn->real_escape_string($_POST['gmail']);
    $telefono  = $DB_conn->real_escape_string($_POST['telefono']);
    $tipo_usuario = 1; // Por defecto, usuario común

    // Preparar la consulta
    $sql = "INSERT INTO usuarios (nombre, apellido, clave, gmail, telefono, id_tipo_usuario)
            VALUES ('$nombre', '$apellido', '$clave', '$gmail', '$telefono', '$tipo_usuario')";

    if ($DB_conn->query($sql) === TRUE) {
        echo "<script>alert('Registro exitoso'); window.location.href = 'login.html';</script>";
    } else {
        echo "Error: " . $DB_conn->error;
    }
} else {
    echo "Acceso no válido.";
}
?>
