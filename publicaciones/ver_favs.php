<?php
session_start();
$logueado       = isset($_SESSION['id_usuario']);
$nombre_usuario = $logueado ? $_SESSION['nombre'] : '';
$tipo_usuario    = $logueado ? $_SESSION['tipo_usuario'] : 0;
$id_usuario      = $logueado ? $_SESSION['id_usuario'] : 0;

include('../db/db.php');

// Cargar catálogos (estos los mantenemos igual)
$contratos     = $DB_conn->query("SELECT id_contrato, nombre FROM contrato")->fetch_all(MYSQLI_ASSOC);
$tiposVehiculo = $DB_conn->query("SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo")->fetch_all(MYSQLI_ASSOC);
$tiposInmueble = $DB_conn->query("SELECT id_tipo_inmueble, nombre FROM tipo_inmueble")->fetch_all(MYSQLI_ASSOC);
$estados       = $DB_conn->query("SELECT id_estado, nombre FROM estado")->fetch_all(MYSQLI_ASSOC);

// Filtros (estos también los mantenemos, aunque su aplicación cambiará ligeramente)
$precio_min        = $_GET['precio_min'] ?? '';
$precio_max        = $_GET['precio_max'] ?? '';
$id_contrato_filtro = $_GET['id_contrato'] ?? '';
$id_estado_filtro  = $_GET['id_estado'] ?? '';
$tipo_filtro       = $_GET['tipo'] ?? '';

// Consulta principal MODIFICADA para obtener solo las publicaciones favoritas del usuario
$sql = "
    SELECT p.*, c.nombre AS nombre_contrato, e.nombre AS nombre_estado,
           COALESCE(v.nombre, i.nombre) AS nombre_item,
           COALESCE(v.id_vehiculo, i.id_inmueble) AS id_item,
           CASE
               WHEN v.id_vehiculo IS NOT NULL THEN 'vehiculo'
               WHEN i.id_inmueble IS NOT NULL THEN 'inmueble'
               ELSE 'desconocido'
           END AS tipo
    FROM favoritos f
    INNER JOIN publicaciones p ON f.id_publicacion = p.id_publicacion
    LEFT JOIN contrato c ON p.id_contrato = c.id_contrato
    LEFT JOIN estado e ON p.id_estado = e.id_estado
    LEFT JOIN vehiculos v ON p.id_vehiculo = v.id_vehiculo
    LEFT JOIN inmuebles i ON p.id_inmueble = i.id_inmueble
    WHERE f.id_usuario = ?
";

$params = [$id_usuario];
$types = "i";

// Aplicación de los filtros (ahora se aplican a las publicaciones favoritas)
if ($precio_min !== '') {
    $sql .= " AND p.precio >= ?";
    $params[] = $precio_min;
    $types .= "d";
}
if ($precio_max !== '') {
    $sql .= " AND p.precio <= ?";
    $params[] = $precio_max;
    $types .= "d";
}
if ($id_contrato_filtro !== '') {
    $sql .= " AND p.id_contrato = ?";
    $params[] = $id_contrato_filtro;
    $types .= "i";
}
if ($id_estado_filtro !== '') {
    $sql .= " AND p.id_estado = ?";
    $params[] = $id_estado_filtro;
    $types .= "i";
}
if ($tipo_filtro !== '') {
    if ($tipo_filtro === 'vehiculo') {
        $sql .= " AND v.id_vehiculo IS NOT NULL";
    } elseif ($tipo_filtro === 'inmueble') {
        $sql .= " AND i.id_inmueble IS NOT NULL";
    }
}

$stmt = $DB_conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Publicaciones Favoritas</title>
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
    <h2 class="mb-4">Mis Publicaciones Favoritas</h2>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Precio Mínimo</label>
            <input type="number" name="precio_min" class="form-control" value="<?= htmlspecialchars($precio_min) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Precio Máximo</label>
            <input type="number" name="precio_max" class="form-control" value="<?= htmlspecialchars($precio_max) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Contrato</label>
            <select name="id_contrato" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($contratos as $c): ?>
                    <option value="<?= $c['id_contrato'] ?>" <?= $id_contrato_filtro == $c['id_contrato'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="id_estado" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e['id_estado'] ?>" <?= $id_estado_filtro == $e['id_estado'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select">
                <option value="">Todos</option>
                <option value="vehiculo" <?= $tipo_filtro === 'vehiculo' ? 'selected' : '' ?>>Vehículo</option>
                <option value="inmueble" <?= $tipo_filtro === 'inmueble' ? 'selected' : '' ?>>Inmueble</option>
            </select>
        </div>
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary mt-2">Aplicar filtros</button>
        </div>
    </form>

    <div class="row">
        <div class="col-12">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="row g-4">
              <?php while ($row = $result->fetch_assoc()): ?>
    <div class="col-md-6">
        <div class="card h-100">
            <?php
            $tipo = $row['tipo'];
            $id_pub = $row['id_item'];
            $url_det = "detalle_publicacion.php?tipo=$tipo&id=$id_pub";

            // Cargar imagen (tu código existente para cargar la imagen)
            $stmt_img = $DB_conn->prepare("SELECT imagen FROM imagenes WHERE id_imagen = ?");
            $stmt_img->bind_param("i", $row['id_imagen_publicacion']);
            $stmt_img->execute();
            $stmt_img->store_result();
            if ($stmt_img->num_rows) {
                $stmt_img->bind_result($binImg);
                $stmt_img->fetch();
                $src = "data:image/jpeg;base64," . base64_encode($binImg);
            } else {
                $src = "https://via.placeholder.com/150";
            }
            $stmt_img->close();
            ?>
            <a href="<?= htmlspecialchars($url_det) ?>"><img src="<?= $src ?>" class="card-img-top" alt=""></a>
            <div class="card-body">
                <h5 class="card-title">$<?= htmlspecialchars($row['precio']) ?></h5>
                <p class="card-text">Contrato: <?= htmlspecialchars($row['nombre_contrato']) ?></p>
                <p class="card-text">Estado: <?= htmlspecialchars($row['nombre_estado']) ?></p>
                <p class="card-text"><?= htmlspecialchars($row['nombre_item']) ?></p>
                <p class="card-text">Tipo: <?= htmlspecialchars(ucfirst($tipo)) ?></p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    
                    <button type="button" class="btn btn-sm btn-danger eliminar-favorito-btn"
                            data-id-publicacion="<?= htmlspecialchars($row['id_publicacion']) ?>"
                            data-id-usuario="<?= htmlspecialchars($id_usuario) ?>">
                        Eliminar de Favoritos
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No tienes publicaciones marcadas como favoritas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const botonesEliminarFavorito = document.querySelectorAll('.eliminar-favorito-btn');

        botonesEliminarFavorito.forEach(button => {
            button.addEventListener('click', function() {
                const idPublicacion = this.dataset.idPublicacion;
                const idUsuario = this.dataset.idUsuario;

                fetch('../publicaciones/agregar_favorito.php', { // Asegúrate de que la ruta al script sea correcta
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=eliminar&id_publicacion=${encodeURIComponent(idPublicacion)}&id_usuario=${encodeURIComponent(idUsuario)}`,
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    alert(data); // Muestra el mensaje del servidor
                    // Opcional: Recargar la página para actualizar la lista de favoritos
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error al eliminar de favoritos:', error);
                    alert('Ocurrió un error al intentar eliminar de favoritos.');
                });
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>