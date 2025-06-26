<?php
session_start();
require 'db/conexion.php';

$loginError = '';
$crearError = '';
$crearExito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['accion'] === 'login') {
        $correo = $_POST['correo'];
        $clave = $_POST['clave'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($clave, $usuario['clave'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            // Guardamos correo como nombre para mostrar
            $_SESSION['nombre'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirección según rol
            if ($usuario['rol'] === 'admin') {
                header("Location: menu.php");
            } else {
                header("Location: menu.php");
            }
            exit;
        } else {
            $loginError = "Correo o contraseña incorrectos.";
        }
    }

    if ($_POST['accion'] === 'crear') {
        $nuevoCorreo = $_POST['nuevoCorreo'];
        $nuevoPassword = $_POST['nuevoPassword'];
        $nuevoRol = $_POST['nuevoRol'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$nuevoCorreo]);

        if ($stmt->rowCount() > 0) {
            $crearError = "Este correo ya está registrado.";
        } else {
            $claveHash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
            // Insertar sólo con correo, clave y rol
            $stmt = $pdo->prepare("INSERT INTO usuarios (correo, clave, rol) VALUES (?, ?, ?)");
            $stmt->execute([$nuevoCorreo, $claveHash, $nuevoRol]);
            $crearExito = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Login - Red de Donaciones</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f4f7;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-container {
      background: rgb(18, 152, 18);
      padding: 30px 40px;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      width: 350px;
      text-align: center;
      position: relative;
    }
    label {
      display: block;
      text-align: left;
      margin-top: 15px;
      font-weight: bold;
      color: white;
    }
    input, select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    button {
      margin-top: 20px;
      padding: 10px 15px;
      width: 100%;
      border: none;
      border-radius: 25px;
      background-color: #0715dc;
      color: white;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #1e5f5b;
    }
    #mostrarCrearCuentaBtn {
      background-color: transparent;
      border: 2px solid #0b0bc9;
      margin-top: 10px;
      font-weight: bold;
      width: auto;
      padding: 8px 20px;
      border-radius: 25px;
      color: white;
      cursor: pointer;
    }
    .error-message {
      color: red;
      font-size: 14px;
      margin-top: 10px;
    }
    #crearExito {
      margin-top: 10px;
      font-size: 14px;
      color: lightgreen;
    }
    #crearCuentaModal {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 10;
    }
    #crearCuentaForm {
      background: white;
      padding: 30px 40px;
      border-radius: 8px;
      width: 350px;
      position: relative;
      color: black;
    }
    #cerrarModal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 22px;
      font-weight: bold;
      color: #888;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h1 style="color: white;">Iniciar Sesión</h1>
    <form method="POST" autocomplete="off">
      <input type="hidden" name="accion" value="login">
      <label for="correo">Correo electrónico:</label>
      <input type="email" name="correo" id="correo" required>

      <label for="clave">Contraseña:</label>
      <input type="password" name="clave" id="clave" required>

      <button type="submit">Ingresar</button>
    </form>
    <?php if ($loginError): ?><p class="error-message"><?= htmlspecialchars($loginError) ?></p><?php endif; ?>

    <button id="mostrarCrearCuentaBtn" type="button">Crear Cuenta Nueva</button>
  </div>

  <!-- Modal de Crear Cuenta -->
  <div id="crearCuentaModal">
    <form id="crearCuentaForm" method="POST" autocomplete="off">
      <input type="hidden" name="accion" value="crear">
      <span id="cerrarModal">&times;</span>
      <h2>Crear Cuenta</h2>

      <label for="nuevoCorreo">Correo electrónico:</label>
      <input type="email" name="nuevoCorreo" id="nuevoCorreo" required>

      <label for="nuevoPassword">Contraseña:</label>
      <input type="password" name="nuevoPassword" id="nuevoPassword" required>

      <label for="nuevoRol">Rol:</label>
      <select name="nuevoRol" id="nuevoRol" required>
        <option value="usuario">Usuario</option>
        <option value="admin">Administrador</option>
      </select>

      <button type="submit">Crear Cuenta</button>
      <?php if ($crearError): ?><p class="error-message"><?= htmlspecialchars($crearError) ?></p><?php endif; ?>
      <?php if ($crearExito): ?><p id="crearExito"><?= htmlspecialchars($crearExito) ?></p><?php endif; ?>
    </form>
  </div>

  <script>
    const modal = document.getElementById('crearCuentaModal');
    const abrir = document.getElementById('mostrarCrearCuentaBtn');
    const cerrar = document.getElementById('cerrarModal');

    abrir.onclick = () => modal.style.display = 'flex';
    cerrar.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
  </script>
</body>
</html>
