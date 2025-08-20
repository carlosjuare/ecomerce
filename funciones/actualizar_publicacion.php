<?php
require_once __DIR__ . '/../core/db.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    exit("Error: Debes iniciar sesión.");
}

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id_publicacion'])) {
        exit("Error: Falta el ID de publicación.");
    }

    $id_publicacion = (int)$_POST['id_publicacion'];
    $titulo = trim($_POST['titulo']);
    $tipo_publicacion = $_POST['tipo_publicacion'];
    $precio = isset($_POST['precio']) ? (int)$_POST['precio'] : 0;

    // Validar estado de publicación
    $estado_publicacion = $_POST['estado_publicacion'] ?? 'publicado';
    $estados_validos = ['publicado', 'oculto'];
    if (!in_array($estado_publicacion, $estados_validos)) {
        exit("Estado de publicación no válido.");
    }

    // Validación mínima
    if (!$titulo || !$tipo_publicacion) {
        exit("Faltan datos obligatorios.");
    }

    $db = new Database();
    $conn = $db->connect();

    try {
        // Verificar que la publicación pertenezca al usuario
        $checkSql = "SELECT id_usuario FROM publicaciones WHERE id_publicacion = :id_publicacion";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->execute([':id_publicacion' => $id_publicacion]);
        $publicacion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$publicacion || $publicacion['id_usuario'] != $id_usuario) {
            exit("No tienes permisos para modificar esta publicación.");
        }

        $conn->beginTransaction();

        // Actualizar publicación base
        $sqlUpdate = "UPDATE publicaciones
                      SET titulo = :titulo, tipo_publicacion = :tipo, precio = :precio, estado_publicacion = :estado
                      WHERE id_publicacion = :id";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':titulo' => $titulo,
            ':tipo' => $tipo_publicacion,
            ':precio' => $precio,
            ':estado' => $estado_publicacion,
            ':id' => $id_publicacion
        ]);

        // Actualizar datos específicos según el tipo
        if ($tipo_publicacion === 'vehiculo') {
            $sqlVeh = "UPDATE vehiculos SET
                        tipo_vehiculo = :tipo_vehiculo,
                        marca = :marca,
                        modelo = :modelo,
                        anio = :anio,
                        kilometros = :kilometros,
                        tipo_combustible = :combustible,
                        transmision = :transmision,
                        color = :color
                       WHERE id_publicacion = :id";

            $stmtVeh = $conn->prepare($sqlVeh);
            $stmtVeh->execute([
                ':tipo_vehiculo' => $_POST['tipo_vehiculo'],
                ':marca' => $_POST['marca'],
                ':modelo' => $_POST['modelo'],
                ':anio' => $_POST['anio'],
                ':kilometros' => $_POST['kilometros'] ?? null,
                ':combustible' => $_POST['tipo_combustible'],
                ':transmision' => $_POST['transmision'],
                ':color' => $_POST['color'] ?? null,
                ':id' => $id_publicacion
            ]);
        } elseif ($tipo_publicacion === 'inmueble') {
            $sqlInm = "UPDATE inmuebles SET
                        tipo_inmueble = :tipo_inmueble,
                        tipo_contrato = :tipo_contrato,
                        direccion = :direccion,
                        ciudad = :ciudad,
                        provincia = :provincia,
                        superficie_total = :superficie_total,
                        superficie_cubierta = :superficie_cubierta,
                        ambientes = :ambientes,
                        dormitorios = :dormitorios,
                        banios = :banios,
                        cochera = :cochera
                       WHERE id_publicacion = :id";

            $stmtInm = $conn->prepare($sqlInm);
            $stmtInm->execute([
                ':tipo_inmueble' => $_POST['tipo_inmueble'],
                ':tipo_contrato' => $_POST['tipo_contrato'],
                ':direccion' => $_POST['direccion'],
                ':ciudad' => $_POST['ciudad'],
                ':provincia' => $_POST['provincia'],
                ':superficie_total' => $_POST['superficie_total'] ?? null,
                ':superficie_cubierta' => $_POST['superficie_cubierta'] ?? null,
                ':ambientes' => $_POST['ambientes'] ?? null,
                ':dormitorios' => $_POST['dormitorios'] ?? null,
                ':banios' => $_POST['banios'] ?? null,
                ':cochera' => $_POST['cochera'] ?? 0,
                ':id' => $id_publicacion
            ]);
        }

        // TODO: Lógica para actualizar imagen, si es necesario

        $conn->commit();
        header("Location: ../vistas/gestionar_publicaciones.php?mensaje=actualizado");
        exit;

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "❌ Error al actualizar publicación: " . $e->getMessage();
    }
} else {
    exit("Acceso denegado.");
}
