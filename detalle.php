<?php
session_start(); // Inicia la sesión para guardar datos de la queja
require 'db/conexion.php'; 
date_default_timezone_set('America/Lima'); 

// --- Lógica para Obtener Organizaciones ---
$sql_organizaciones = "SELECT * FROM organizaciones WHERE 1=1";
$params_organizaciones = [];
if (isset($_GET['filtro_organizacion_estado']) && $_GET['filtro_organizacion_estado'] !== '') {
    $estado_org = $_GET['filtro_organizacion_estado'];
    $sql_organizaciones .= " AND estado = ?";
    $params_organizaciones[] = $estado_org;
}
$stmt_organizaciones = $pdo->prepare($sql_organizaciones);
$stmt_organizaciones->execute($params_organizaciones);
$organizaciones = $stmt_organizaciones->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para Obtener Donaciones ---
$sql_donaciones = "SELECT d.*, o.nombre AS nombre_organizacion 
                   FROM donaciones d 
                   JOIN organizaciones o ON d.organizacion = o.nombre 
                   WHERE 1=1";
$params_donaciones = [];

if (isset($_GET['filtro_donacion_estado']) && $_GET['filtro_donacion_estado'] !== '') {
    $estado_don = $_GET['filtro_donacion_estado'];
    $sql_donaciones .= " AND d.estado = ?";
    $params_donaciones[] = $estado_don;
}
if (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] !== '') {
    $tipo_don = $_GET['filtro_donacion_tipo'];
    $sql_donaciones .= " AND d.tipo_donacion = ?";
    $params_donaciones[] = $tipo_don;
}
$sql_donaciones .= " ORDER BY d.creado_en DESC"; // Ordenar por fecha de creación
$stmt_donaciones = $pdo->prepare($sql_donaciones);
$stmt_donaciones->execute($params_donaciones);
$donaciones = $stmt_donaciones->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para Guardar Quejas ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_queja'])) {
    $tipo_queja = $_POST['tipo_queja']; 
    $id_afectado = $_POST['id_afectado'];
    $titulo_queja = $_POST['titulo_queja']; 
    $descripcion_queja = $_POST['descripcion_queja'];
    $fecha_queja = date('Y-m-d H:i:s'); 
    $estado_queja = 'pendiente'; 

    try {
        $stmt_queja = $pdo->prepare("INSERT INTO quejas (tipo_queja, id_afectado, titulo, descripcion, fecha_queja, estado) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt_queja->execute([$tipo_queja, $id_afectado, $titulo_queja, $descripcion_queja, $fecha_queja, $estado_queja])) {
            // Guarda los datos de la queja en la sesión para mostrarlos en la siguiente página
            $_SESSION['queja_data'] = [
                'tipo_queja' => $tipo_queja,
                'id_afectado' => $id_afectado,
                'titulo' => $titulo_queja,
                'descripcion' => $descripcion_queja,
                'fecha_queja' => $fecha_queja,
                'estado' => $estado_queja
            ];
            header("Location: detalle.php?queja_enviada=true");
            exit;
        } else {
            error_log("Error al insertar queja: " . implode(" ", $stmt_queja->errorInfo()));
            echo "<script>alert('Error al registrar la queja. Por favor, intente de nuevo.');</script>";
        }
    } catch (PDOException $e) {
        error_log("PDO Exception al insertar queja: " . $e->getMessage());
        echo "<script>alert('Error de base de datos al registrar la queja.');</script>";
    }
}

// --- Lógica para Eliminar Registros ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_eliminar'])) {
    $id_a_eliminar = $_POST['id_a_eliminar'];
    $tipo_eliminar = $_POST['tipo_eliminar']; // 'organizacion' o 'donacion'

    if (is_numeric($id_a_eliminar)) {
        try {
            $table_name = '';
            if ($tipo_eliminar === 'organizacion') {
                $table_name = 'organizaciones';
            } elseif ($tipo_eliminar === 'donacion') {
                $table_name = 'donaciones';
            } else {
                echo "<script>alert('Tipo de eliminación no válido.');</script>";
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM $table_name WHERE id = ?");
            if ($stmt->execute([$id_a_eliminar])) {
                header("Location: detalle.php?eliminacion=exitosa");
                exit;
            } else {
                error_log("Error al eliminar registro de $table_name: " . implode(" ", $stmt->errorInfo()));
                echo "<script>alert('Error al eliminar el registro.');</script>";
            }
        } catch (PDOException $e) {
            error_log("PDO Exception al eliminar registro: " . $e->getMessage());
            echo "<script>alert('Error de base de datos al eliminar el registro.');</script>";
        }
    } else {
        echo "<script>alert('ID de registro no válido para eliminar.');</script>";
    }
}

