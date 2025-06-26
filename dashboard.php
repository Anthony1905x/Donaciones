<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
?>

<h2>Bienvenido, <?= $_SESSION['nombre'] ?> 👋</h2>
<p>Tu rol: <?= $_SESSION['rol'] ?></p>

<a href="logout.php">Cerrar sesión</a>
