<?php
include 'header.php';
require_once __DIR__ . '/../core/db.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_calificador = $_SESSION['id_usuario'];

// Validar ID del usuario a calificar
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de usuario a calificar no válido.");
}

$id_calificado = (int) $_GET['id'];

// Evitar que un usuario se califique a sí mismo
if ($id_calificador === $id_calificado) {
    die("No puedes calificarte a ti mismo.");
}

// Capturar URL de redirección (opcional)
$redir = $_GET['redir'] ?? '';

$db = new Database();
$conn = $db->connect();

// Verificar si ya calificó antes
$stmt = $conn->prepare("SELECT * FROM calificaciones WHERE id_usuario_calificador = :calificador AND id_usuario_calificado = :calificado");
$stmt->execute([
    ':calificador' => $id_calificador,
    ':calificado' => $id_calificado
]);

$yaCalifico = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calificar Usuario</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px auto;
      max-width: 600px;
    }

    form {
      padding: 20px;
      background-color: #f8f8f8;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    select, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    button {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      background-color: #007bff;
      color: white;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .info {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <h2>Calificar al Usuario</h2>

  <div class="info">
    <p>Estás calificando al usuario con ID: <strong><?= $id_calificado ?></strong></p>
  </div>

  <?php if ($yaCalifico): ?>
    <p>Ya has calificado a este usuario. No puedes calificarlo más de una vez.</p>
  <?php else: ?>
    <form action="../funciones/procesar_calificacion.php" method="POST">
      <input type="hidden" name="id_calificado" value="<?= $id_calificado ?>">
      <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">

      <label for="puntuacion">Puntuación (1 a 5):</label>
      <select name="puntuacion" id="puntuacion" required>
        <option value="">Seleccionar</option>
        <option value="1">1 ★</option>
        <option value="2">2 ★★</option>
        <option value="3">3 ★★★</option>
        <option value="4">4 ★★★★</option>
        <option value="5">5 ★★★★★</option>
      </select>

      <label for="comentario">Comentario:</label>
      <textarea name="comentario" id="comentario" rows="4" required></textarea>

      <button type="submit">Enviar Calificación</button>
    </form>
  <?php endif; ?>

</body>
</html>
