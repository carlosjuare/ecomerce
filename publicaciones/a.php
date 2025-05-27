<?php
// Iniciar sesión (si no está iniciada aún)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db/db.php');

// Verificar si el 'id' está en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_publicacion = $_GET['id'];

    // Consulta SQL para traer los datos de la publicación con JOIN para obtener los datos de las tablas relacionadas
$query = "
    SELECT 
    p.id_publicacion,
    p.id_tipo_publicacion,
    p.id_vehiculo,
    p.id_inmueble,
    p.precio,
    p.id_user,
    p.id_contrato,
    p.id_estado,
    p.id_imagen_publicacion,
    u.nombre AS nombre_usuario,
    u.apellido AS apellido_usuario,
    c.nombre AS nombre_contrato,
    e.nombre AS nombre_estado,
    t.nombre AS nombre_tipo_publicacion,
    i.imagen AS imagen_blob,

    -- Vehículo
    v.nombre AS nombre_vehiculo,
    v.modelo AS modelo_vehiculo,
    v.kilometraje AS kilometraje_vehiculo,
    v.marca AS marca_vehiculo,
    v.id_tipo_vehiculo AS id_tipo_vehiculo,
    tv.nombre AS nombre_tipo_vehiculo,

    -- Inmueble
    m.nombre AS nombre_inmueble,
    m.km2 AS km2_inmueble,
    m.id_tipo_inmueble AS id_tipo_inmueble,
    ti.nombre AS nombre_tipo_inmueble

FROM publicaciones p
LEFT JOIN usuarios u ON p.id_user = u.id_usuario
LEFT JOIN contrato c ON p.id_contrato = c.id_contrato
LEFT JOIN estado e ON p.id_estado = e.id_estado
LEFT JOIN tipo_publicacion t ON p.id_tipo_publicacion = t.id_tipo_publicacion
LEFT JOIN imagenes i ON p.id_imagen_publicacion = i.id_imagen
LEFT JOIN vehiculos v ON p.id_vehiculo = v.id_vehiculo
LEFT JOIN tipo_vehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
LEFT JOIN inmuebles m ON p.id_inmueble = m.id_inmueble
LEFT JOIN tipo_inmueble ti ON m.id_tipo_inmueble = ti.id_tipo_inmueble
WHERE p.id_publicacion = $id_publicacion

";



    $result = mysqli_query($DB_conn, $query);

    if (!$result) {
        die("Error en la consulta: " . mysqli_error($DB_conn));
    }

    // Verificar si se encontró la publicación
    if (mysqli_num_rows($result) > 0) {
        $publicacion = mysqli_fetch_assoc($result);
    } else {
        die("Publicación no encontrada.");
    }
} else {
    die("ID de publicación no válido.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Modificar Publicación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
  <h2 class="mb-4">Modificar Publicación</h2>

  <form method="post" enctype="multipart/form-data" action="procesar_modificacion.php">

    <!-- ID de la publicación (oculto) -->
    <input type="hidden" name="id_publicacion" value="<?= htmlspecialchars($publicacion['id_publicacion']) ?>">

    <!-- Usuario (no editable) -->
    <div class="mb-3">
      <label class="form-label">Usuario</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_usuario']) ?> <?= htmlspecialchars($publicacion['apellido_usuario']) ?>" disabled>
    </div>

    <!-- Contrato -->
    <div class="mb-3">
      <label class="form-label">Contrato</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_contrato']) ?>" disabled>
    </div>

    <!-- Estado -->
    <div class="mb-3">
      <label class="form-label">Estado</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_estado']) ?>" disabled>
    </div>

    <!-- Tipo de Publicación -->
    <div class="mb-3">
      <label class="form-label">Tipo de Publicación</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_tipo_publicacion']) ?>" disabled>
    </div>

    <!-- Imagen Actual -->
    <div class="mb-3">
      <label class="form-label">Imagen Actual</label><br>
      <?php if (!empty($publicacion['imagen_blob'])): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($publicacion['imagen_blob']) ?>" 
             alt="Imagen actual" style="max-width: 300px; max-height: 300px;">
      <?php else: ?>
        <p>No hay imagen disponible.</p>
      <?php endif; ?>
    </div>

    <?php if ($publicacion['id_tipo_publicacion'] == 1): ?>
  <div class="mb-3">
    <label for="id_vehiculo" class="form-label">ID Vehículo</label>
    <input type="text" class="form-control" name="id_vehiculo" value="<?= htmlspecialchars($publicacion['id_vehiculo']) ?>" required>
  </div>

  <!-- Mostrar los detalles del vehículo -->
  <div class="mb-3">
    <label for="nombre_vehiculo" class="form-label">Nombre del Vehículo</label>
    <input type="text" class="form-control" name="nombre_vehiculo" value="<?= htmlspecialchars($publicacion['nombre_vehiculo']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="modelo_vehiculo" class="form-label">Modelo</label>
    <input type="text" class="form-control" name="modelo_vehiculo" value="<?= htmlspecialchars($publicacion['modelo_vehiculo']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="kilometraje_vehiculo" class="form-label">Kilometraje</label>
    <input type="text" class="form-control" name="kilometraje_vehiculo" value="<?= htmlspecialchars($publicacion['kilometraje_vehiculo']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="marca_vehiculo" class="form-label">Marca</label>
    <input type="text" class="form-control" name="marca_vehiculo" value="<?= htmlspecialchars($publicacion['marca_vehiculo']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="id_tipo_vehiculo" class="form-label">Tipo de Vehículo</label>
   <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_tipo_vehiculo']) ?>" disabled>

  </div>
<?php endif; ?>


  <?php if ($publicacion['id_tipo_publicacion'] == 2): ?>
  <div class="mb-3">
    <label for="id_inmueble" class="form-label">ID Inmueble</label>
    <input type="text" class="form-control" name="id_inmueble" value="<?= htmlspecialchars($publicacion['id_inmueble']) ?>" required>
  </div>

  <!-- Mostrar los detalles del inmueble -->
  <div class="mb-3">
    <label for="nombre_inmueble" class="form-label">Nombre del Inmueble</label>
    <input type="text" class="form-control" name="nombre_inmueble" value="<?= htmlspecialchars($publicacion['nombre_inmueble']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="km2_inmueble" class="form-label">Metros Cuadrados</label>
    <input type="text" class="form-control" name="km2_inmueble" value="<?= htmlspecialchars($publicacion['km2_inmueble']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="id_tipo_inmueble" class="form-label">Tipo de Inmueble</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_tipo_inmueble']) ?>" disabled>

  </div>
<?php endif; ?>



    

    <!-- Subir nueva imagen -->
    <div class="mb-3">
      <label for="nueva_imagen" class="form-label">Seleccionar nueva imagen</label>
      <input type="file" class="form-control" name="nueva_imagen" id="nueva_imagen" accept="image/*">
    </div>

    <!-- Precio -->
    <div class="mb-3">
      <label for="precio" class="form-label">Precio</label>
      <input type="number" class="form-control" name="precio" value="<?= htmlspecialchars($publicacion['precio']) ?>" required>
    </div>

    <!-- Enviar -->
    <button type="submit" class="btn btn-primary">Actualizar Publicación</button>
  </form>
</div>

</body>
</html>
