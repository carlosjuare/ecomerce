<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>
    <h2>Login</h2>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
        <p style="color: green;">Usuario registrado correctamente. Inicie sesión.</p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="../funciones/procesar_login.php" method="post">
        <label for="gmail">Correo Gmail:</label>
        <input type="email" name="gmail" required><br><br>

        <label for="clave">Clave:</label>
        <input type="password" name="clave" required><br><br>

        <input type="submit" value="Ingresar">
    </form>

    <br>
    <form action="formulario_usuario.php" method="get">
        <button type="submit">Registrarse</button>
    </form>
</body>
</html>
