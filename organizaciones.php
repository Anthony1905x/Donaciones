<?php
require 'db/conexion.php';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST['nombre'];
  $tipo = $_POST['tipo'];
  $descripcion = $_POST['descripcion'];
  $contacto = $_POST['contacto'];
  $correo = $_POST['correo'];
  $fecha_registro = $_POST['fecha_inicio'];
  $fecha_final = $_POST['fecha_final'];
  $estado = (strtotime($fecha_final) >= strtotime(date("Y-m-d"))) ? 'activa' : 'inactiva';

  try {
    $sql = "INSERT INTO organizaciones (nombre, tipo_necesidad, descripcion, contacto, correo, fecha_registro, fecha_final, estado)
            VALUES (:nombre, :tipo, :descripcion, :contacto, :correo, :fecha_registro, :fecha_final, :estado)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nombre' => $nombre,
      ':tipo' => $tipo,
      ':descripcion' => $descripcion,
      ':contacto' => $contacto,
      ':correo' => $correo,
      ':fecha_registro' => $fecha_registro,
      ':fecha_final' => $fecha_final,
      ':estado' => $estado
    ]);
    $mensaje = '✅ Organización registrada correctamente en la base de datos.';
  } catch (PDOException $e) {
    $mensaje = '❌ Error al registrar: ' . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registrar Organización - Red de Donaciones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root {
      --color-principal: #0f766e;
      --color-secundario: #5eead4;
    }

    body {
      background: url('https://wallpapers.com/images/hd/blue-and-green-background-2560-x-1600-8qc89dybzjndplms.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      margin: 0;
    }

    .navbar {
      background-color: var(--color-principal);
      padding: 0.4rem 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar .navbar-brand {
      color: white;
      font-weight: bold;
    }

    .navbar .btn-volver {
      background-color: white;
      color: var(--color-principal);
      font-weight: bold;
      border: 2px solid white;
      transition: 0.3s;
      padding: 0.35rem 0.8rem;
      font-size: 0.9rem;
    }

    .navbar .btn-volver:hover {
      background-color: var(--color-secundario);
      color: black;
    }

    .container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 15px;
    }

    .card {
      width: 100%;
      max-width: 700px;
      padding: 25px;
      border-radius: 15px;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }

    h2 {
      margin-bottom: 20px;
      color: var(--color-principal);
      font-weight: bold;
      text-align: center;
    }

    label {
      font-weight: bold;
      color: var(--color-principal);
      font-size: 14px;
    }

    .form-control {
      font-weight: bold;
      font-size: 14px;
      padding: 8px;
    }

    .btn-primary {
      background-color: var(--color-principal);
      border: none;
      font-weight: bold;
    }

    .btn-primary:hover {
      background-color: var(--color-secundario);
      color: black;
    }

    footer {
      background-color: var(--color-principal);
      color: white;
      text-align: center;
      padding: 10px 0;
      font-size: 13px;
    }

    footer p {
      font-weight: bold;
      margin: 0;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar shadow-sm">
    <a class="navbar-brand" href="#"><i class="bi bi-hand-index-thumb-fill me-2"></i> Red de Donaciones</a>
    <button class="btn btn-volver" onclick="window.location.href='menu.php'">
      <i class="bi bi-house-door-fill"></i> Menú
    </button>
  </nav>

  <!-- Contenido -->
  <div class="container">
    <div class="card">
      <h2><i class="bi bi-building"></i> Registrar Organización</h2>

      <?php if ($mensaje): ?>
        <div class="alert alert-info"><?php echo $mensaje; ?></div>
      <?php endif; ?>

      <form id="formOrganizacion" method="POST" action="">

        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre de la organización</label>
          <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>

        <div class="mb-3">
          <label for="tipo" class="form-label">Tipo de necesidad</label>
          <select class="form-control" id="tipo" name="tipo" required>
            <option value="">Seleccione un tipo</option>
            <option value="ropas">Ropas</option>
            <option value="alimentos">Alimentos</option>
            <option value="juguetes">Juguetes</option>
            <option value="libros">Libros</option>
            <option value="artefactos">Artefactos</option>
            <option value="dinero">Dinero</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
        </div>

        <div class="mb-3">
          <label for="contacto" class="form-label">Datos de contacto (9 dígitos)</label>
          <input type="text" class="form-control" id="contacto" name="contacto" pattern="\d{9}" required>
        </div>

        <div class="mb-3">
          <label for="correo" class="form-label">Correo de acceso</label>
          <input type="email" class="form-control" id="correo" name="correo" required>
        </div>

        <div class="mb-3">
          <label for="fecha_inicio" class="form-label">Fecha de registro</label>
          <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
        </div>

        <div class="mb-3">
          <label for="fecha_final" class="form-label">Fecha final</label>
          <input type="date" class="form-control" id="fecha_final" name="fecha_final" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Estado</label>
          <input type="text" class="form-control" id="estado" value="activa" disabled>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save-fill"></i> Registrar Organización
          </button>
        </div>

      </form>
    </div>
  </div>

  <footer>
    <p>🌱 Apoyamos a quienes ayudan - Red de Donaciones 💚</p>
  </footer>

  <script>
    const today = new Date().toISOString().split("T")[0];
    document.getElementById('fecha_inicio').min = today;
    document.getElementById('fecha_final').min = today;

    // Opcional: Guardar en localStorage además de MySQL
    document.getElementById('formOrganizacion').addEventListener('submit', function () {
      const fechaFinal = new Date(document.getElementById('fecha_final').value);
      const hoy = new Date();
      const estadoAuto = fechaFinal >= hoy ? 'activa' : 'inactiva';
      document.getElementById('estado').value = estadoAuto;

      const nuevaOrg = {
        id: Date.now(),
        nombre: document.getElementById('nombre').value.trim(),
        tipo_necesidad: document.getElementById('tipo').value,
        descripcion: document.getElementById('descripcion').value.trim(),
        contacto: document.getElementById('contacto').value.trim(),
        correo: document.getElementById('correo').value.trim(),
        fecha_registro: document.getElementById('fecha_inicio').value,
        fecha_final: document.getElementById('fecha_final').value,
        estado: estadoAuto
      };

      let organizaciones = JSON.parse(localStorage.getItem('organizaciones')) || [];
      organizaciones.push(nuevaOrg);
      localStorage.setItem('organizaciones', JSON.stringify(organizaciones));
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
