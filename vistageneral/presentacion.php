<?php
session_start(); // Asegurarse de que la sesión esté activa
$logueado = isset($_SESSION['id_usuario']);
$nombre_usuario = $logueado ? $_SESSION['nombre'] : '';
$tipo_usuario = $logueado ? $_SESSION['tipo_usuario'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi E-commerce</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJkXcuZI5gQp9+gQ7YllIfr4Ktv6f5wyY8H8g5R9hPjw7Jl5w3LOMB++m2kF" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .redirect-box {
      position: relative;
      overflow: hidden;
      cursor: pointer;
      border-radius: 15px;
      transition: transform 0.3s ease;
    }
    .redirect-box img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      opacity: 0.8;
    }
    .redirect-box:hover {
      transform: scale(1.02);
    }
    .redirect-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 1.8rem;
      color: white;
      font-weight: bold;
      text-shadow: 2px 2px 5px black;
    }
  </style>
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


  <!-- Quiénes somos -->
  <section class="container my-5">
    <h2>¿Quiénes somos?</h2>
    <p>
      Somos una plataforma dedicada a conectar personas que quieren comprar y vender <strong>vehículos</strong> o <strong>inmuebles</strong>. Nuestra misión es facilitar la publicación de ofertas y garantizar una experiencia simple, rápida y segura.
    </p>
  </section>

  <!-- Redirecciones -->
  <section class="container mb-5">
    <div class="row g-4">
      <div class="col-md-6">
      <a href="../publicaciones/vista_publicaciones.php?tipo=vehiculo" class="text-decoration-none">

          <div class="redirect-box">
            <img src="https://cdn.pixabay.com/photo/2015/01/19/13/51/car-604019_1280.jpg" alt="Vehículos">
           

            <div class="redirect-text">Ver Vehículos</div>
          </div>
        </a>
      </div>
      <div class="col-md-6">
      <a href="../publicaciones/vista_publicaciones.php?tipo=inmueble" class="text-decoration-none">
          <div class="redirect-box">
            <img src="https://cdn.pixabay.com/photo/2016/11/29/03/53/architecture-1867187_1280.jpg" alt="Inmuebles">
            

            <div class="redirect-text">Ver Inmuebles</div>
          </div>
        </a>
      </div>
    </div>
  </section>

  <footer class="bg-light text-center py-3">
    © 2025 Mi E-commerce - Todos los derechos reservados.
  </footer>

</body>
</html>