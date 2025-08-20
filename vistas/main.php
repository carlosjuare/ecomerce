<?php include 'header.php'; ?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicaciones</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    h2 {
      margin-top: 40px;
    }
    .carrusel-placeholder {
      width: 100%;
      height: 200px;
      background-color: #eee;
      margin: 10px 0;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
      color: #555;
    }
    .ver-mas {
      margin-top: 10px;
      display: inline-block;
      padding: 8px 16px;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }
    .ver-mas:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

  <h2>🚗 Vehículos en venta</h2>
  <div class="carrusel-placeholder">[Carrusel de vehículos aquí]</div>
  <a class="ver-mas" href="publicaciones.php?tipo=vehiculo">Ver más vehículos</a>

  <h2>🏠 Inmuebles disponibles</h2>
  <div class="carrusel-placeholder">[Carrusel de inmuebles aquí]</div>
 <a class="ver-mas" href="publicaciones.php?tipo=inmueble">Ver más inmuebles</a>

</body>
</html>