<?php
include 'header.php';
require_once __DIR__ . '/../core/db.php';



if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$db = new Database();
$conn = $db->connect();

$filtro = $_GET['tipo'] ?? 'todos';
$precio_min = isset($_GET['precio_min']) && is_numeric($_GET['precio_min']) ? (int)$_GET['precio_min'] : null;
$precio_max = isset($_GET['precio_max']) && is_numeric($_GET['precio_max']) ? (int)$_GET['precio_max'] : null;
$orden_precio = $_GET['orden_precio'] ?? '';
$busqueda_titulo = $_GET['busqueda_titulo'] ?? '';
$orden_fecha = $_GET['orden_fecha'] ?? 'desc';
$filtro_fecha_periodo = $_GET['filtro_fecha_periodo'] ?? '';

$where = [];
$params = [];

$where[] = "p.id_usuario = :id_usuario";
$params[':id_usuario'] = $id_usuario;
$where[] = "p.estado_publicacion != 'eliminado'";
if ($filtro === 'vehiculo') {
    $where[] = "p.tipo_publicacion = 'vehiculo'";
} elseif ($filtro === 'inmueble') {
    $where[] = "p.tipo_publicacion = 'inmueble'";
}

if ($precio_min !== null) {
    $where[] = "p.precio >= :precio_min";
    $params[':precio_min'] = $precio_min;
}
if ($precio_max !== null) {
    $where[] = "p.precio <= :precio_max";
    $params[':precio_max'] = $precio_max;
}

if ($busqueda_titulo !== '') {
    $where[] = "p.titulo LIKE :busqueda_titulo";
    $params[':busqueda_titulo'] = '%' . $busqueda_titulo . '%';
}

if ($filtro_fecha_periodo === 'mes') {
    $where[] = "p.fecha_publicacion >= :fecha_inicio_mes";
    $params[':fecha_inicio_mes'] = date('Y-m-01 00:00:00');
} elseif ($filtro_fecha_periodo === 'anio') {
    $where[] = "p.fecha_publicacion >= :fecha_inicio_anio";
    $params[':fecha_inicio_anio'] = date('Y-01-01 00:00:00');
}

$sql = "
  SELECT p.id_publicacion, p.titulo, p.tipo_publicacion, p.precio,
         v.marca, v.modelo, v.anio,
         i.tipo_inmueble, i.tipo_contrato, i.ciudad,
         img.imagen, p.fecha_publicacion
  FROM publicaciones p
  LEFT JOIN vehiculos v ON v.id_publicacion = p.id_publicacion
  LEFT JOIN inmuebles i ON i.id_publicacion = p.id_publicacion
  LEFT JOIN (
      SELECT id_publicacion, imagen 
      FROM imagenes 
      GROUP BY id_publicacion
  ) img ON img.id_publicacion = p.id_publicacion
";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$orderBy = [];

if ($orden_precio === 'asc') {
    $orderBy[] = "p.precio ASC";
} elseif ($orden_precio === 'desc') {
    $orderBy[] = "p.precio DESC";
}

if ($orden_fecha === 'asc') {
    $orderBy[] = "p.fecha_publicacion ASC";
} else {
    $orderBy[] = "p.fecha_publicacion DESC";
}

