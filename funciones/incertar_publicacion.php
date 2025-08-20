<?php
require_once __DIR__ . '/../core/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que haya usuario logueado
    if (!isset($_SESSION['id_usuario'])) {
        exit("Error: Debes iniciar sesión para crear una publicación.");
    }

    $id_usuario = $_SESSION['id_usuario'];

    $titulo = trim($_POST['titulo']);
    $tipo_publicacion = $_POST['tipo_publicacion'];
    $estado = 'publicado';
    $precio = isset($_POST['precio']) ? (int)$_POST['precio'] : 0;

    // Validación base
    if (!$titulo || !$tipo_publicacion) {
        exit("Faltan datos obligatorios de publicación.");
    }

    $db = new Database();
    $conn = $db->connect();

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // 1. Insertar en publicaciones, ahora con precio y id_usuario real
        $sqlPub = "INSERT INTO publicaciones (id_usuario, titulo, tipo_publicacion, estado_publicacion, precio)
                   VALUES (:id_usuario, :titulo, :tipo, :estado, :precio)";
        $stmtPub = $conn->prepare($sqlPub);
        $stmtPub->execute([
            ':id_usuario' => $id_usuario,
            ':titulo' => $titulo,
            ':tipo' => $tipo_publicacion,
            ':estado' => $estado,
            ':precio' => $precio
        ]);

        $id_publicacion = $conn->lastInsertId();

        if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['tmp_name'][0])) {
            $imagenes = $_FILES['imagenes'];

            $sqlImagen = "INSERT INTO imagenes (id_publicacion, imagen) VALUES (:id_publicacion, :imagen)";
            $stmtImagen = $conn->prepare($sqlImagen);

            foreach ($imagenes['tmp_name'] as $index => $tmpName) {
                if ($imagenes['error'][$index] === UPLOAD_ERR_OK) {
                    $imgData = file_get_contents($tmpName);

                    $stmtImagen->execute([
                        ':id_publicacion' => $id_publicacion,
                        ':imagen' => $imgData
                    ]);
                }
            }
        }

        // 2. Insertar en tabla correspondiente
        if ($tipo_publicacion === 'vehiculo') {
            // Validación mínima para vehículo
            if (!$_POST['marca'] || !$_POST['modelo'] || !$_POST['anio']) {
                exit("Faltan datos del vehículo.");
            }

            $sqlVeh = "INSERT INTO vehiculos (
                id_publicacion, tipo_vehiculo, marca, modelo, anio, kilometros,
                tipo_combustible, transmision, color
            ) VALUES (
                :id_publicacion, :tipo_vehiculo, :marca, :modelo, :anio, :kilometros,
                :combustible, :transmision, :color
            )";

            $stmtVeh = $conn->prepare($sqlVeh);
            $stmtVeh->execute([
                ':id_publicacion' => $id_publicacion,
                ':tipo_vehiculo' => $_POST['tipo_vehiculo'],
                ':marca' => $_POST['marca'],
                ':modelo' => $_POST['modelo'],
                ':anio' => $_POST['anio'],
                ':kilometros' => $_POST['kilometros'] ?? null,
                ':combustible' => $_POST['tipo_combustible'],
                ':transmision' => $_POST['transmision'],
                ':color' => $_POST['color'] ?? null
            ]);
        } elseif ($tipo_publicacion === 'inmueble') {
            // Validación mínima para inmueble
            if (!$_POST['direccion'] || !$_POST['ciudad'] || !$_POST['provincia']) {
                exit("Faltan datos del inmueble.");
            }

            $sqlInm = "INSERT INTO inmuebles (
                id_publicacion, tipo_inmueble, tipo_contrato, direccion, ciudad, provincia,
                superficie_total, superficie_cubierta, ambientes, dormitorios, banios, cochera
            ) VALUES (
                :id_publicacion, :tipo_inmueble, :tipo_contrato, :direccion, :ciudad, :provincia,
                :superficie_total, :superficie_cubierta, :ambientes, :dormitorios, :banios, :cochera
            )";

            $stmtInm = $conn->prepare($sqlInm);
            $stmtInm->execute([
                ':id_publicacion' => $id_publicacion,
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
                ':cochera' => $_POST['cochera'] ?? 0
            ]);
        } else {
            throw new Exception("Tipo de publicación no válido.");
        }

        // Confirmar transacción
        $conn->commit();
        header("Location: ../vistas/main.php");
        exit;

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "❌ Error al guardar publicación: " . $e->getMessage();
    }
} else {
    exit("Acceso denegado.");
}
