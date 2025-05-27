<?php
include('../db/db.php');

// Realizar las consultas
$query_publicacion = "SELECT id_tipo_publicacion, nombre FROM tipo_publicacion";
$result_publicacion = mysqli_query($DB_conn, $query_publicacion);

$query_contrato = "SELECT id_contrato, nombre FROM contrato";
$result_contrato = mysqli_query($DB_conn, $query_contrato);

$query_tipo_vehiculo = "SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo";
$result_tipo_vehiculo = mysqli_query($DB_conn, $query_tipo_vehiculo);

$query_tipo_inmueble = "SELECT id_tipo_inmueble, nombre FROM tipo_inmueble";
$result_tipo_inmueble = mysqli_query($DB_conn, $query_tipo_inmueble);

if (!$result_publicacion || !$result_contrato || !$result_tipo_vehiculo || !$result_tipo_inmueble) {
    die("Error en la consulta: " . mysqli_error($DB_conn));
}

$nombre_usuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario no identificado';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Generar Publicación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <script>
    function mostrarCamposEspecificos() {
      const tipo = document.getElementById('tipo_publicacion').value;

      const camposVehiculo = document.getElementById('campos_vehiculo');
      const camposInmueble = document.getElementById('campos_inmueble');

      const tipoVehiculo = document.getElementById('tipo_vehiculo');
      const tipoInmueble = document.getElementById('tipo_inmueble');

      if (tipo === '1') { // Vehículo
        camposVehiculo.style.display = 'block';
        tipoVehiculo.disabled = false;

        camposInmueble.style.display = 'none';
        tipoInmueble.disabled = true; // ✅ CAMBIO
      } else if (tipo === '2') { // Inmueble
        camposInmueble.style.display = 'block';
        tipoInmueble.disabled = false;

        camposVehiculo.style.display = 'none';
        tipoVehiculo.disabled = true; // ✅ CAMBIO
      } else {
        camposVehiculo.style.display = 'none';
        tipoVehiculo.disabled = true;
        camposInmueble.style.display = 'none';
        tipoInmueble.disabled = true;
      }
    }

    // Ejecutar al cargar la página
    window.addEventListener('DOMContentLoaded', mostrarCamposEspecificos);
  </script>
</head>
<body class="bg-light">
<header class="bg-dark text-white py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <h1 class="m-0">Mi E-commerce</h1>
    
    <div class="d-flex align-items-center gap-3">
    <?php if ($logueado): ?>
      <span>Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></span>

      <?php if ($tipo_usuario == 1): ?>
        <a href="usuario_opciones.php" class="btn btn-outline-light">Opciones de usuario</a>
      <?php elseif ($tipo_usuario == 2): ?>
        <!-- Botón desplegable para administrar publicaciones -->
        <a href="../tares-admin/admin_publicaicones.php" class="btn btn-success">administrar publicaicones</a>
        
        <a href="../tares-admin/formulario_publicaion.php" class="btn btn-success">Generar publicación</a>

      <?php endif; ?>
      <a href="../publicaciones/ver_favs.php" class="btn btn-success">ver favoritos </a>
      <a href="../login/logout.php" class="btn btn-danger">Cerrar sesión</a>
    <?php else: ?>
      <a href="../login/login.html" class="btn btn-outline-light">Iniciar sesión</a>
    <?php endif; ?>
    </div>
  </div>
