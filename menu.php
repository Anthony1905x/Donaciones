<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Red de Donaciones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom right, rgba(20, 92, 80, 0.85), rgba(255, 255, 255, 0.2)), url('https://images.unsplash.com/photo-1527799820374-c0428ed7c5f1?auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .main-box {
      display: flex;
      width: 96%;
      max-width: 1300px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.25);
      background-color: rgba(255, 255, 255, 0.97);
    }

    .sidebar {
      width: 320px;
      background-color: #125c50;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 35px 25px;
    }

    .sidebar h3 {
      text-align: center;
      font-weight: bold;
      margin-bottom: 30px;
      font-size: 1.6rem;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 14px 20px;
      font-weight: 500;
      border-radius: 8px;
      margin-bottom: 10px;
      transition: all 0.3s ease;
    }

    .sidebar a:hover {
      background-color: #2ecc71;
      transform: scale(1.02);
    }

    .sidebar .logout-btn {
      background-color: #e74c3c;
      text-align: center;
      margin-top: auto;
      padding: 14px;
      border-radius: 8px;
      font-weight: bold;
    }

    .content-area {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .topbar {
      background-color: #198754;
      color: white;
      padding: 12px 25px;
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .topbar .title {
      font-size: 1.5rem;
      font-weight: bold;
    }

    .topbar .user-info {
      font-size: 1rem;
    }

    .welcome {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 30px;
      background-color: #f0fdfa;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .welcome-text h1 {
      font-size: 2.3rem;
      color: #145c50;
      margin-bottom: 10px;
    }

    .welcome-text p {
      color: #333;
      font-size: 1.1rem;
    }

    .welcome-text a.btn {
      margin-top: 20px;
    }

    .welcome img {
      max-width: 360px;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    }

    .footer {
      text-align: center;
      margin-top: 30px;
    }

    .social-icons a {
      color: #145c50;
      font-size: 1.8rem;
      margin: 0 10px;
      transition: 0.3s;
    }

    .social-icons a:hover {
      color: #2ecc71;
      transform: scale(1.2);
    }
  </style>
</head>
<body>

<div class="main-box">

  <!-- Sidebar -->
  <div class="sidebar">
    <h3>Red de Donaciones</h3>
    <?php if ($rol === 'usuario'): ?>
      <a href="organizaciones.php"><i class="fas fa-hands-helping me-2"></i> Publicar Necesidad</a>
      <a href="donaciones.php"><i class="fas fa-donate me-2"></i> Realizar Donación</a>
    <?php endif; ?>
    <a href="detalle.php"><i class="fas fa-list me-2"></i> Ver Detalles</a>
    <?php if ($rol === 'admin'): ?>
      <a href="reportes.php"><i class="fas fa-chart-bar me-2"></i> Reportes</a>
    <?php endif; ?>
    <a href="#" class="logout-btn" id="btnCerrarSesion"><i class="fas fa-sign-out-alt me-2"></i> Salir</a>
  </div>

  <!-- Content Area -->
  <div class="content-area">
    <div class="topbar">
      <div class="title">Red de Donaciones</div>
      <div class="user-info"><i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($rol) ?>)</div>
    </div>

    <div class="welcome">
      <div class="welcome-text">
        <h1>Bienvenido a la Red Peruana de Donaciones</h1>
        <p>Conectamos corazones solidarios con organizaciones que más lo necesitan.</p>
        <?php if ($rol === 'usuario'): ?>
          <a href="organizaciones.php" class="btn btn-success">Comenzar</a>
        <?php else: ?>
          <a href="detalle.php" class="btn btn-primary">Ir a Detalles</a>
        <?php endif; ?>
      </div>
      <div>
        <img src="https://noticias-cr.laiglesiadejesucristo.org/media/640x480/10703766_5987613435803631463988337_n.jpg" alt="Red de Donaciones">
      </div>
    </div>

    <div class="footer">
      <p><strong>SÍGUENOS EN NUESTRAS REDES SOCIALES</strong></p>
      <div class="social-icons">
        <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook-square"></i></a>
        <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.youtube.com" target="_blank"><i class="fab fa-youtube"></i></a>
        <a href="https://www.linkedin.com" target="_blank"><i class="fab fa-linkedin"></i></a>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('btnCerrarSesion').addEventListener('click', () => {
    fetch('logout.php').then(() => {
      window.location.href = 'index.php';
    });
  });
</script>
</body>
</html>
