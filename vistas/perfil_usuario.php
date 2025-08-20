<?php
require_once __DIR__ . '/../core/db.php';
include 'header.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$db = new Database();
$conn = $db->connect();

// Procesar formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_usuario']);
    $apellido = trim($_POST['apellido_usuario']);
    $telefono = trim($_POST['telefono']);
    $clave = $_POST['clave'];
    $confirmar = $_POST['confirmar_clave'];

    if ($clave !== $confirmar) {
        $mensaje = "⚠️ Las contraseñas no coinciden.";
    } else {
        try {
            $sql = "UPDATE usuario SET nombre_usuario = :nombre, apellido_usuario = :apellido, telefono = :telefono";

            $params = [
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':telefono' => $telefono,
                ':id_usuario' => $id_usuario
            ];

            if (!empty($clave)) {
                $sql .= ", clave = :clave";
                $params[':clave'] = password_hash($clave, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $mensaje = "✅ Datos actualizados correctamente.";
            $_SESSION['nombre_usuario'] = $nombre;

        } catch (PDOException $e) {
            $mensaje = "❌ Error al actualizar: " . $e->getMessage();
        }
    }
}

// Obtener datos actuales del usuario
$stmt = $conn->prepare("SELECT nombre_usuario, apellido_usuario, telefono FROM usuario WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 30px auto;
        }

        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .mensaje {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Mi perfil</h2>

<?php if ($mensaje): ?>
    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<form method="POST">
    <label for="nombre_usuario">Nombre:</label>
    <input type="text" name="nombre_usuario" id="nombre_usuario" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>

    <label for="apellido_usuario">Apellido:</label>
    <input type="text" name="apellido_usuario" id="apellido_usuario" value="<?= htmlspecialchars($usuario['apellido_usuario']) ?>" required>

    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>">

    <label for="clave">Nueva contraseña:</label>
    <input type="password" name="clave" id="clave" placeholder="Dejar en blanco si no cambia">

    <label for="confirmar_clave">Confirmar contraseña:</label>
    <input type="password" name="confirmar_clave" id="confirmar_clave">

    <button type="submit">Guardar cambios</button>
</form>

</body>
</html>
