<?php include 'header.php'; ?>
<?php
require_once __DIR__ . '/../core/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de publicación no válido.");
}

$id = (int) $_GET['id'];

$db = new Database();
$conn = $db->connect();

// Obtener datos de la publicación
$sql = "
SELECT 
    p.id_publicacion, p.titulo, p.tipo_publicacion, p.precio, p.fecha_publicacion,
    v.tipo_vehiculo, v.marca, v.modelo, v.anio, v.kilometros, v.tipo_combustible, v.transmision, v.color,
    i.tipo_inmueble, i.tipo_contrato, i.direccion, i.ciudad, i.provincia,
    i.superficie_total, i.superficie_cubierta, i.ambientes, i.dormitorios, i.banios, i.cochera,
    u.id_usuario AS id_usuario_publicador, u.nombre_usuario, u.telefono,
    img.imagen
FROM publicaciones p
LEFT JOIN vehiculos v ON v.id_publicacion = p.id_publicacion
LEFT JOIN inmuebles i ON i.id_publicacion = p.id_publicacion
LEFT JOIN usuario u ON u.id_usuario = p.id_usuario
LEFT JOIN (
    SELECT id_publicacion, imagen 
    FROM imagenes 
    GROUP BY id_publicacion
) img ON img.id_publicacion = p.id_publicacion
WHERE p.id_publicacion = :id
";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$pub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pub) {
    die("Publicación no encontrada.");
}

$id_publicador = $pub['id_usuario_publicador'];

// Obtener comentarios
$sqlComentarios = "SELECT c.comentario, c.fecha_comentario, u.nombre_usuario
                   FROM comentarios c
                   JOIN usuario u ON c.id_usuario = u.id_usuario
                   WHERE c.id_publicacion = :id
                   ORDER BY c.fecha_comentario DESC";

$stmtComentarios = $conn->prepare($sqlComentarios);
$stmtComentarios->execute([':id' => $id]);
$comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);

// Obtener promedio y total de calificaciones del publicador
$stmtCalif = $conn->prepare("
    SELECT ROUND(AVG(puntuacion), 1) AS promedio, COUNT(*) AS total 
    FROM calificaciones 
    WHERE id_usuario_calificado = :id
");
$stmtCalif->execute([':id' => $id_publicador]);
$calif = $stmtCalif->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle de publicación</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      max-width: 800px;
    }
    .imagen-principal {
      max-width: 100%;
      height: auto;
      margin-bottom: 15px;
    }
    .info {
      border: 1px solid #ccc;
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
    }
    .info h2 {
      margin-top: 0;
    }
    .precio {
      font-size: 1.4em;
      color: green;
      font-weight: bold;
    }
    .dato {
      margin: 8px 0;
    }
  </style>
</head>
<body>

  <div class="info">
    <h2><?= htmlspecialchars($pub['titulo']) ?></h2>
    <p class="precio">$ <?= number_format($pub['precio'], 0, ',', '.') ?></p>
    <p><strong>Publicado el:</strong> <?= date('d/m/Y', strtotime($pub['fecha_publicacion'])) ?></p>

    <?php if ($pub['imagen']): ?>
      <img class="imagen-principal" src="data:image/jpeg;base64,<?= base64_encode($pub['imagen']) ?>" alt="Imagen de la publicación">
    <?php else: ?>
      <p>[Sin imagen]</p>
    <?php endif; ?>
  </div>

  <div class="info">
    <h3>Detalles</h3>
    <?php if ($pub['tipo_publicacion'] === 'vehiculo'): ?>
      <p class="dato"><strong>Marca:</strong> <?= htmlspecialchars($pub['marca']) ?></p>
      <p class="dato"><strong>Modelo:</strong> <?= htmlspecialchars($pub['modelo']) ?> (<?= htmlspecialchars($pub['anio']) ?>)</p>
      <p class="dato"><strong>Tipo:</strong> <?= htmlspecialchars($pub['tipo_vehiculo']) ?></p>
      <p class="dato"><strong>Kilómetros:</strong> <?= number_format($pub['kilometros'], 0, ',', '.') ?></p>
      <p class="dato"><strong>Combustible:</strong> <?= htmlspecialchars($pub['tipo_combustible']) ?></p>
      <p class="dato"><strong>Transmisión:</strong> <?= htmlspecialchars($pub['transmision']) ?></p>
      <p class="dato"><strong>Color:</strong> <?= htmlspecialchars($pub['color']) ?></p>
    
    <?php elseif ($pub['tipo_publicacion'] === 'inmueble'): ?>
      <p class="dato"><strong>Tipo:</strong> <?= htmlspecialchars($pub['tipo_inmueble']) ?></p>
      <p class="dato"><strong>Contrato:</strong> <?= htmlspecialchars($pub['tipo_contrato']) ?></p>
      <p class="dato"><strong>Dirección:</strong> <?= htmlspecialchars($pub['direccion']) ?></p>
      <p class="dato"><strong>Ciudad:</strong> <?= htmlspecialchars($pub['ciudad']) ?> (<?= htmlspecialchars($pub['provincia']) ?>)</p>
      <p class="dato"><strong>Superficie total:</strong> <?= htmlspecialchars($pub['superficie_total']) ?> m²</p>
      <p class="dato"><strong>Superficie cubierta:</strong> <?= htmlspecialchars($pub['superficie_cubierta']) ?> m²</p>
      <p class="dato"><strong>Ambientes:</strong> <?= htmlspecialchars($pub['ambientes']) ?></p>
      <p class="dato"><strong>Dormitorios:</strong> <?= htmlspecialchars($pub['dormitorios']) ?></p>
      <p class="dato"><strong>Baños:</strong> <?= htmlspecialchars($pub['banios']) ?></p>
      <p class="dato"><strong>Cochera:</strong> <?= htmlspecialchars($pub['cochera']) ?></p>
    <?php endif; ?>
  </div>
  <?php
