<?php
session_start();
$logueado = isset($_SESSION['id_usuario']);
$nombre_usuario = $logueado ? $_SESSION['nombre'] : '';
$tipo_usuario  = $logueado ? $_SESSION['tipo_usuario'] : 0;

include('../db/db.php');

// Validar parámetro GET
if (!isset($_GET['tipo'])) {
    echo "Tipo de publicación no especificado.";
    exit;
}
$tipo = $_GET['tipo'];

// Cargar catálogos
$contratos    = $DB_conn->query("SELECT id_contrato, nombre FROM contrato")->fetch_all(MYSQLI_ASSOC);
$tiposVehiculo = $DB_conn->query("SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo")->fetch_all(MYSQLI_ASSOC);
$tiposInmueble = $DB_conn->query("SELECT id_tipo_inmueble, nombre FROM tipo_inmueble")->fetch_all(MYSQLI_ASSOC);

// Determinar tabla y campo según tipo
if ($tipo === 'vehiculo') {
    $id_tipo_publicacion = 1;
    $tabla     = 'vehiculos';
    $campo_id  = 'id_vehiculo';
} elseif ($tipo === 'inmueble') {
    $id_tipo_publicacion = 2;
    $tabla     = 'inmuebles';
    $campo_id = 'id_inmueble';
} else {
    echo "Tipo de publicación inválido.";
    exit;
}

// Obtener precio mínimo desde la BD
$stmtMin = $DB_conn->prepare(
    "SELECT MIN(precio) FROM publicaciones WHERE id_tipo_publicacion = ?"
);
$stmtMin->bind_param("i", $id_tipo_publicacion);
$stmtMin->execute();
$stmtMin->bind_result($precioMin);
$stmtMin->fetch();
$stmtMin->close();

// Recoger filtros enviados por GET
$contrato_sel    = $_GET['contrato']       ?? '';
$tipo_veh_sel    = $_GET['tipo_vehiculo']  ?? '';
$tipo_inm_sel    = $_GET['tipo_inmueble']  ?? '';
$precio_max      = $_GET['precio_max']    ?? '';    // Cadena para validar
$nombre_item_sel = $_GET['nombre_item']    ?? '';

// Construir cláusula WHERE dinámica
$whereSQL = ["p.id_tipo_publicacion = ?"];
$params   = [$id_tipo_publicacion];
$types    = "i";

// Filtro de contrato
if ($contrato_sel !== '') {
    $whereSQL[] = "p.id_contrato = ?";
    $types    .= "i";
    $params[]   = (int)$contrato_sel;
}

// Filtro de tipo de vehículo o de inmueble
if ($id_tipo_publicacion === 1 && $tipo_veh_sel !== '') {
    $whereSQL[] = "t.id_tipo_vehiculo = ?";
    $types    .= "i";
    $params[]   = (int)$tipo_veh_sel;
}
if ($id_tipo_publicacion === 2 && $tipo_inm_sel !== '') {
    $whereSQL[] = "t.id_tipo_inmueble = ?";
    $types    .= "i";
    $params[]   = (int)$tipo_inm_sel;
}

// Filtro de precio máximo: solo si el campo no está vacío
if (trim($precio_max) !== '') {
    $whereSQL[] = "p.precio BETWEEN ? AND ?";
    $types    .= "ii";
    $params[]   = (int)$precioMin;
    $params[]   = (int)$precio_max;
}

// Filtro por nombre_item exacto: solo si se ingresó texto
if (trim($nombre_item_sel) !== '') {
    $whereSQL[] = "t.nombre = ?";
    $types    .= "s";
    $params[]   = $nombre_item_sel;
}

// Consulta principal
$sql = sprintf(
    "SELECT p.*, c.nombre AS nombre_contrato, e.nombre AS nombre_estado, t.nombre AS nombre_item
    FROM publicaciones p
    LEFT JOIN contrato c ON p.id_contrato = c.id_contrato
    LEFT JOIN estado e ON p.id_estado = e.id_estado
    LEFT JOIN %s t ON p.%s = t.%s
    WHERE %s",
    $tabla,
    $campo_id,
    $campo_id,
    implode(' AND ', $whereSQL)
);

