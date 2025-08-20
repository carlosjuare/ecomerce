<?php include 'header.php'; ?>
<form action="../funciones/incertar_publicacion.php" method="POST" enctype="multipart/form-data">
  <h3>Datos de la publicación</h3>
  
  <label>Título:</label>
  <input type="text" name="titulo" required><br>

  <label>Precio:</label>
  <input type="number" name="precio" min="0" step="1" required><br>

  <label>Tipo de publicación:</label>
  <select name="tipo_publicacion" id="tipo_publicacion" onchange="mostrarCampos()" required>
    <option value="">Seleccionar</option>
    <option value="vehiculo">Vehículo</option>
    <option value="inmueble">Inmueble</option>
  </select><br>

  <!-- VEHICULO -->
  <div id="vehiculo_fields" style="display:none;">
    <h4>Datos del vehículo</h4>
    <label>Tipo de vehículo:</label>
    <select name="tipo_vehiculo" required>
      <option value="auto">Auto</option>
      <option value="camioneta">Camioneta</option>
      <option value="moto">Moto</option>
      <option value="utilitario">Utilitario</option>
      <option value="camión">Camión</option>
      <option value="otro">Otro</option>
    </select><br>

    <label>Marca:</label><input type="text" name="marca" required><br>
    <label>Modelo:</label><input type="text" name="modelo" required><br>
    <label>Año:</label><input type="number" name="anio" required><br>
    <label>Kilómetros:</label><input type="number" name="kilometros"><br>

    <label>Tipo de combustible:</label>
    <select name="tipo_combustible" required>
      <option value="nafta">Nafta</option>
      <option value="diesel">Diesel</option>
      <option value="eléctrico">Eléctrico</option>
      <option value="híbrido">Híbrido</option>
      <option value="gnc">GNC</option>
      <option value="otro">Otro</option>
    </select><br>

    <label>Transmisión:</label>
    <select name="transmision" required>
      <option value="manual">Manual</option>
      <option value="automática">Automática</option>
      <option value="otro">Otro</option>
    </select><br>

    <label>Color:</label><input type="text" name="color"><br>
  </div>

  <!-- INMUEBLE -->
  <div id="inmueble_fields" style="display:none;">
    <h4>Datos del inmueble</h4>

    <label>Tipo de inmueble:</label>
    <select name="tipo_inmueble" required>
      <option value="casa">Casa</option>
      <option value="departamento">Departamento</option>
      <option value="lote">Lote</option>
      <option value="local">Local</option>
      <option value="oficina">Oficina</option>
      <option value="galpón">Galpón</option>
      <option value="otro">Otro</option>
    </select><br>

    <label>Tipo de contrato:</label>
    <select name="tipo_contrato" required>
      <option value="venta">Venta</option>
      <option value="alquiler">Alquiler</option>
      <option value="alquiler temporario">Alquiler Temporario</option>
      <option value="anticresis">Anticresis</option>
      <option value="otro">Otro</option>
    </select><br>

    <label>Dirección:</label><input type="text" name="direccion" required><br>
    <label>Ciudad:</label><input type="text" name="ciudad" required><br>
    <label>Provincia:</label><input type="text" name="provincia" required><br>
    <label>Superficie total:</label><input type="number" name="superficie_total"><br>
    <label>Superficie cubierta:</label><input type="number" name="superficie_cubierta"><br>
    <label>Ambientes:</label><input type="number" name="ambientes"><br>
    <label>Dormitorios:</label><input type="number" name="dormitorios"><br>
    <label>Baños:</label><input type="number" name="banios"><br>
    <label>Cochera:</label>
    <select name="cochera">
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select><br>
  </div>
  
  <label>Imágenes:</label>
  <input type="file" name="imagenes[]" multiple accept="image/*"><br>

  <input type="submit" value="Publicar">
</form>

<script>
function mostrarCampos() {
  const tipo = document.getElementById("tipo_publicacion").value;

  const vehiculoFields = document.getElementById("vehiculo_fields");
  const inmuebleFields = document.getElementById("inmueble_fields");

  // Ocultar todo por defecto y desactivar inputs
  vehiculoFields.style.display = "none";
  inmuebleFields.style.display = "none";
  [...vehiculoFields.querySelectorAll("input, select")].forEach(e => e.disabled = true);
  [...inmuebleFields.querySelectorAll("input, select")].forEach(e => e.disabled = true);

  // Mostrar y activar solo los que correspondan
  if (tipo === "vehiculo") {
    vehiculoFields.style.display = "block";
    [...vehiculoFields.querySelectorAll("input, select")].forEach(e => e.disabled = false);
  } else if (tipo === "inmueble") {
    inmuebleFields.style.display = "block";
    [...inmuebleFields.querySelectorAll("input, select")].forEach(e => e.disabled = false);
  }
}
</script>
