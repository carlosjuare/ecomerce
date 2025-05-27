<?php
include('../db/db.php');

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Obtener el tipo de publicación (vehículo o inmueble)
    $tipo_publicacion = $_POST['tipo_publicacion'];

    if (empty($tipo_publicacion)) {
        die("Debe seleccionar un tipo de publicación.");
    }

    // ============ INSERCIÓN DE VEHÍCULO ============
    $id_vehiculo = null; // Definir la variable de id_vehiculo
    if ($tipo_publicacion == '1') { // Si es vehículo
        $nombre_vehiculo = $_POST['nombre_vehiculo'];
        $modelo_vehiculo = $_POST['modelo_vehiculo'];
        $kilometraje = $_POST['kilometraje'];
        $anio_vehiculo = $_POST['anio_vehiculo'];
        $marca_vehiculo = $_POST['marca_vehiculo'];
        $tipo_vehiculo = $_POST['tipo_vehiculo'];

        if (empty($nombre_vehiculo) || empty($modelo_vehiculo) || empty($kilometraje) || empty($anio_vehiculo) || empty($marca_vehiculo) || empty($tipo_vehiculo)) {
            die("Todos los campos del vehículo son obligatorios.");
        }

        $query_vehiculo = "INSERT INTO vehiculos (nombre, modelo, kilometraje, anio, marca, id_tipo_vehiculo)
                           VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($DB_conn, $query_vehiculo)) {
            mysqli_stmt_bind_param($stmt, "ssdiss", $nombre_vehiculo, $modelo_vehiculo, $kilometraje, $anio_vehiculo, $marca_vehiculo, $tipo_vehiculo);

            if (mysqli_stmt_execute($stmt)) {
                $id_vehiculo = mysqli_insert_id($DB_conn); // Obtener el ID del vehículo insertado
            } else {
                echo "Error al registrar el vehículo: " . mysqli_error($DB_conn) . "<br>";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación del statement: " . mysqli_error($DB_conn) . "<br>";
        }
    }

    // ============ INSERCIÓN DE INMUEBLE ============
    $id_inmueble = null; // Definir la variable de id_inmueble
    if ($tipo_publicacion == '2') { // Si es inmueble
        $nombre_inmueble = $_POST['nombre_inmueble'];
        $km2_inmueble = $_POST['km2_inmueble'];
        $ubicacion_inmueble = $_POST['ubicacion_inmueble'];
        $tipo_inmueble = $_POST['tipo_inmueble'];

        if (empty($nombre_inmueble) || empty($km2_inmueble) || empty($ubicacion_inmueble) || empty($tipo_inmueble)) {
            die("Todos los campos del inmueble son obligatorios.");
        }

        $query_inmueble = "INSERT INTO inmuebles (nombre, km2, id_tipo_inmueble, ubicacion)
                           VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($DB_conn, $query_inmueble)) {
            mysqli_stmt_bind_param($stmt, "sdsi", $nombre_inmueble, $km2_inmueble, $tipo_inmueble, $ubicacion_inmueble);

            if (mysqli_stmt_execute($stmt)) {
                $id_inmueble = mysqli_insert_id($DB_conn); // Obtener el ID del inmueble insertado
            } else {
                echo "Error al registrar el inmueble: " . mysqli_error($DB_conn) . "<br>";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación del statement: " . mysqli_error($DB_conn) . "<br>";
        }
    }

    // ============ INSERCIÓN DE IMAGEN ============
    $id_imagen_publicacion = null; // Definir la variable de id_imagen_publicacion
    if (isset($_FILES['imagenes']) && $_FILES['imagenes']['error'][0] === UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['imagenes']['tmp_name'][0]);

        $query_imagen = "INSERT INTO imagenes (imagen) VALUES (?)";
        if ($stmt_imagen = mysqli_prepare($DB_conn, $query_imagen)) {
            mysqli_stmt_bind_param($stmt_imagen, "b", $imagen);
            mysqli_stmt_send_long_data($stmt_imagen, 0, $imagen); // Necesario para blobs

            if (mysqli_stmt_execute($stmt_imagen)) {
                $id_imagen_publicacion = mysqli_insert_id($DB_conn); // Obtener el ID de la imagen
            } else {
                echo "Error al cargar la imagen: " . mysqli_error($DB_conn) . "<br>";
            }

            mysqli_stmt_close($stmt_imagen);
        } else {
            echo "Error al preparar la inserción de la imagen: " . mysqli_error($DB_conn) . "<br>";
        }
    }

    // ============ INSERCIÓN DE PUBLICACIÓN ============
    $id_user = $_SESSION['id_usuario']; // Asumimos que tienes el ID del usuario en la sesión
    $id_contrato = $_POST['contrato']; // El contrato viene del formulario
    $id_estado = 4; // Estado "Producto Efecto" según lo que mencionaste

    $query_publicacion = "INSERT INTO publicaciones (id_tipo_publicacion, id_vehiculo, id_inmueble, precio, id_user, id_contrato, id_estado, id_imagen_publicacion)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt_publicacion = mysqli_prepare($DB_conn, $query_publicacion)) {
        // Vinculamos los parámetros según corresponda
        mysqli_stmt_bind_param($stmt_publicacion, "iiidiiii", $tipo_publicacion, $id_vehiculo, $id_inmueble, $_POST['precio'], $id_user, $id_contrato, $id_estado, $id_imagen_publicacion);

        if (mysqli_stmt_execute($stmt_publicacion)) {
            header("Location: ../vistageneral/presentacion.php");
            exit();
        } else {
            echo "Error al registrar la publicación: " . mysqli_error($DB_conn) . "<br>";
        }

        mysqli_stmt_close($stmt_publicacion);
    } else {
        echo "Error en la preparación del statement para la publicación: " . mysqli_error($DB_conn) . "<br>";
    }
}
?>
