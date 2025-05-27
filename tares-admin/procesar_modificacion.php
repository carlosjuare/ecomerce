<?php
include('../db/db.php');

// Verificar si el formulario ha sido enviado por método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Obtener el ID de la publicación que se está modificando
    $id_publicacion = $_POST['id_publicacion'];
    if (!is_numeric($id_publicacion)) {
        die("ID de publicación no válido.");
    }

    // Obtener el precio (siempre se puede modificar)
    $precio = $_POST['precio'];
    if (!is_numeric($precio) || $precio < 0) {
        die("El precio debe ser un número válido.");
    }

    // Obtener los IDs de los desplegables
    $id_contrato = $_POST['id_contrato'];
    $id_estado = $_POST['id_estado'];
    $id_tipo_publicacion = $_POST['id_tipo_publicacion']; // Viene como hidden
    $id_imagen_publicacion_actual = $_POST['id_imagen_publicacion'];

    // ============ ACTUALIZACIÓN DE VEHÍCULO (SI APLICA) ============
    if ($id_tipo_publicacion == 1) {
        $nombre_vehiculo = $_POST['nombre_vehiculo'];
        $modelo_vehiculo = $_POST['modelo_vehiculo'];
        $kilometraje_vehiculo = $_POST['kilometraje_vehiculo'];
        $marca_vehiculo = $_POST['marca_vehiculo'];
        $id_tipo_vehiculo = $_POST['id_tipo_vehiculo'];

        if (empty($nombre_vehiculo) || empty($modelo_vehiculo) || empty($kilometraje_vehiculo) || empty($marca_vehiculo) || empty($id_tipo_vehiculo)) {
            die("Todos los campos del vehículo son obligatorios.");
        }

        $query_vehiculo = "UPDATE vehiculos SET nombre = ?, modelo = ?, kilometraje = ?, marca = ?, id_tipo_vehiculo = ? WHERE id_vehiculo = ?";
        if ($stmt = mysqli_prepare($DB_conn, $query_vehiculo)) {
            mysqli_stmt_bind_param($stmt, "ssdsii", $nombre_vehiculo, $modelo_vehiculo, $kilometraje_vehiculo, $marca_vehiculo, $id_tipo_vehiculo, $_POST['id_vehiculo']);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error al actualizar el vehículo: " . mysqli_error($DB_conn) . "<br>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación del statement para el vehículo: " . mysqli_error($DB_conn) . "<br>";
        }
    }

    // ============ ACTUALIZACIÓN DE INMUEBLE (SI APLICA) ============
    if ($id_tipo_publicacion == 2) {
        $nombre_inmueble = $_POST['nombre_inmueble'];
        $km2_inmueble = $_POST['km2_inmueble'];
        $id_tipo_inmueble = $_POST['id_tipo_inmueble'];

        if (empty($nombre_inmueble) || empty($km2_inmueble) || empty($id_tipo_inmueble)) {
            die("Todos los campos del inmueble son obligatorios.");
        }

        $query_inmueble = "UPDATE inmuebles SET nombre = ?, km2 = ?, id_tipo_inmueble = ? WHERE id_inmueble = ?";
        if ($stmt = mysqli_prepare($DB_conn, $query_inmueble)) {
            mysqli_stmt_bind_param($stmt, "sdii", $nombre_inmueble, $km2_inmueble, $id_tipo_inmueble, $_POST['id_inmueble']);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error al actualizar el inmueble: " . mysqli_error($DB_conn) . "<br>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación del statement para el inmueble: " . mysqli_error($DB_conn) . "<br>";
        }
    }

    // ============ ACTUALIZACIÓN DE IMAGEN (SI SE SUBE UNA NUEVA) ============
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['nueva_imagen']['tmp_name']);

        $query_imagen = "UPDATE imagenes SET imagen = ? WHERE id_imagen = ?";
        if ($stmt_imagen = mysqli_prepare($DB_conn, $query_imagen)) {
            mysqli_stmt_bind_param($stmt_imagen, "bi", $imagen, $id_imagen_publicacion_actual);
            mysqli_stmt_send_long_data($stmt_imagen, 0, $imagen);

            if (!mysqli_stmt_execute($stmt_imagen)) {
                echo "Error al actualizar la imagen: " . mysqli_error($DB_conn) . "<br>";
            }
            mysqli_stmt_close($stmt_imagen);
        } else {
            echo "Error en la preparación del statement para la imagen: " . mysqli_error($DB_conn) . "<br>";
        }
    }

    // ============ ACTUALIZACIÓN DE LA PUBLICACIÓN ============
    $query_publicacion = "UPDATE publicaciones SET precio = ?, id_contrato = ?, id_estado = ? WHERE id_publicacion = ?";
    if ($stmt_publicacion = mysqli_prepare($DB_conn, $query_publicacion)) {
        mysqli_stmt_bind_param($stmt_publicacion, "iiii", $precio, $id_contrato, $id_estado, $id_publicacion);
        if (mysqli_stmt_execute($stmt_publicacion)) {
            header("Location: ../vistageneral/presentacion.php"); // Redirigir tras la actualización
            exit();
        } else {
            echo "Error al actualizar la publicación: " . mysqli_error($DB_conn) . "<br>";
        }
        mysqli_stmt_close($stmt_publicacion);
    } else {
        echo "Error en la preparación del statement para la publicación: " . mysqli_error($DB_conn) . "<br>";
    }
} else {
    // Si se intenta acceder al script directamente sin POST
    header("Location: formulario_modificacion.php?error=metodo"); // Redirigir con un mensaje de error
    exit();
}
?>