$sql .= " ORDER BY " . implode(", ", $orderBy);

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicaciones</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      max-width: 900px;
    }
    form.filtro {
      margin-bottom: 20px;
      background: #f5f5f5;
      padding: 15px;
      border-radius: 6px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: flex-end;
    }
    form.filtro label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
    }
    form.filtro div {
      flex: 1 1 150px;
      min-width: 150px;
    }
    input[type="text"], input[type="number"], select {
      width: 100%;
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button[type="submit"] {
      padding: 8px 15px;
      font-size: 16px;
      cursor: pointer;
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }
    button[type="submit"]:hover {
      background-color: #0056b3;
    }

    .card {
      border: 1px solid #ccc;
      padding: 10px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 15px;
      border-radius: 6px;
      box-shadow: 1px 1px 5px rgba(0,0,0,0.1);
    }
    .card img {
      width: 120px;
      height: 90px;
      object-fit: cover;
      border-radius: 4px;
      flex-shrink: 0;
    }
    .info {
      flex: 1;
    }
    .precio {
      font-weight: bold;
      color: green;
      margin-top: 5px;
    }
    .fecha-publicacion {
      font-size: 0.9em;
      color: #555;
      margin-top: 5px;
    }
  </style>
</head>
<body>

  <h2>Listado de publicaciones</h2>

  <form method="GET" class="filtro">
    <div>
      <label for="tipo">Tipo:</label>
      <select name="tipo" id="tipo">
        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
        <option value="vehiculo" <?= $filtro === 'vehiculo' ? 'selected' : '' ?>>Vehículos</option>
        <option value="inmueble" <?= $filtro === 'inmueble' ? 'selected' : '' ?>>Inmuebles</option>
      </select>
    </div>

    <div>
      <label for="precio_min">Precio mínimo:</label>
      <input type="number" name="precio_min" id="precio_min" min="0" value="<?= $precio_min ?? '' ?>">
    </div>

    <div>
      <label for="precio_max">Precio máximo:</label>
      <input type="number" name="precio_max" id="precio_max" min="0" value="<?= $precio_max ?? '' ?>">
    </div>

    <div>
      <label for="orden_precio">Ordenar precio:</label>
      <select name="orden_precio" id="orden_precio">
        <option value="" <?= $orden_precio === '' ? 'selected' : '' ?>>Sin orden</option>
        <option value="asc" <?= $orden_precio === 'asc' ? 'selected' : '' ?>>Menor a mayor</option>
        <option value="desc" <?= $orden_precio === 'desc' ? 'selected' : '' ?>>Mayor a menor</option>
      </select>
    </div>

    <div>
      <label for="busqueda_titulo">Buscar título:</label>
      <input type="text" name="busqueda_titulo" id="busqueda_titulo" value="<?= htmlspecialchars($busqueda_titulo) ?>">
    </div>

    <div>
      <label for="filtro_fecha_periodo">Fecha publicación:</label>
      <select name="filtro_fecha_periodo" id="filtro_fecha_periodo">
        <option value="" <?= $filtro_fecha_periodo === '' ? 'selected' : '' ?>>Todas</option>
        <option value="mes" <?= $filtro_fecha_periodo === 'mes' ? 'selected' : '' ?>>Este mes</option>
        <option value="anio" <?= $filtro_fecha_periodo === 'anio' ? 'selected' : '' ?>>Este año</option>
      </select>
    </div>

    <div>
      <label for="orden_fecha">Ordenar fecha:</label>
      <select name="orden_fecha" id="orden_fecha">
        <option value="desc" <?= $orden_fecha === 'desc' ? 'selected' : '' ?>>Más recientes primero</option>
        <option value="asc" <?= $orden_fecha === 'asc' ? 'selected' : '' ?>>Más antiguos primero</option>
      </select>
    </div>

    <div style="flex: 0 0 auto;">
      <button type="submit">Filtrar</button>
      <a href="formulario_publicacion.php">Publicar</a>
    </div>
  </form>

  <?php if (count($publicaciones) === 0): ?>
    <p>No hay publicaciones que coincidan con los filtros.</p>
  <?php else: ?>
    <?php foreach ($publicaciones as $pub): ?>
    <div class="card">
  <div class="info">
    <h3>
      <a href="detalle_publicacion.php?id=<?= urlencode($pub['id_publicacion']) ?>">
        <?= htmlspecialchars($pub['titulo']) ?>
      </a>
    </h3>
    
    <?php if ($pub['tipo_publicacion'] === 'vehiculo'): ?>
      <p><?= htmlspecialchars($pub['marca']) ?> <?= htmlspecialchars($pub['modelo']) ?> (<?= htmlspecialchars($pub['anio']) ?>)</p>
    <?php elseif ($pub['tipo_publicacion'] === 'inmueble'): ?>
      <p><?= htmlspecialchars($pub['tipo_inmueble']) ?> en <?= htmlspecialchars($pub['ciudad']) ?> (<?= htmlspecialchars($pub['tipo_contrato']) ?>)</p>
    <?php endif; ?>

    <p class="precio">$ <?= number_format($pub['precio'], 0, ',', '.') ?></p>

    <p class="fecha-publicacion">
      Publicado el 
      <?= !empty($pub['fecha_publicacion']) 
            ? date('d/m/Y', strtotime($pub['fecha_publicacion'])) 
            : 'Fecha no disponible' ?>
    </p>
    <a href="editar_publicacion.php?id=<?= $pub['id_publicacion'] ?>">Editar</a>
    <form action="../funciones/eliminar_publicacion.php" method="POST" style="display:inline;">
  <input type="hidden" name="id_publicacion" value="<?= $pub['id_publicacion'] ?>">
  <button type="submit" onclick="return confirm('¿Estás seguro de eliminar esta publicación?')" style="margin-left:10px; background-color:red; color:white; border:none; padding:5px 10px; border-radius:4px;">
    Eliminar
  </button>
</form>
  </div>

  <?php if ($pub['imagen']): ?>
    <a href="detalle_publicacion.php?id=<?= urlencode($pub['id_publicacion']) ?>">
      <img src="data:image/jpeg;base64,<?= base64_encode($pub['imagen']) ?>" alt="Imagen publicación">
    </a>
  <?php else: ?>
    <p>[Sin imagen]</p>
  <?php endif; ?>
</div>
    <?php endforeach; ?>
  <?php endif; ?>


</body>
</html>