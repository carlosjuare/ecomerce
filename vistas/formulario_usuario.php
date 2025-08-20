<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>
    <h2>Registro de Usuario</h2>
    <form action="../funciones/insertar_usuario.php" method="post">
        <label>Nombre:</label>
        <input type="text" name="nombre_usuario" required><br><br>

        <label>Apellido:</label>
        <input type="text" name="apellido_usuario" required><br><br>

        <label>Gmail:</label>
        <input type="email" name="gmail" required><br><br>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required><br><br>

        <label>Clave:</label>
        <input type="password" name="clave" required><br><br>

        <input type="submit" value="Registrar">
    </form>
</body>
</html>
