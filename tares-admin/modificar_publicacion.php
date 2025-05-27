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
            c.id_contrato AS id_contrato_value,
            c.nombre AS nombre_contrato,
            e.id_estado AS id_estado_value,
            e.nombre AS nombre_estado,
            t.id_tipo_publicacion AS id_tipo_publicacion_value,
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
            ti.id_tipo_inmueble AS id_tipo_inmueble_value,
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

    // Consultas para los desplegables
    $contratos_query = "SELECT id_contrato, nombre FROM contrato";
    $contratos_result = mysqli_query($DB_conn, $contratos_query);
    $contratos = mysqli_fetch_all($contratos_result, MYSQLI_ASSOC);

    $estados_query = "SELECT id_estado, nombre FROM estado";
    $estados_result = mysqli_query($DB_conn, $estados_query);
    $estados = mysqli_fetch_all($estados_result, MYSQLI_ASSOC);

    $tipos_publicacion_query = "SELECT id_tipo_publicacion, nombre FROM tipo_publicacion";
    $tipos_publicacion_result = mysqli_query($DB_conn, $tipos_publicacion_query);
    $tipos_publicacion = mysqli_fetch_all($tipos_publicacion_result, MYSQLI_ASSOC);

    $tipos_inmueble_query = "SELECT id_tipo_inmueble, nombre FROM tipo_inmueble";
    $tipos_inmueble_result = mysqli_query($DB_conn, $tipos_inmueble_query);
    $tipos_inmueble = mysqli_fetch_all($tipos_inmueble_result, MYSQLI_ASSOC);

    // Nueva consulta para tipos de vehículo
    $tipos_vehiculo_query = "SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo";
    $tipos_vehiculo_result = mysqli_query($DB_conn, $tipos_vehiculo_query);
    $tipos_vehiculo = mysqli_fetch_all($tipos_vehiculo_result, MYSQLI_ASSOC);

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
    <h2 class="mb-4">Modificar Publicación</h2>

    <form method="post" enctype="multipart/form-data" action="procesar_modificacion.php">

        <input type="hidden" name="id_publicacion" value="<?= htmlspecialchars($publicacion['id_publicacion']) ?>">
        <input type="hidden" name="id_imagen_publicacion" value="<?= htmlspecialchars($publicacion['id_imagen_publicacion']) ?>">

        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_usuario']) ?> <?= htmlspecialchars($publicacion['apellido_usuario']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label for="id_contrato" class="form-label">Contrato</label>
            <select class="form-select" name="id_contrato" id="id_contrato" required>
                <?php foreach ($contratos as $contrato): ?>
                    <option value="<?= htmlspecialchars($contrato['id_contrato']) ?>"
                        <?= ($contrato['id_contrato'] == $publicacion['id_contrato_value']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($contrato['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="id_estado" class="form-label">Estado</label>
            <select class="form-select" name="id_estado" id="id_estado" required>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= htmlspecialchars($estado['id_estado']) ?>"
                        <?= ($estado['id_estado'] == $publicacion['id_estado_value']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($estado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de Publicación</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($publicacion['nombre_tipo_publicacion']) ?>" disabled>
            <input type="hidden" name="id_tipo_publicacion" value="<?= htmlspecialchars($publicacion['id_tipo_publicacion_value']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Imagen Actual</label><br>
            <?php if (!empty($publicacion['imagen_blob'])): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($publicacion['imagen_blob']) ?>"
                     alt alt="Imagen actual" style="max-width: 300px; max-height: 300px;">
            <?php else: ?>
                <p>No hay imagen disponible.</p>
            <?php endif; ?>
        </div>

        <?php if ($publicacion['id_tipo_publicacion'] == 1): ?>
            <div class="mb-3">
                <label for="nombre_vehiculo" class="form-label">Nombre del Vehículo</label>
                <input type="text" class="form-control" name="nombre_vehiculo" value="<?= htmlspecialchars($publicacion['nombre_vehiculo']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="modelo_vehiculo" class="form-label">Modelo</label>
                <input type="text" class="form-control" name="modelo_vehiculo" value="<?= htmlspecialchars($publicacion['modelo_vehiculo']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="kilometraje_vehiculo" class="form-label">Kilometraje</label>
                <input type="text" class="form-control" name="kilometraje_vehiculo" value="<?= htmlspecialchars($publicacion['kilometraje_vehiculo']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="marca_vehiculo" class="form-label">Marca</label>
                <input type="text" class="form-control" name="marca_vehiculo" value="<?= htmlspecialchars($publicacion['marca_vehiculo']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="id_tipo_vehiculo" class="form-label">Tipo de Vehículo</label>
                <select class="form-select" name="id_tipo_vehiculo" id="id_tipo_vehiculo" required>
                    <?php foreach ($tipos_vehiculo as $tipo_vehiculo): ?>
                        <option value="<?= htmlspecialchars($tipo_vehiculo['id_tipo_vehiculo']) ?>"
                            <?= ($tipo_vehiculo['id_tipo_vehiculo'] == $publicacion['id_tipo_vehiculo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo_vehiculo['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>


        <?php if ($publicacion['id_tipo_publicacion'] == 2): ?>
            <div class="mb-3">
                <label for="nombre_inmueble" class="form-label">Nombre del Inmueble</label>
                <input type="text" class="form-control" name="nombre_inmueble" value="<?= htmlspecialchars($publicacion['nombre_inmueble']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="km2_inmueble" class="form-label">Metros Cuadrados</label>
                <input type="text" class="form-control" name="km2_inmueble" value="<?= htmlspecialchars($publicacion['km2_inmueble']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="id_tipo_inmueble" class="form-label">Tipo de Inmueble</label>
                <select class="form-select" name="id_tipo_inmueble" id="id_tipo_inmueble" required>
                    <?php foreach ($tipos_inmueble as $tipo_inmueble): ?>
                        <option value="<?= htmlspecialchars($tipo_inmueble['id_tipo_inmueble']) ?>"
                            <?= ($tipo_inmueble['id_tipo_inmueble'] == $publicacion['id_tipo_inmueble_value']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo_inmueble['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>


        <div class="mb-3">
            <label for="nueva_imagen" class="form-label">Seleccionar nueva imagen</label>
            <input type="file" class="form-control" name="nueva_imagen" id="nueva_imagen" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" class="form-control" name="precio" value="<?= htmlspecialchars($publicacion['precio']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Publicación</button>
    </form>
</div>

</body>
</html>