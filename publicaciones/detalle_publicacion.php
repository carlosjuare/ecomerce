<?php
session_start(); // Asegura que la sesión esté activa

$logueado = isset($_SESSION['id_usuario']);
$nombre_usuario = $logueado ? $_SESSION['nombre'] : '';
$tipo_usuario = $logueado ? $_SESSION['tipo_usuario'] : 0;

include('../db/db.php');

// Validar parámetros GET
if (!isset($_GET['tipo']) || !isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "Tipo de publicación o ID no válido.";
    exit;
}

$tipo = $_GET['tipo'];
$id_publicacion = $_GET['id'];

// Determinar tabla y campos según el tipo
if ($tipo === 'vehiculo') {
    $id_tipo_publicacion = 1;
    $tabla = 'vehiculos';
    $campo_id = 'id_vehiculo';
} elseif ($tipo === 'inmueble') {
    $id_tipo_publicacion = 2;
    $tabla = 'inmuebles';
    $campo_id = 'id_inmueble';
} else {
    echo "Tipo de publicación inválido.";
    exit;
}

// Consulta principal
$query = "
    SELECT p.*, 
           c.nombre AS nombre_contrato, 
           e.nombre AS nombre_estado,
           t.nombre AS nombre_item,
           u.nombre AS nombre_usuario,
           u.apellido AS apellido_usuario,
           u.gmail AS gmail_usuario,
           u.telefono AS telefono_usuario,
           p.id_imagen_publicacion
    FROM publicaciones p
    LEFT JOIN contrato c ON p.id_contrato = c.id_contrato
    LEFT JOIN estado e ON p.id_estado = e.id_estado
    LEFT JOIN $tabla t ON p.$campo_id = t.$campo_id
    LEFT JOIN usuarios u ON p.id_user = u.id_usuario
    WHERE p.id_tipo_publicacion = ? AND p.$campo_id = ?
";
$stmt = $DB_conn->prepare($query);
$stmt->bind_param("ii", $id_tipo_publicacion, $id_publicacion);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $imagen_publicacion = $row['id_imagen_publicacion'];
} else {
    echo "Publicación no encontrada.";
    exit;
}

// Obtener imagen
$query_imagen = "SELECT imagen FROM imagenes WHERE id_imagen = ?";
$stmt_imagen = $DB_conn->prepare($query_imagen);
$stmt_imagen->bind_param("i", $imagen_publicacion);
$stmt_imagen->execute();
$stmt_imagen->store_result();

if ($stmt_imagen->num_rows > 0) {
    $stmt_imagen->bind_result($imagen);
    $stmt_imagen->fetch();
    $imagen_base64 = base64_encode($imagen);
} else {
    $imagen_base64 = null;
}

// Datos adicionales
if ($tipo === 'vehiculo') {
    $vehiculo_query = "SELECT nombre, modelo, kilometraje, anio, marca FROM vehiculos WHERE id_vehiculo = ?";
    $vehiculo_stmt = $DB_conn->prepare($vehiculo_query);
    $vehiculo_stmt->bind_param("i", $id_publicacion);
    $vehiculo_stmt->execute();
    $vehiculo_result = $vehiculo_stmt->get_result();

    if ($vehiculo_result->num_rows > 0) {
        $vehiculo_row = $vehiculo_result->fetch_assoc();
        $nombre_vehiculo = $vehiculo_row['nombre'];
        $modelo_vehiculo = $vehiculo_row['modelo'];
        $kilometraje_vehiculo = $vehiculo_row['kilometraje'];
        $anio_vehiculo = $vehiculo_row['anio'];
        $marca_vehiculo = $vehiculo_row['marca'];
    } else {
        $nombre_vehiculo = $modelo_vehiculo = $kilometraje_vehiculo = $anio_vehiculo = $marca_vehiculo = "No disponible";
    }
}

if ($tipo === 'inmueble') {
    $inmueble_query = "SELECT nombre, km2 FROM inmuebles WHERE id_inmueble = ?";
    $inmueble_stmt = $DB_conn->prepare($inmueble_query);
    $inmueble_stmt->bind_param("i", $id_publicacion);
    $inmueble_stmt->execute();
    $inmueble_result = $inmueble_stmt->get_result();

    if ($inmueble_result->num_rows > 0) {
        $inmueble_row = $inmueble_result->fetch_assoc();
        $nombre_inmueble = $inmueble_row['nombre'];
        $km2_inmueble = $inmueble_row['km2'];
    } else {
        $nombre_inmueble = $km2_inmueble = "No disponible";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Publicación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

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

<div class="container mt-5">
    <h2 class="mb-4">Detalle de la Publicación</h2>

    <div class="row">
        <div class="col-md-6">
            <?php if ($imagen_base64): ?>
                <img src="data:image/jpeg;base64,<?php echo $imagen_base64; ?>" class="img-fluid" alt="Imagen de la publicación">
            <?php else: ?>
                <img src="https://via.placeholder.com/600x400" class="img-fluid" alt="Imagen no disponible">
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <h5>Precio: $<?php echo $row['precio']; ?></h5>
            <p><strong>Contrato:</strong> <?php echo $row['nombre_contrato']; ?></p>
            <p><strong>Estado:</strong> <?php echo $row['nombre_estado']; ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($row['nombre_item']); ?></p>

            <?php if ($tipo === 'vehiculo'): ?>
                <p><strong>Marca:</strong> <?php echo $marca_vehiculo; ?></p>
                <p><strong>Modelo:</strong> <?php echo $modelo_vehiculo; ?></p>
                <p><strong>Año:</strong> <?php echo $anio_vehiculo; ?></p>
                <p><strong>Kilometraje:</strong> <?php echo $kilometraje_vehiculo; ?></p>
            <?php elseif ($tipo === 'inmueble'): ?>
                <p><strong>Nombre Inmueble:</strong> <?php echo $nombre_inmueble; ?></p>
                <p><strong>Área:</strong> <?php echo $km2_inmueble; ?> m²</p>
            <?php endif; ?>

            <hr>
            <h5>Información del Publicador</h5>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($row['nombre_usuario'] . ' ' . $row['apellido_usuario']); ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($row['gmail_usuario']); ?>"><?php echo htmlspecialchars($row['gmail_usuario']); ?></a></p>
            <p><strong>Teléfono:</strong> <a href="tel:<?php echo htmlspecialchars($row['telefono_usuario']); ?>"><?php echo htmlspecialchars($row['telefono_usuario']); ?></a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
