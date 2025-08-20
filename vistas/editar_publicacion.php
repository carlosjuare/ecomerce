<?php
require_once __DIR__ . '/../core/db.php';
include 'header.php';


if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_publicacion = $_GET['id'] ?? null;
if (!$id_publicacion || !is_numeric($id_publicacion)) {
    exit("ID inválido");
}

$db = new Database();
$conn = $db->connect();

// Obtener publicación
$sql = "
    SELECT p.*, v.*, i.*, img.imagen
    FROM publicaciones p
    LEFT JOIN vehiculos v ON v.id_publicacion = p.id_publicacion
    LEFT JOIN inmuebles i ON i.id_publicacion = p.id_publicacion
    LEFT JOIN (
        SELECT id_publicacion, imagen 
        FROM imagenes 
        WHERE id_publicacion = :id
        LIMIT 1
    ) img ON img.id_publicacion = p.id_publicacion
    WHERE p.id_publicacion = :id
";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_publicacion]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    exit("Publicación no encontrada");
}
?>

<h2>Editar Publicación</h2>

<form action="../funciones/actualizar_publicacion.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>">

    <label>Título:</label>
    <input type="text" name="titulo" value="<?= htmlspecialchars($data['titulo']) ?>" required><br>

    <label>Precio:</label>
    <input type="number" name="precio" value="<?= htmlspecialchars($data['precio']) ?>" required><br>

    <label>Tipo de publicación:</label>
    <input type="text" name="tipo_publicacion" value="<?= htmlspecialchars($data['tipo_publicacion']) ?>" readonly><br>
<label>Estado de la publicación:</label>
    <select name="estado_publicacion" required>
        <option value="publicado" <?= $data['estado_publicacion'] === 'publicado' ? 'selected' : '' ?>>Publicado</option>
        <option value="oculto" <?= $data['estado_publicacion'] === 'oculto' ? 'selected' : '' ?>>Oculto</option>
    </select><br>
    <?php if ($data['tipo_publicacion'] === 'vehiculo'): ?>
        <h3>Datos del Vehículo</h3>
        <label>Tipo:</label><input type="text" name="tipo_vehiculo" value="<?= $data['tipo_vehiculo'] ?>"><br>
        <label>Marca:</label><input type="text" name="marca" value="<?= $data['marca'] ?>"><br>
        <label>Modelo:</label><input type="text" name="modelo" value="<?= $data['modelo'] ?>"><br>
        <label>Año:</label><input type="number" name="anio" value="<?= $data['anio'] ?>"><br>
        <label>Kilómetros:</label><input type="number" name="kilometros" value="<?= $data['kilometros'] ?>"><br>
        <label>Combustible:</label><input type="text" name="tipo_combustible" value="<?= $data['tipo_combustible'] ?>"><br>
        <label>Transmisión:</label><input type="text" name="transmision" value="<?= $data['transmision'] ?>"><br>
        <label>Color:</label><input type="text" name="color" value="<?= $data['color'] ?>"><br>
    <?php elseif ($data['tipo_publicacion'] === 'inmueble'): ?>
        <h3>Datos del Inmueble</h3>
        <label>Tipo:</label><input type="text" name="tipo_inmueble" value="<?= $data['tipo_inmueble'] ?>"><br>
        <label>Contrato:</label><input type="text" name="tipo_contrato" value="<?= $data['tipo_contrato'] ?>"><br>
        <label>Dirección:</label><input type="text" name="direccion" value="<?= $data['direccion'] ?>"><br>
        <label>Ciudad:</label><input type="text" name="ciudad" value="<?= $data['ciudad'] ?>"><br>
        <label>Provincia:</label><input type="text" name="provincia" value="<?= $data['provincia'] ?>"><br>
        <label>Sup. total:</label><input type="number" name="superficie_total" value="<?= $data['superficie_total'] ?>"><br>
        <label>Sup. cubierta:</label><input type="number" name="superficie_cubierta" value="<?= $data['superficie_cubierta'] ?>"><br>
        <label>Ambientes:</label><input type="number" name="ambientes" value="<?= $data['ambientes'] ?>"><br>
        <label>Dormitorios:</label><input type="number" name="dormitorios" value="<?= $data['dormitorios'] ?>"><br>
        <label>Baños:</label><input type="number" name="banios" value="<?= $data['banios'] ?>"><br>
        <label>Cochera:</label>
        <select name="cochera">
            <option value="1" <?= $data['cochera'] == 1 ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= $data['cochera'] == 0 ? 'selected' : '' ?>>No</option>
        </select><br>
    <?php endif; ?>

    <h4>Imagen actual:</h4>
    <?php if ($data['imagen']): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($data['imagen']) ?>" width="150"><br>
    <?php else: ?>
        <p>[Sin imagen]</p>
    <?php endif; ?>

    <label>Cambiar imagen:</label>
    <input type="file" name="nueva_imagen"><br><br>

    <input type="submit" value="Actualizar">
</form>
