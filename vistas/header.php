<?php
// Iniciar sesión si no se ha hecho
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logueado = isset($_SESSION['id_usuario']);
$nombre_usuario = $logueado ? $_SESSION['nombre_usuario'] : '';
?>

<style>
  header {
    background-color: #f8f9fa;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ccc;
  }

  .logo {
    font-size: 22px;
    font-weight: bold;
    color: #007BFF;
    text-decoration: none;
  }

  .usuario-opciones {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .usuario-opciones a {
    padding: 6px 12px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 4px;
  }

  .usuario-opciones a:hover {
    background-color: #0056b3;
  }
</style>

<header>
  <a class="logo" href="main.php">MiSitio</a>

  <div class="usuario-opciones">
    <?php if ($logueado): ?>
      <span>Hola, <?= htmlspecialchars($nombre_usuario) ?></span>
      
      <a href="gestionar_publicaciones.php">Gestionar publicaciones</a>
      <a href="publicaciones.php">Explorar</a>
      <a href="perfil_usuario.php">Mi perfil</a>
      <a href="../funciones/cerrar_sesion.php">Cerrar sesión</a>
    <?php else: ?>
      <a href="login.php">Iniciar sesión</a>
    <?php endif; ?>
  </div>
</header>