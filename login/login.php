<?php
include('../db/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gmail = $DB_conn->real_escape_string($_POST['gmail']);
    $clave = $_POST['clave'];

    $query = "SELECT * FROM usuarios WHERE gmail = '$gmail' LIMIT 1";
    $result = $DB_conn->query($query);

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        if (password_verify($clave, $usuario['clave'])) {
            // Guardar datos de sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['tipo_usuario'] = $usuario['id_tipo_usuario'];

            echo "<script>alert('Inicio de sesión exitoso'); window.location.href='../vistageneral/presentacion.php';</script>";
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.location.href='login.html';</script>";
    }
} else {
    echo "Acceso no válido.";
}
?>