// Procesar número de teléfono para usar con WhatsApp
$telefono = preg_replace('/[^0-9]/', '', $pub['telefono']); // quitar símbolos
$telefono_con_codigo = '54' . ltrim($telefono, '0'); // asumiendo Argentina (código 54) y sin ceros iniciales
$mensaje_whatsapp = urlencode("Hola, estoy interesado en tu publicación en el sitio.");
$whatsapp_link = "https://wa.me/$telefono_con_codigo?text=$mensaje_whatsapp";
?>

  <div class="info">
    <h3>Contacto del publicador</h3>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($pub['nombre_usuario']) ?></p>
  <p><strong>Teléfono:</strong>
  <a href="tel:<?= htmlspecialchars($pub['telefono']) ?>">
    <?= htmlspecialchars($pub['telefono']) ?>
  </a>

  <!-- Botón/ícono WhatsApp al lado del número -->
  <a href="<?= $whatsapp_link ?>" target="_blank" style="margin-left: 8px;" title="Contactar por WhatsApp">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="height: 24px; vertical-align: middle;">
  </a>
</p>

    <p><strong>Calificación del usuario:</strong>
      <?php if ($calif && $calif['total'] > 0): ?>
        <?= $calif['promedio'] ?> ★ (<?= $calif['total'] ?> calificaciones)
      <?php else: ?>
        Sin calificaciones aún.
      <?php endif; ?>
    </p>

    <?php if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] != $id_publicador): ?>
      <p><a href="calificar_usuario.php?id=<?= $id_publicador ?>&redir=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Calificar este usuario</a></p>

    <?php endif; ?>
  </div>

  <div class="info">
    <h3>Comentarios</h3>

    <?php if (count($comentarios) === 0): ?>
      <p>No hay comentarios todavía.</p>
    <?php else: ?>
      <?php foreach ($comentarios as $coment): ?>
        <div style="margin-bottom: 15px; border-top: 1px solid #ddd; padding-top: 10px;">
          <strong><?= htmlspecialchars($coment['nombre_usuario']) ?></strong><br>
          <small><?= date('d/m/Y H:i', strtotime($coment['fecha_comentario'])) ?></small>
          <p><?= nl2br(htmlspecialchars($coment['comentario'])) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="info">
    <h3>Deja un comentario</h3>
    <?php if (isset($_SESSION['id_usuario'])): ?>
      <form action="../funciones/agregar_comentario.php" method="POST">
        <input type="hidden" name="id_publicacion" value="<?= $id ?>">
        <textarea name="comentario" rows="4" style="width: 100%;" required placeholder="Escribe tu comentario..."></textarea><br><br>
        <button type="submit">Comentar</button>
      </form>
    <?php else: ?>
      <p><a href="login.php">Inicia sesión</a> para dejar un comentario.</p>
    <?php endif; ?>
  </div>

</body>
</html>