$stmt = $DB_conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicaciones de <?= htmlspecialchars(ucfirst($tipo)) ?></title>
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

  <div class="container mt-5">
    <h2 class="mb-4">Publicaciones de <?= htmlspecialchars(ucfirst($tipo)) ?></h2>
    <div class="row">
      <div class="col-md-3">
        <form method="GET" action="vista_publicaciones.php">
          <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
          <div class="border p-3 mb-4">
            <h4>Filtros</h4>

            <div class="mb-3">
              <label for="contrato" class="form-label">Contrato</label>
              <select id="contrato" name="contrato" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($contratos as $c): ?>
                  <option value="<?= $c['id_contrato'] ?>" <?= ($contrato_sel == $c['id_contrato']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if ($id_tipo_publicacion === 1): ?>
              <div class="mb-3">
                <label for="tipo_vehiculo" class="form-label">Tipo de Vehículo</label>
                <select id="tipo_vehiculo" name="tipo_vehiculo" class="form-select">
                  <option value="">Todos</option>
                  <?php foreach ($tiposVehiculo as $tv): ?>
                    <option value="<?= $tv['id_tipo_vehiculo'] ?>" <?= ($tipo_veh_sel == $tv['id_tipo_vehiculo']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($tv['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="tipo_inmueble" class="form-label">Tipo de Inmueble</label>
                <select id="tipo_inmueble" name="tipo_inmueble" class="form-select">
                  <option value="">Todos</option>
                  <?php foreach ($tiposInmueble as $ti): ?>
                    <option value="<?= $ti['id_tipo_inmueble'] ?>" <?= ($tipo_inm_sel == $ti['id_tipo_inmueble']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($ti['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label for="nombre_item" class="form-label">Nombre del ítem</label>
              <input type="text" id="nombre_item" name="nombre_item" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($nombre_item_sel) ?>">
            </div>

            <div class="mb-3">
              <label for="precio_max" class="form-label">Precio máximo (desde <?= htmlspecialchars($precioMin) ?>)</label>
              <input type="number" id="precio_max" name="precio_max" class="form-control" placeholder="<?= htmlspecialchars($precioMin) ?>" value="<?= htmlspecialchars($precio_max) ?>">
            </div>

            <button type="submit" class="btn btn-primary w-100">Aplicar filtros</button>
          </div>
        </form>
      </div>

      <div class="col-md-9">
        <?php if ($result && $result->num_rows > 0): ?>
          <div class="row g-4">
           <?php while ($row = $result->fetch_assoc()): ?>
    <div class="col-md-6">
        <div class="card h-100">
            <?php
            $id_pub   = ($tipo === 'vehiculo') ? $row['id_vehiculo'] : $row['id_inmueble'];
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

            // Verificar si la publicación ya está en los favoritos del usuario logueado
            $esFavorito = false;
            if ($logueado) {
                $stmt_fav_check = $DB_conn->prepare("SELECT id_publicacion FROM favoritos WHERE id_publicacion = ? AND id_usuario = ?");
                $stmt_fav_check->bind_param("ii", $row['id_publicacion'], $_SESSION['id_usuario']);
                $stmt_fav_check->execute();
                $stmt_fav_check->store_result();
                if ($stmt_fav_check->num_rows > 0) {
                    $esFavorito = true;
                }
                $stmt_fav_check->close();
            }
            ?>
            <a href="<?= htmlspecialchars($url_det) ?>"><img src="<?= $src ?>" class="card-img-top" alt=""></a>
            <div class="card-body">
                <h5 class="card-title">$<?= htmlspecialchars($row['precio']) ?></h5>
                <p class="card-text">Contrato: <?= htmlspecialchars($row['nombre_contrato']) ?></p>
                <p class="card-text">Estado: <?= htmlspecialchars($row['nombre_estado']) ?></p>
                <p class="card-text"><?= htmlspecialchars($row['nombre_item']) ?></p>

                <?php if ($logueado): ?>
                    <button type="button" class="btn btn-sm btn-outline-<?= $esFavorito ? 'danger' : 'success' ?> mt-2 agregar-favorito-btn"
                        data-id-publicacion="<?= htmlspecialchars($row['id_publicacion']) ?>"
                        data-id-usuario="<?= htmlspecialchars($_SESSION['id_usuario']) ?>"
                        data-es-favorito="<?= $esFavorito ? 'true' : 'false' ?>">
                        <?= $esFavorito ? 'Eliminar de Favoritos' : 'Agregar a Favoritos' ?>
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                            onclick="window.location.href='../login/login.html'">
                        Iniciar Sesión para Favoritos
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php endwhile; ?>
        </div>
      <?php else: ?>
        <p>No se encontraron publicaciones.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
 <script>
  document.addEventListener('DOMContentLoaded', function() {
    const botonesFavorito = document.querySelectorAll('.agregar-favorito-btn');

    botonesFavorito.forEach(button => {
        button.addEventListener('click', function() {
            const idPublicacion = this.dataset.idPublicacion;
            const idUsuario = this.dataset.idUsuario;
            const esFavorito = this.dataset.esFavorito === 'true'; // Convertir a booleano

            let accion = 'agregar';
            let nuevoTexto = 'Eliminar de Favoritos';
            let nuevaClase = 'btn-outline-danger';
            let nuevoDataEsFavorito = 'true';
            let mensaje = 'agregada';

            if (esFavorito) {
                accion = 'eliminar';
                nuevoTexto = 'Agregar a Favoritos';
                nuevaClase = 'btn-outline-success';
                nuevoDataEsFavorito = 'false';
                mensaje = 'eliminada';
            }

            fetch('agregar_favorito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_publicacion=${encodeURIComponent(idPublicacion)}&id_usuario=${encodeURIComponent(idUsuario)}&accion=${encodeURIComponent(accion)}`,
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                alert(`Publicación ${mensaje} de tus favoritos.`);
                button.textContent = nuevoTexto;
                button.className = `btn btn-sm ${nuevaClase} mt-2 agregar-favorito-btn`;
                button.dataset.esFavorito = nuevoDataEsFavorito;
            })
            .catch(error => {
                console.error('Error al actualizar favoritos:', error);
                alert('Ocurrió un error al actualizar favoritos.');
            });
        });
    });
});
  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