</header>
<div class="container my-5">
  <h2 class="mb-4">Generar nueva publicación</h2>
  <form method="post" enctype="multipart/form-data" action="cragar_publicaion.php">

    <!-- Tipo de publicación -->
    <div class="mb-3">
      <label for="tipo_publicacion" class="form-label">Tipo de publicación</label>
      <select name="tipo_publicacion" id="tipo_publicacion" class="form-select" onchange="mostrarCamposEspecificos()" required>
        <option value="">Seleccionar</option>
        <?php while ($row = mysqli_fetch_assoc($result_publicacion)): ?>
          <option value="<?= htmlspecialchars($row['id_tipo_publicacion']) ?>"><?= htmlspecialchars($row['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Vehículo -->
    <div id="campos_vehiculo" style="display:none;">
      <h5>Detalles del Vehículo</h5>
      <div class="mb-3">
        <label for="nombre_vehiculo" class="form-label">Nombre</label>
        <input type="text" class="form-control" name="nombre_vehiculo">
      </div>
      <div class="mb-3">
        <label for="modelo_vehiculo" class="form-label">Modelo</label>
        <input type="text" class="form-control" name="modelo_vehiculo">
      </div>
      <div class="mb-3">
        <label for="kilometraje" class="form-label">Kilometraje</label>
        <input type="number" class="form-control" name="kilometraje">
      </div>
      <div class="mb-3">
        <label for="anio_vehiculo" class="form-label">Año</label>
        <input type="number" class="form-control" name="anio_vehiculo">
      </div>
      <div class="mb-3">
        <label for="marca_vehiculo" class="form-label">Marca</label>
        <input type="text" class="form-control" name="marca_vehiculo">
      </div>
      <div class="mb-3">
        <label for="tipo_vehiculo" class="form-label">Tipo de Vehículo</label>
        <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-select" required disabled> <!-- ✅ CAMBIO -->
          <option value="">Seleccionar tipo de vehículo</option>
          <?php while ($row = mysqli_fetch_assoc($result_tipo_vehiculo)): ?>
            <option value="<?= htmlspecialchars($row['id_tipo_vehiculo']) ?>"><?= htmlspecialchars($row['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <!-- Inmueble -->
    <div id="campos_inmueble" style="display:none;">
      <h5>Detalles del Inmueble</h5>
      <div class="mb-3">
        <label for="nombre_inmueble" class="form-label">Nombre</label>
        <input type="text" class="form-control" name="nombre_inmueble">
      </div>
      <div class="mb-3">
        <label for="km2_inmueble" class="form-label">Superficie (km²)</label>
        <input type="number" class="form-control" name="km2_inmueble">
      </div>
      <div class="mb-3">
        <label for="ubicacion_inmueble" class="form-label">Ubicación</label>
        <input type="text" class="form-control" name="ubicacion_inmueble">
      </div>
      <div class="mb-3">
        <label for="tipo_inmueble" class="form-label">Tipo de Inmueble</label>
        <select name="tipo_inmueble" id="tipo_inmueble" class="form-select" required disabled> <!-- ✅ CAMBIO -->
          <option value="">Seleccionar tipo de inmueble</option>
          <?php while ($row = mysqli_fetch_assoc($result_tipo_inmueble)): ?>
            <option value="<?= htmlspecialchars($row['id_tipo_inmueble']) ?>"><?= htmlspecialchars($row['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <!-- Precio -->
    <div class="mb-3">
      <label for="precio" class="form-label">Precio</label>
      <input type="number" class="form-control" name="precio" required>
    </div>

    <!-- Usuario -->
    <div class="mb-3">
      <label class="form-label">Usuario</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_usuario) ?>" disabled>
    </div>

    <!-- Contrato -->
    <div class="mb-3">
      <label for="contrato" class="form-label">Contrato</label>
      <select name="contrato" class="form-select" required>
        <option value="">Seleccionar contrato</option>
        <?php while ($row = mysqli_fetch_assoc($result_contrato)): ?>
          <option value="<?= htmlspecialchars($row['id_contrato']) ?>"><?= htmlspecialchars($row['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Imágenes -->
    <div class="mb-3">
      <label for="imagenes" class="form-label">Seleccionar imágenes</label>
      <input type="file" class="form-control" name="imagenes[]" multiple>
    </div>

    <!-- Enviar -->
    <button type="submit" class="btn btn-primary">Publicar</button>
  </form>
</div>

</body>
</html>