// Muestra una alerta si la queja fue enviada exitosamente
if (isset($_GET['queja_enviada']) && $_GET['queja_enviada'] === 'true') {
    echo "<script>alert('¡Queja registrada exitosamente!'); window.history.replaceState(null, null, window.location.pathname);</script>";
}
// Muestra una alerta si la eliminación fue exitosa
if (isset($_GET['eliminacion']) && $_GET['eliminacion'] === 'exitosa') {
    echo "<script>alert('¡Registro eliminado exitosamente!'); window.history.replaceState(null, null, window.location.pathname);</script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Donaciones y Organizaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        /* Variables CSS para colores */
        :root {
            --color-principal: #0f766e; /* Verde azulado oscuro */
            --color-secundario: #5eead4; /* Verde azulado claro */
            --color-texto-oscuro: #343a40; /* Gris oscuro para texto */

            /* Colores para los diferentes estados */
            --color-inactivo: #dc3545; /* Rojo */
            --color-activo: #28a745; /* Verde */
            --color-proceso: #ffc107; /* Amarillo */
            --color-entregado: #17a2b8; /* Cian */
            --color-cancelado: #6c757d; /* Gris */

            /* Colores de encabezados y fondos de tabla */
            --color-header-organizaciones: #007bff; /* Azul para organizaciones */
            --bg-tabla-organizaciones: #e6f7ff; /* Tono pastel de azul */

            --color-header-donaciones:rgb(40, 199, 12); /* Amarillo para donaciones */
            --bg-tabla-donaciones: #fffbe6; /* Tono pastel de amarillo */

            /* Colores para el formulario de quejas (naranja) */
            --color-queja-principal: #fd7e14; /* Naranja vibrante */
            --color-queja-secundario: #ffa500; /* Naranja más suave */
            --color-queja-boton-claro: #007bff; /* Azul para el botón de envío */
            --color-queja-boton-claro-hover: #0056b3; /* Azul más oscuro al pasar el ratón */
            --color-boton-queja-tabla: #ff4500; /* Naranja rojizo para el botón "Queja" */
            --color-boton-queja-tabla-hover: #cc3700; /* Naranja más oscuro al pasar el ratón */
            --color-boton-eliminar: #dc3545; /* Rojo para el botón eliminar */
            --color-boton-eliminar-hover: #c82333; /* Rojo más oscuro al pasar el ratón */
        }
        
        /* Estilos generales del cuerpo */
        body {
            background: url('https://images.unsplash.com/photo-1586296621780-1e209ec9ff94?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fHZlcmRlJTIwYXzulaWRvJTIwb3NjdXJvcjxlbnwwfHwwfHx8MA%3D%3D') no-repeat center center fixed;
            background-size: cover; /* La imagen cubre todo el fondo */
            font-family: 'Segoe UI', sans-serif; /* Fuente de texto */
            margin: 0; min-height: 100vh; display: flex; flex-direction: column;
        }

        /* Estilos de la barra de navegación (navbar) */
        .navbar {
            background-color: var(--color-principal); padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Sombra suave */
        }
        .navbar .frase { color: white; font-weight: 600; font-size: 1.1rem; }
        .btn-menu {
            background-color: white; color: var(--color-principal); font-weight: bold;
            border-radius: 0.5rem; padding: 0.5rem 1rem; transition: all 0.3s ease;
        }
        .btn-menu:hover {
            background-color: var(--color-secundario); color: white; border-color: var(--color-secundario);
        }

        /* Estilos del contenedor principal del contenido */
        .container-main {
            background: rgba(255,255,255,0.98); /* Fondo blanco semitransparente */
            border-radius: 15px; padding: 30px; margin-top: 30px; margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15); /* Sombra pronunciada */
            flex-grow: 1; max-width: 1300px; width: 95%; margin-left: auto; margin-right: auto;
        }

        /* Estilos para encabezados H2 (títulos principales de sección) */
        h2 { color: var(--color-principal); font-weight: 700; text-align: center; margin-bottom: 30px; }
        /* Estilos para H4 (subtítulos de filtros/eliminación) */
        .filter-section h4, .delete-section h4 { 
            color: var(--color-principal); font-weight: 700; text-align: center;
            margin-top: 20px; /* Reducido para compactar */
            margin-bottom: 15px; /* Reducido para compactar */
            font-size: 1.5rem;
        }

        /* Estilos de las tablas */
        table {
            margin-top: 20px; border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); /* Sombra ligera */
        }
        table th, table td {
            text-align: center; vertical-align: middle; padding: 0.75rem; font-size: 0.95rem;
        }

        /* Colores específicos para los encabezados de las tablas (Organizaciones) */
        #organizacionesTable thead th, #organizacionesFilter h4 {
            background-color: var(--color-header-organizaciones) !important; color: white; border-color: var(--color-header-organizaciones) !important;
        }
        #organizacionesFilter h4 { color: var(--color-header-organizaciones); background-color: transparent !important; }

        /* Colores específicos para los encabezados de las tablas (Donaciones) */
        #donacionesTable thead th, #donacionesFilter h4 {
            background-color: var(--color-header-donaciones) !important; color: var(--color-texto-oscuro); border-color: var(--color-header-donaciones) !important;
        }
        #donacionesFilter h4 { color: var(--color-header-donaciones); background-color: transparent !important; }

        .table-hover tbody tr:hover { background-color: #f1f1f1; }

        /* Colores de fondo para los cuerpos de las tablas */
        #organizacionesTable tbody { background-color: var(--bg-tabla-organizaciones); }
        #donacionesTable tbody { background-color: var(--bg-tabla-donaciones); }

        /* Estilos para los botones de estado */
        .btn-estado {
            font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 0.3rem;
            cursor: default; display: inline-block; text-transform: capitalize;
        }
        .btn-estado-activa { background-color: var(--color-activo); color: white; }
        .btn-estado-inactiva { background-color: var(--color-inactivo); color: white; }
        .btn-estado-en_proceso { background-color: var(--color-proceso); color: var(--color-texto-oscuro); }
        .btn-estado-entregado { background-color: var(--color-entregado); color: white; }
        .btn-estado-cancelado { background-color: var(--color-cancelado); color: white; }

        /* Estilos de la sección de filtros */
        .filter-section {
            background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
            padding: 20px; margin-bottom: 15px; /* Ajustado para reducir espacio */
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; justify-content: center;
        }
        .filter-section .form-select, .filter-section .form-control { border-radius: 0.3rem; border-color: #ced4da; }
        .filter-section .btn { border-radius: 0.3rem; font-weight: 500; padding: 0.5rem 1.25rem; }
        .filter-section label { margin-bottom: 5px; font-weight: 500; color: var(--color-texto-oscuro); }

        /* Estilos del botón "Queja" en las tablas */
        .btn-queja {
            color: white; background-color: var(--color-boton-queja-tabla); border-color: var(--color-boton-queja-tabla);
            transition: all 0.3s ease;
        }
        .btn-queja:hover {
            background-color: var(--color-boton-queja-tabla-hover); color: white; border-color: var(--color-boton-queja-tabla-hover);
        }

        /* Estilos de la sección de eliminación */
        .delete-section {
            background-color: #f8d7da; border: 1px solid #dc3545; border-radius: 8px;
            padding: 20px; 
            margin-top: 15px; /* Ajustado para reducir espacio */
            margin-bottom: 30px; /* Mantener un poco de espacio antes de la tabla */
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; justify-content: center;
        }
        .delete-section h4 { color: var(--color-boton-eliminar); margin-top: 0; margin-bottom: 15px; } /* Ajustado para compactar */
        .delete-section .btn-danger {
            background-color: var(--color-boton-eliminar); border-color: var(--color-boton-eliminar);
        }
        .delete-section .btn-danger:hover {
            background-color: var(--color-boton-eliminar-hover); border-color: var(--color-boton-eliminar-hover);
        }

        /* Estilos del Modal de Queja */
        .modal-header { 
            background-color: var(--color-queja-principal) !important; color: white;
            border-bottom: 1px solid var(--color-queja-secundario);
        }
        .modal-header .btn-close-white { filter: invert(1); } /* Icono de cerrar blanco */
        .modal-footer .btn-primary { 
            background-color: var(--color-queja-boton-claro); border-color: var(--color-queja-boton-claro);
        }
        .modal-footer .btn-primary:hover {
            background-color: var(--color-queja-boton-claro-hover); border-color: var(--color-queja-boton-claro-hover);
        }
        .modal-footer .btn-secondary { background-color: #6c757d; border-color: #6c757d; color: white; }
        .modal-footer .btn-secondary:hover { background-color: #5a6268; border-color: #545b62; }

        /* Ajustes para dispositivos móviles (responsive) */
        @media (max-width: 768px) {
            .navbar .frase { font-size: 0.9rem; }
            .container-main { padding: 15px; margin-top: 15px; margin-bottom: 15px; }
            h2 { font-size: 1.8rem; margin-bottom: 20px; }
            h4 { font-size: 1.3rem; margin-top: 15px; margin-bottom: 10px; } /* Ajustado para móvil */
            table th, table td { font-size: 0.85rem; padding: 0.5rem; }
            .filter-section, .delete-section { 
                flex-direction: column; align-items: stretch; /* Apila los filtros/elementos en columnas */
                padding: 15px; /* Reducir padding en móvil */
                margin-bottom: 10px; /* Reducir margen inferior */
                margin-top: 10px; /* Reducir margen superior */
            }
            .filter-section .col-md-4, .filter-section .col-md-2, .filter-section .col-sm-6,
            .delete-section .col-md-4, .delete-section .col-md-2 { 
                width: 100%; /* Los elementos ocupan todo el ancho */
            }
            .filter-section .btn, .delete-section .btn { width: 100%; } /* Los botones ocupan todo el ancho */
        }

        /* Estilos del pie de página (footer) */
        footer {
            background-color: var(--color-principal); color: white; text-align: center;
            padding: 1rem; font-size: 0.9rem; margin-top: auto;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1); /* Sombra suave */
        }
    </style>
</head>
<body>
    <nav class="navbar d-flex justify-content-between align-items-center">
        <div class="frase"><i class="bi bi-heart-fill me-2"></i> Gracias por hacer la diferencia 💚</div>
        <a class="btn btn-menu" href="menu.php"><i class="bi bi-house-door-fill me-2"></i> Menú Principal</a>
    </nav>

    <div class="container container-main">
        <h2><i class="bi bi-building-fill me-2"></i> Detalle de Organizaciones</h2>

        <div class="filter-section" id="organizacionesFilter">
            <h4 class="w-100 mb-3 text-center">Filtrar Organizaciones</h4>
            <form action="detalle.php" method="GET" class="row g-3 w-100 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_organizacion_estado" class="form-label">Estado:</label>
                    <select class="form-select" id="filtro_organizacion_estado" name="filtro_organizacion_estado">
                        <option value="">Todos los Estados</option>
                        <option value="activa" <?= (isset($_GET['filtro_organizacion_estado']) && $_GET['filtro_organizacion_estado'] === 'activa') ? 'selected' : '' ?>>Activa</option>
                        <option value="inactiva" <?= (isset($_GET['filtro_organizacion_estado']) && $_GET['filtro_organizacion_estado'] === 'inactiva') ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-2"></i> Filtrar</button>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <a href="detalle.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle-fill me-2"></i> Limpiar</a>
                </div>
            </form>
        </div>

        <div class="delete-section">
            <h4 class="w-100 mb-2 text-center"><i class="bi bi-trash-fill me-2"></i> Eliminar Organización por ID</h4>
            <form action="detalle.php" method="POST" class="row g-3 w-100 justify-content-center">
                <input type="hidden" name="submit_eliminar" value="1">
                <input type="hidden" name="tipo_eliminar" value="organizacion">
                <div class="col-md-4 col-sm-6">
                    <label for="id_a_eliminar_org" class="form-label visually-hidden">ID de Organización:</label>
                    <input type="number" class="form-control" id="id_a_eliminar_org" name="id_a_eliminar" required placeholder="ID de Organización">
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('¿Está seguro de que desea eliminar esta organización? Esta acción es irreversible.');">
                        <i class="bi bi-trash-fill me-2"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="organizacionesTable">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Tipo Necesidad</th>
                        <th>Descripción</th>
                        <th>Contacto</th>
                        <th>Correo</th>
                        <th>Fecha Registro</th>
                        <th>Fecha Final</th>
                        <th>Estado</th>
                        <th>Queja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($organizaciones)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">No hay organizaciones registradas o que coincidan con el filtro.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($organizaciones as $index => $org): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($org['nombre']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($org['tipo_necesidad'])) ?></td>
                            <td><?= htmlspecialchars($org['descripcion']) ?></td>
                            <td><?= htmlspecialchars($org['contacto']) ?></td>
                            <td><?= htmlspecialchars($org['correo']) ?></td>
                            <td><?= htmlspecialchars($org['fecha_registro']) ?></td>
                            <td><?= htmlspecialchars($org['fecha_final']) ?></td>
                            <td>
                                <?php
                                $estado_class = ($org['estado'] === 'activa') ? 'btn-estado-activa' : 'btn-estado-inactiva';
                                ?>
                                <span class="btn-estado <?= $estado_class ?>"><?= htmlspecialchars(ucfirst($org['estado'])) ?></span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-queja" 
                                    data-bs-toggle="modal" data-bs-target="#quejaModal" 
                                    data-type="organizacion" data-id="<?= $org['id'] ?>" 
                                    data-name="<?= htmlspecialchars($org['nombre']) ?>">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Queja
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr class="my-5 border-2">

        <h2><i class="bi bi-box-seam-fill me-2"></i> Detalle de Donaciones</h2>

        <div class="filter-section" id="donacionesFilter">
            <h4 class="w-100 mb-3 text-center">Filtrar Donaciones</h4>
            <form action="detalle.php" method="GET" class="row g-3 w-100 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_donacion_estado" class="form-label">Estado:</label>
                    <select class="form-select" id="filtro_donacion_estado" name="filtro_donacion_estado">
                        <option value="">Todos los Estados</option>
                        <option value="en_proceso" <?= (isset($_GET['filtro_donacion_estado']) && $_GET['filtro_donacion_estado'] === 'en_proceso') ? 'selected' : '' ?>>En Proceso</option>
                        <option value="entregado" <?= (isset($_GET['filtro_donacion_estado']) && $_GET['filtro_donacion_estado'] === 'entregado') ? 'selected' : '' ?>>Entregado</option>
                        <option value="cancelado" <?= (isset($_GET['filtro_donacion_estado']) && $_GET['filtro_donacion_estado'] === 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_donacion_tipo" class="form-label">Tipo de Donación:</label>
                    <select class="form-select" id="filtro_donacion_tipo" name="filtro_donacion_tipo">
                        <option value="">Todos los Tipos</option>
                        <option value="Alimentos" <?= (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] === 'Alimentos') ? 'selected' : '' ?>>Alimentos</option>
                        <option value="Ropa" <?= (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] === 'Ropa') ? 'selected' : '' ?>>Ropa</option>
                        <option value="Dinero" <?= (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] === 'Dinero') ? 'selected' : '' ?>>Dinero</option>
                        <option value="Juguetes" <?= (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] === 'Juguetes') ? 'selected' : '' ?>>Juguetes</option>
                        <option value="Libros" <?= (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] === 'Libros') ? 'selected' : '' ?>>Libros</option>
                        <option value="Artefactos" <?= (isset($_GET['filtro_donacion_tipo']) && $_GET['filtro_donacion_tipo'] === 'Artefactos') ? 'selected' : '' ?>>Artefactos</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-2"></i> Filtrar</button>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <a href="detalle.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle-fill me-2"></i> Limpiar</a>
                </div>
            </form>
        </div>

        <div class="delete-section">
            <h4 class="w-100 mb-2 text-center"><i class="bi bi-trash-fill me-2"></i> Eliminar Donación por ID</h4>
            <form action="detalle.php" method="POST" class="row g-3 w-100 justify-content-center">
                <input type="hidden" name="submit_eliminar" value="1">
                <input type="hidden" name="tipo_eliminar" value="donacion">
                <div class="col-md-4 col-sm-6">
                    <label for="id_a_eliminar_don" class="form-label visually-hidden">ID de Donación:</label>
                    <input type="number" class="form-control" id="id_a_eliminar_don" name="id_a_eliminar" required placeholder="ID de Donación">
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('¿Está seguro de que desea eliminar esta donación? Esta acción es irreversible.');">
                        <i class="bi bi-trash-fill me-2"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="donacionesTable">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Organización</th>
                        <th>Nombre Donante</th>
                        <th>Correo Donante</th>
                        <th>Fecha Donación</th>
                        <th>Tipo Donación</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Fecha Entrega</th>
                        <th>Lugar Entrega</th>
                        <th>Queja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($donaciones)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4">No hay donaciones registradas o que coincidan con el filtro.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donaciones as $index => $d): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($d['nombre_organizacion'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['nombre_donante'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['correo'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['fecha_donacion'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['tipo_donacion'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['descripcion'] ?? 'N/A') ?></td>
                            <td>
                                <?php
                                $estado_class = '';
                                switch ($d['estado']) {
                                    case 'en_proceso': $estado_class = 'btn-estado-en_proceso'; break;
                                    case 'entregado': $estado_class = 'btn-estado-entregado'; break;
                                    case 'cancelado': $estado_class = 'btn-estado-cancelado'; break;
                                    default: $estado_class = 'btn-secondary'; break;
                                }
                                ?>
                                <span class="btn-estado <?= $estado_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $d['estado']))) ?></span>
                            </td>
                            <td><?= htmlspecialchars($d['fecha_entrega'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['lugar_entrega'] ?? 'N/A') ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-queja" 
                                    data-bs-toggle="modal" data-bs-target="#quejaModal" 
                                    data-type="donante" data-id="<?= $d['id'] ?>" 
                                    data-name="<?= htmlspecialchars($d['nombre_donante'] ?? 'Donante Desconocido') ?>">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Queja
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="quejaModal" tabindex="-1" aria-labelledby="quejaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quejaModalLabel"><i class="bi bi-exclamation-octagon-fill me-2"></i> Registrar Queja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="detalle.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="submit_queja" value="1">
                        <input type="hidden" id="queja_type" name="tipo_queja">
                        <input type="hidden" id="queja_id_afectado" name="id_afectado">
                        
                        <div class="mb-3">
                            <label for="queja_target_name" class="form-label">De Parte De:</label>
                            <input type="text" class="form-control" id="queja_target_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="titulo_queja" class="form-label">Tipo de Queja (Asunto):</label>
                            <input type="text" class="form-control" id="titulo_queja" name="titulo_queja" required placeholder="Ej: Demora en la respuesta, Problema de entrega">
                        </div>
                        <div class="mb-3">
                            <label for="descripcion_queja" class="form-label">Descripción de Queja:</label>
                            <textarea class="form-control" id="descripcion_queja" name="descripcion_queja" rows="4" required placeholder="Describe tu queja aquí detalladamente..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_queja_display" class="form-label">Fecha de Queja:</label>
                            <input type="text" class="form-control" id="fecha_queja_display" value="<?= date('d/m/Y') ?>" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-2"></i> Enviar Queja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="text-center py-3">
        Red de Donaciones - Unidos por una causa 🫶
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quejaModal = document.getElementById('quejaModal');
            if (quejaModal) {
                quejaModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var type = button.getAttribute('data-type');
                    var id = button.getAttribute('data-id');
                    var name = button.getAttribute('data-name');

                    var modalTitle = quejaModal.querySelector('.modal-title');
                    var inputType = quejaModal.querySelector('#queja_type');
                    var inputId = quejaModal.querySelector('#queja_id_afectado');
                    var inputTargetName = quejaModal.querySelector('#queja_target_name');
                    var inputTituloQueja = quejaModal.querySelector('#titulo_queja');
                    var descripcionQueja = quejaModal.querySelector('#descripcion_queja');
                    
                    modalTitle.textContent = `Registrar Queja para ${name}`;
                    inputType.value = type;
                    inputId.value = id;
                    inputTargetName.value = name;
                    
                    inputTituloQueja.value = '';
                    descripcionQueja.value = ''; 
                });
            }
        });
    </script>
</body>
</html>