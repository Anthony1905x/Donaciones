<?php
session_start(); // Inicia la sesión para guardar datos de la queja
require 'db/conexion.php'; 
date_default_timezone_set('America/Lima'); 

// Función para manejar redirecciones y mensajes de error de forma consistente
function redirect_with_message($location, $param, $message_type, $log_message = "") {
    if (!empty($log_message)) {
        error_log($log_message);
    }
    echo "<script>alert('" . addslashes($message_type) . "'); window.location.href = '" . $location . "';</script>";
    exit;
}

// Actualizar estado de Organización
if (isset($_GET['accion_org_estado']) && isset($_GET['id_org'])) {
    $id_org = $_GET['id_org'];
    $nuevo_estado_org = $_GET['accion_org_estado']; 
    try {
        $stmt_update_org = $pdo->prepare("UPDATE organizaciones SET estado = ? WHERE id = ?");
        $stmt_update_org->execute([$nuevo_estado_org, $id_org]);
        header("Location: reportes.php?org_updated=true"); 
        exit;
    } catch (PDOException $e) {
        redirect_with_message("reportes.php", "org_error", "Error al actualizar el estado de la organización.", "Error al actualizar estado de organización: " . $e->getMessage());
    }
}

// Actualizar estado de Donación
if (isset($_GET['accion_don_estado']) && isset($_GET['id_don'])) {
    $id_don = $_GET['id_don'];
    $nuevo_estado_don = $_GET['accion_don_estado']; 
    try {
        $stmt_update_don = $pdo->prepare("UPDATE donaciones SET estado = ? WHERE id = ?");
        $stmt_update_don->execute([$nuevo_estado_don, $id_don]);
        header("Location: reportes.php?don_updated=true"); 
        exit;
    } catch (PDOException $e) {
        redirect_with_message("reportes.php", "don_error", "Error al actualizar el estado de la donación.", "Error al actualizar estado de donación: " . $e->getMessage());
    }
}

// Actualizar estado de Queja
if (isset($_GET['accion_queja_estado']) && isset($_GET['id_queja'])) {
    $id_queja = $_GET['id_queja'];
    $nuevo_estado_queja = $_GET['accion_queja_estado']; 
    try {
        $stmt_update_queja = $pdo->prepare("UPDATE quejas SET estado = ? WHERE id = ?");
        $stmt_update_queja->execute([$nuevo_estado_queja, $id_queja]);
        header("Location: reportes.php?queja_updated=true"); 
        exit;
    } catch (PDOException $e) {
        redirect_with_message("reportes.php", "queja_error", "Error al actualizar el estado de la queja.", "Error al actualizar estado de queja: " . $e->getMessage());
    }
}

// Lógica para Editar Organización
if (isset($_GET['accion_org_editar']) && isset($_GET['id_org_edit']) && isset($_GET['campo']) && isset($_GET['valor'])) {
    $id_org = $_GET['id_org_edit'];
    $campo = $_GET['campo'];
    $valor = $_GET['valor'];
    $campos_permitidos = ['nombre', 'tipo_necesidad', 'descripcion', 'contacto', 'correo', 'fecha_final']; 

    if (in_array($campo, $campos_permitidos)) {
        try {
            $stmt_edit_org = $pdo->prepare("UPDATE organizaciones SET {$campo} = ? WHERE id = ?");
            $stmt_edit_org->execute([$valor, $id_org]);
            header("Location: reportes.php?org_edited=true"); 
            exit;
        } catch (PDOException $e) {
            redirect_with_message("reportes.php", "org_error", "Error al editar la organización.", "Error al editar organización: " . $e->getMessage());
        }
    } else {
        redirect_with_message("reportes.php", "org_error", "Campo de edición no válido para organización.");
    }
}

// Lógica para Eliminar Organización
if (isset($_GET['accion_org_eliminar']) && isset($_GET['id_org_del'])) {
    $id_org = $_GET['id_org_del'];
    try {
        $stmt_delete_org = $pdo->prepare("DELETE FROM organizaciones WHERE id = ?");
        $stmt_delete_org->execute([$id_org]);
        header("Location: reportes.php?org_deleted=true"); 
        exit;
    } catch (PDOException $e) {
        redirect_with_message("reportes.php", "org_error", "Error al eliminar la organización.", "Error al eliminar organización: " . $e->getMessage());
    }
}

// Lógica para Editar Donación
if (isset($_GET['accion_don_editar']) && isset($_GET['id_don_edit']) && isset($_GET['campo']) && isset($_GET['valor'])) {
    $id_don = $_GET['id_don_edit'];
    $campo = $_GET['campo'];
    $valor = $_GET['valor'];

    $campos_permitidos = ['descripcion', 'lugar_entrega', 'fecha_entrega', 'tipo_donacion']; 

    if (in_array($campo, $campos_permitidos)) {
        try {
            $stmt_edit_don = $pdo->prepare("UPDATE donaciones SET {$campo} = ? WHERE id = ?");
            $stmt_edit_don->execute([$valor, $id_don]);
            header("Location: reportes.php?don_edited=true"); 
            exit;
        } catch (PDOException $e) {
            redirect_with_message("reportes.php", "don_error", "Error al editar la donación.", "Error al editar donación: " . $e->getMessage());
        }
    } else {
        redirect_with_message("reportes.php", "don_error", "Campo de edición no válido para donación.");
    }
}

// Lógica para Eliminar Donación
if (isset($_GET['accion_don_eliminar']) && isset($_GET['id_don_del'])) {
    $id_don = $_GET['id_don_del'];
    try {
        $stmt_delete_don = $pdo->prepare("DELETE FROM donaciones WHERE id = ?");
        $stmt_delete_don->execute([$id_don]);
        header("Location: reportes.php?don_deleted=true"); 
        exit;
    } catch (PDOException $e) {
        redirect_with_message("reportes.php", "don_error", "Error al eliminar la donación.", "Error al eliminar donación: " . $e->getMessage());
    }
}

// Lógica para Editar Queja
if (isset($_GET['accion_queja_editar']) && isset($_GET['id_queja_edit']) && isset($_GET['campo']) && isset($_GET['valor'])) {
    $id_queja = $_GET['id_queja_edit'];
    $campo = $_GET['campo'];
    $valor = $_GET['valor'];

    $campos_permitidos = ['titulo', 'descripcion']; 

    if (in_array($campo, $campos_permitidos)) {
        try {
            $stmt_edit_queja = $pdo->prepare("UPDATE quejas SET {$campo} = ? WHERE id = ?");
            $stmt_edit_queja->execute([$valor, $id_queja]);
            header("Location: reportes.php?queja_edited=true"); 
            exit;
        } catch (PDOException $e) {
            redirect_with_message("reportes.php", "queja_error", "Error al editar la queja.", "Error al editar queja: " . $e->getMessage());
        }
    } else {
        redirect_with_message("reportes.php", "queja_error", "Campo de edición no válido para queja.");
    }
}

// Lógica para Eliminar Queja
if (isset($_GET['accion_queja_eliminar']) && isset($_GET['id_queja_del'])) {
    $id_queja = $_GET['id_queja_del'];
    try {
        $stmt_delete_queja = $pdo->prepare("DELETE FROM quejas WHERE id = ?");
        $stmt_delete_queja->execute([$id_queja]);
        header("Location: reportes.php?queja_deleted=true"); 
        exit;
    } catch (PDOException $e) {
        redirect_with_message("reportes.php", "queja_error", "Error al eliminar la queja.", "Error al eliminar queja: " . $e->getMessage());
    }
}

// --- Lógica para Obtener Datos con Filtros ---

// Obtener Organizaciones
$sql_organizaciones = "SELECT * FROM organizaciones WHERE 1=1";
$params_organizaciones = [];
if (isset($_GET['filtro_org_estado']) && $_GET['filtro_org_estado'] !== '') {
    $estado_org = $_GET['filtro_org_estado'];
    $sql_organizaciones .= " AND estado = ?";
    $params_organizaciones[] = $estado_org;
}
$stmt_organizaciones = $pdo->prepare($sql_organizaciones);
$stmt_organizaciones->execute($params_organizaciones);
$organizaciones = $stmt_organizaciones->fetchAll(PDO::FETCH_ASSOC);

// Obtener Donaciones (con nombre de organización)
$sql_donaciones = "SELECT d.*, o.nombre AS nombre_organizacion 
                     FROM donaciones d 
                     JOIN organizaciones o ON d.organizacion = o.nombre 
                     WHERE 1=1"; 
$params_donaciones = [];
if (isset($_GET['filtro_don_estado']) && $_GET['filtro_don_estado'] !== '') {
    $estado_don = $_GET['filtro_don_estado'];
    $sql_donaciones .= " AND d.estado = ?";
    $params_donaciones[] = $estado_don;
}
if (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] !== '') {
    $tipo_don = $_GET['filtro_don_tipo'];
    $sql_donaciones .= " AND d.tipo_donacion = ?";
    $params_donaciones[] = $tipo_don;
}
$sql_donaciones .= " ORDER BY d.creado_en DESC";
$stmt_donaciones = $pdo->prepare($sql_donaciones);
$stmt_donaciones->execute($params_donaciones);
$donaciones = $stmt_donaciones->fetchAll(PDO::FETCH_ASSOC);

// Obtener Quejas
$sql_quejas = "SELECT q.*, 
                       CASE q.tipo_queja 
                           WHEN 'organizacion' THEN (SELECT nombre FROM organizaciones WHERE id = q.id_afectado)
                           WHEN 'donante' THEN (SELECT nombre_donante FROM donaciones WHERE id = q.id_afectado) 
                           ELSE 'Desconocido' 
                       END AS nombre_afectado 
                FROM quejas q
                WHERE 1=1";
$params_quejas = [];
if (isset($_GET['filtro_queja_estado']) && $_GET['filtro_queja_estado'] !== '') {
    $estado_queja_filtro = $_GET['filtro_queja_estado'];
    $sql_quejas .= " AND q.estado = ?";
    $params_quejas[] = $estado_queja_filtro;
}
$sql_quejas .= " ORDER BY q.fecha_queja DESC";
$stmt_quejas = $pdo->prepare($sql_quejas);
$stmt_quejas->execute($params_quejas);
$quejas = $stmt_quejas->fetchAll(PDO::FETCH_ASSOC);

// --- Preparar Datos para Gráficos ---

// Gráfico 1: Donaciones por Tipo (Pie Chart)
$tipos_donacion_data = [];
foreach ($donaciones as $donacion) {
    $tipo = $donacion['tipo_donacion'];
    $tipos_donacion_data[$tipo] = ($tipos_donacion_data[$tipo] ?? 0) + 1;
}
$chart_labels_tipos = json_encode(array_keys($tipos_donacion_data));
$chart_data_tipos = json_encode(array_values($tipos_donacion_data));

// Gráfico 2: Organizaciones por Estado (Bar Chart)
$org_estado_data = [];
foreach ($organizaciones as $org) {
    $estado = $org['estado'];
    $org_estado_data[$estado] = ($org_estado_data[$estado] ?? 0) + 1;
}
$chart_labels_org_estado = json_encode(array_keys($org_estado_data));
$chart_data_org_estado = json_encode(array_values($org_estado_data));

// Exportar a Excel
if (isset($_GET['exportar_excel'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_donaciones_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');

    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Encabezados para Organizaciones
    fputcsv($output, ['--- Organizaciones ---']);
    fputcsv($output, ['ID', 'Nombre', 'Tipo Necesidad', 'Descripción', 'Contacto', 'Correo', 'Fecha Registro', 'Fecha Final', 'Estado']);
    foreach ($organizaciones as $org) {
        fputcsv($output, $org);
    }

    // Separador
    fputcsv($output, ['']); // Línea en blanco
    fputcsv($output, ['--- Donaciones ---']);
    // Encabezados para Donaciones
    fputcsv($output, ['ID', 'Organizacion', 'Nombre Donante', 'Correo Donante', 'Fecha Donacion', 'Tipo Donacion', 'Descripcion', 'Estado', 'Fecha Entrega', 'Lugar Entrega']);
    foreach ($donaciones as $don) {
        fputcsv($output, [
            $don['id'], 
            $don['nombre_organizacion'], 
            $don['nombre_donante'], 
            $don['correo'], 
            $don['fecha_donacion'], 
            $don['tipo_donacion'], 
            $don['descripcion'], 
            $don['estado'], 
            $don['fecha_entrega'], 
            $don['lugar_entrega']
        ]);
    }

    // Separador
    fputcsv($output, ['']); // Línea en blanco
    fputcsv($output, ['--- Quejas ---']);
    // Encabezados para Quejas
    fputcsv($output, ['ID', 'Tipo Queja', 'Afectado', 'Titulo', 'Descripcion', 'Fecha Queja', 'Estado']);
    foreach ($quejas as $queja) {
        fputcsv($output, [
            $queja['id'],
            $queja['tipo_queja'],
            $queja['nombre_afectado'],
            $queja['titulo'],
            $queja['descripcion'],
            date('Y-m-d H:i:s', strtotime($queja['fecha_queja'])),
            $queja['estado']
        ]);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Donaciones</title>
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
            --color-pendiente: #fd7e14; /* Naranja para estado pendiente de queja */
            --color-resuelta: #20c997; /* Verde turquesa para estado resuelta de queja */

            /* Colores de encabezados y fondos de tabla */
            --color-header-organizaciones: #007bff; /* Azul para organizaciones */
            --bg-tabla-organizaciones: #e6f7ff; /* Tono pastel de azul */

            --color-header-donaciones: #ffc107; /* Amarillo para donaciones */
            --bg-tabla-donaciones: #fffbe6; /* Tono pastel de amarillo */

            --color-header-quejas: #6f42c1; /* Púrpura para quejas */
            --bg-tabla-quejas: #f0e6fa; /* Tono pastel de púrpura */

            /* Colores para acciones de Quejas (modal) */
            --color-queja-principal: #fd7e14; /* Naranja vibrante */
            --color-queja-boton-claro: #007bff; /* Azul */
            --color-queja-boton-claro-hover: #0056b3; /* Azul oscuro al pasar el ratón */
        }
        
        /* Estilos generales del cuerpo */
        body {
            background: url('imagen/fondo.png.jpg') no-repeat center center fixed; /* Fondo fijo */
            background-size: cover; /* Cubre todo */
            font-family: 'Segoe UI', sans-serif; /* Fuente */
            margin: 0; min-height: 100vh; display: flex; flex-direction: column;
        }

        /* Estilos de la barra de navegación */
        .navbar {
            background-color: var(--color-principal); padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Sombra */
        }
        .navbar .frase { color: white; font-weight: 600; font-size: 1.1rem; }
        .btn-menu {
            background-color: white; color: var(--color-principal); font-weight: bold;
            border-radius: 0.5rem; padding: 0.5rem 1rem; transition: all 0.3s ease;
        }
        .btn-menu:hover {
            background-color: var(--color-secundario); color: white; border-color: var(--color-secundario);
        }

        /* Contenedor principal */
        .container-main {
            background: rgba(255,255,255,0.98); /* Fondo semitransparente */
            border-radius: 15px; padding: 30px; margin: 30px auto;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15); /* Sombra */
            flex-grow: 1; max-width: 1400px; width: 95%;
        }

        /* Encabezados H2 */
        h2 { color: var(--color-principal); font-weight: 700; text-align: center; margin-bottom: 30px; }
        /* Encabezados H4 (filtros) */
        .filter-section h4 { 
            font-weight: 700; text-align: center;
            margin-top: 40px; margin-bottom: 20px; font-size: 1.5rem;
            color: var(--color-principal); 
        }

        /* Estilos de las tablas */
        table {
            margin-top: 20px; border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); /* Sombra */
        }
        table th, table td {
            text-align: center; vertical-align: middle; padding: 0.75rem; font-size: 0.95rem;
        }

        /* Colores de encabezados de tablas (Organizaciones) */
        #organizacionesTable thead th { 
            background-color: var(--color-header-organizaciones) !important;
            color: white; border-color: var(--color-header-organizaciones) !important;
        }
        #organizacionesFilter h4 { color: var(--color-header-organizaciones); background-color: transparent !important; }

        /* Colores de encabezados de tablas (Donaciones) */
        #donacionesTable thead th { 
            background-color: var(--color-header-donaciones) !important;
            color: var(--color-texto-oscuro); border-color: var(--color-header-donaciones) !important;
        }
        #donacionesFilter h4 { color: var(--color-header-donaciones); background-color: transparent !important; }

        /* Colores de encabezados de tablas (Quejas) */
        #quejasTable thead th {
            background-color: var(--color-header-quejas) !important;
            color: white; border-color: var(--color-header-quejas) !important;
        }
        #quejasFilter h4 { color: var(--color-header-quejas); background-color: transparent !important; }

        .table-hover tbody tr:hover { background-color: #f1f1f1; }

        /* Fondos de cuerpos de tabla */
        #organizacionesTable tbody { background-color: var(--bg-tabla-organizaciones); }
        #donacionesTable tbody { background-color: var(--bg-tabla-donaciones); }
        #quejasTable tbody { background-color: var(--bg-tabla-quejas); }

        /* Estilos para los botones de estado */
        .btn-estado {
            font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 0.3rem;
            display: inline-block; text-transform: capitalize;
            cursor: pointer; transition: all 0.2s ease;
        }
        .btn-estado:hover { opacity: 0.8; transform: translateY(-1px); }

        /* Clases específicas para los estados */
        .btn-estado-activa { background-color: var(--color-activo); color: white; }
        .btn-estado-inactiva { background-color: var(--color-inactivo); color: white; }
        .btn-estado-en_proceso { background-color: var(--color-proceso); color: var(--color-texto-oscuro); }
        .btn-estado-entregado { background-color: var(--color-entregado); color: white; }
        .btn-estado-cancelado { background-color: var(--color-cancelado); color: white; }
        .btn-estado-pendiente { background-color: var(--color-pendiente); color: white; }
        .btn-estado-resuelta { background-color: var(--color-resuelta); color: white; }

        /* Estilos de la sección de filtros */
        .filter-section {
            background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
            padding: 20px; margin-bottom: 30px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; justify-content: center;
        }
        .filter-section .form-select, .filter-section .form-control { border-radius: 0.3rem; border-color: #ced4da; }
        .filter-section .btn { border-radius: 0.3rem; font-weight: 500; padding: 0.5rem 1.25rem; }
        .filter-section label { margin-bottom: 5px; font-weight: 500; color: var(--color-texto-oscuro); }

        /* Estilos para los gráficos */
        .chart-container {
            background-color: #ffffff; border-radius: 15px; padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 30px;
            height: 400px; display: flex; justify-content: center; align-items: center;
        }
        .chart-container canvas { max-width: 100%; max-height: 100%; }

        /* Contenedor para botones de acción superiores (EDITADO: YA NO NECESARIO) */
        /* .action-buttons-top { display: none; } */ 
        
        /* Ajustes para dispositivos móviles */
        @media (max-width: 768px) {
            .navbar .frase { font-size: 0.9rem; }
            .container-main { padding: 15px; margin: 15px auto; }
            h2 { font-size: 1.8rem; margin-bottom: 20px; }
            h4 { font-size: 1.3rem; margin-top: 25px; margin-bottom: 15px; }
            table th, table td { font-size: 0.85rem; padding: 0.5rem; }
            .filter-section { flex-direction: column; align-items: stretch; }
            .filter-section .col-md-4, .filter-section .col-md-2, .filter-section .col-sm-6 { width: 100%; }
            .filter-section .btn { width: 100%; }
            /* .action-buttons-top { flex-direction: column; align-items: stretch; margin-top: 15px; } */
        }

        /* Estilos del pie de página */
        footer {
            background-color: var(--color-principal); color: white; text-align: center;
            padding: 1rem; font-size: 0.9rem; margin-top: auto;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1); /* Sombra */
        }
    </style>
</head>
<body>
    <nav class="navbar d-flex justify-content-between align-items-center">
        <div class="frase"><i class="bi bi-heart-fill me-2"></i> Panel de Administración 💚</div>
        <a class="btn btn-menu" href="menu.php"><i class="bi bi-house-door-fill me-2"></i> Menú Principal</a>
    </nav>

    <div class="container container-main">
        <h2 class="mb-4"><i class="bi bi-bar-chart-fill me-2"></i> Gráficos Estadísticos</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="tiposDonacionChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="organizacionesEstadoChart"></canvas>
                </div>
            </div>
        </div>

        <hr class="my-5 border-2">
        
        <div class="text-center mb-4">
            <a href="reportes.php?exportar_excel=true" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Exportar Todo a Excel</a>
        </div>

        <h2><i class="bi bi-building-fill me-2"></i> Gestión de Organizaciones</h2>
        <div class="filter-section" id="organizacionesFilter">
            <h4 class="w-100 mb-3 text-center">Filtrar Organizaciones</h4>
            <form action="reportes.php" method="GET" class="row g-3 w-100 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_org_estado" class="form-label">Estado:</label>
                    <select class="form-select" id="filtro_org_estado" name="filtro_org_estado">
                        <option value="">Todos los Estados</option>
                        <option value="activa" <?= (isset($_GET['filtro_org_estado']) && $_GET['filtro_org_estado'] === 'activa') ? 'selected' : '' ?>>Activa</option>
                        <option value="inactiva" <?= (isset($_GET['filtro_org_estado']) && $_GET['filtro_org_estado'] === 'inactiva') ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-2"></i> Filtrar</button>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <a href="reportes.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle-fill me-2"></i> Limpiar</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="organizacionesTable">
                <thead>
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
                        <th>Acciones</th>
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
                                <a href="#" class="btn btn-sm btn-warning edit-organizacion-btn-inline" data-id="<?= $org['id'] ?>" title="Editar Organización"><i class="bi bi-pencil"></i></a>
                                <a href="#" class="btn btn-sm btn-danger delete-organizacion-btn-inline" data-id="<?= $org['id'] ?>" title="Eliminar Organización"><i class="bi bi-trash"></i></a>
                                <?php if ($org['estado'] === 'activa'): ?>
                                    <a href="reportes.php?accion_org_estado=inactiva&id_org=<?= $org['id'] ?>" class="btn btn-sm btn-danger" title="Desactivar Organización"><i class="bi bi-toggle-off"></i></a>
                                <?php else: ?>
                                    <a href="reportes.php?accion_org_estado=activa&id_org=<?= $org['id'] ?>" class="btn btn-sm btn-success" title="Activar Organización"><i class="bi bi-toggle-on"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr class="my-5 border-2">

        <h2><i class="bi bi-box-seam-fill me-2"></i> Gestión de Donaciones</h2>
        <div class="filter-section" id="donacionesFilter">
            <h4 class="w-100 mb-3 text-center">Filtrar Donaciones</h4>
            <form action="reportes.php" method="GET" class="row g-3 w-100 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_don_estado" class="form-label">Estado:</label>
                    <select class="form-select" id="filtro_don_estado" name="filtro_don_estado">
                        <option value="">Todos los Estados</option>
                        <option value="en_proceso" <?= (isset($_GET['filtro_don_estado']) && $_GET['filtro_don_estado'] === 'en_proceso') ? 'selected' : '' ?>>En Proceso</option>
                        <option value="entregado" <?= (isset($_GET['filtro_don_estado']) && $_GET['filtro_don_estado'] === 'entregado') ? 'selected' : '' ?>>Entregado</option>
                        <option value="cancelado" <?= (isset($_GET['filtro_don_estado']) && $_GET['filtro_don_estado'] === 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_don_tipo" class="form-label">Tipo de Donación:</label>
                    <select class="form-select" id="filtro_don_tipo" name="filtro_don_tipo">
                        <option value="">Todos los Tipos</option>
                        <option value="Alimentos" <?= (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] === 'Alimentos') ? 'selected' : '' ?>>Alimentos</option>
                        <option value="Ropa" <?= (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] === 'Ropa') ? 'selected' : '' ?>>Ropa</option>
                        <option value="Dinero" <?= (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] === 'Dinero') ? 'selected' : '' ?>>Dinero</option>
                        <option value="Juguetes" <?= (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] === 'Juguetes') ? 'selected' : '' ?>>Juguetes</option>
                        <option value="Libros" <?= (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] === 'Libros') ? 'selected' : '' ?>>Libros</option>
                        <option value="Artefactos" <?= (isset($_GET['filtro_don_tipo']) && $_GET['filtro_don_tipo'] === 'Artefactos') ? 'selected' : '' ?>>Artefactos</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-2"></i> Filtrar</button>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <a href="reportes.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle-fill me-2"></i> Limpiar</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="donacionesTable">
                <thead>
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
                        <th>Acciones</th>
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
                                <a href="#" class="btn btn-sm btn-warning edit-donacion-btn-inline" data-id="<?= $d['id'] ?>" title="Editar Donación"><i class="bi bi-pencil"></i></a>
                                <a href="#" class="btn btn-sm btn-danger delete-donacion-btn-inline" data-id="<?= $d['id'] ?>" title="Eliminar Donación"><i class="bi bi-trash"></i></a>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar Estado">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="reportes.php?accion_don_estado=en_proceso&id_don=<?= $d['id'] ?>">En Proceso</a></li>
                                        <li><a class="dropdown-item" href="reportes.php?accion_don_estado=entregado&id_don=<?= $d['id'] ?>">Entregado</a></li>
                                        <li><a class="dropdown-item" href="reportes.php?accion_don_estado=cancelado&id_don=<?= $d['id'] ?>">Cancelado</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr class="my-5 border-2">

        <h2><i class="bi bi-exclamation-triangle-fill me-2"></i> Gestión de Quejas</h2>
        <div class="filter-section" id="quejasFilter">
            <h4 class="w-100 mb-3 text-center">Filtrar Quejas</h4>
            <form action="reportes.php" method="GET" class="row g-3 w-100 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <label for="filtro_queja_estado" class="form-label">Estado:</label>
                    <select class="form-select" id="filtro_queja_estado" name="filtro_queja_estado">
                        <option value="">Todos los Estados</option>
                        <option value="pendiente" <?= (isset($_GET['filtro_queja_estado']) && $_GET['filtro_queja_estado'] === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                        <option value="resuelta" <?= (isset($_GET['filtro_queja_estado']) && $_GET['filtro_queja_estado'] === 'resuelta') ? 'selected' : '' ?>>Resuelta</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-2"></i> Filtrar</button>
                </div>
                <div class="col-md-2 col-sm-6 d-flex align-items-end">
                    <a href="reportes.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle-fill me-2"></i> Limpiar</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="quejasTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo Queja</th>
                        <th>Afectado</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Fecha Queja</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quejas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No hay quejas registradas o que coincidan con el filtro.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quejas as $index => $q): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars(ucfirst($q['tipo_queja'])) ?></td>
                            <td><?= htmlspecialchars($q['nombre_afectado'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($q['titulo'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($q['descripcion']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($q['fecha_queja']))) ?></td>
                            <td>
                                <?php
                                $estado_class = '';
                                switch ($q['estado']) {
                                    case 'pendiente': $estado_class = 'btn-estado-pendiente'; break;
                                    case 'resuelta': $estado_class = 'btn-estado-resuelta'; break;
                                    default: $estado_class = 'btn-secondary'; break;
                                }
                                ?>
                                <span class="btn-estado <?= $estado_class ?>"><?= htmlspecialchars(ucfirst($q['estado'])) ?></span>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning edit-queja-btn-inline" data-id="<?= $q['id'] ?>" title="Editar Queja"><i class="bi bi-pencil"></i></a>
                                <a href="#" class="btn btn-sm btn-danger delete-queja-btn-inline" data-id="<?= $q['id'] ?>" title="Eliminar Queja"><i class="bi bi-trash"></i></a>
                                <?php if ($q['estado'] === 'pendiente'): ?>
                                    <a href="reportes.php?accion_queja_estado=resuelta&id_queja=<?= $q['id'] ?>" class="btn btn-sm btn-success" title="Marcar como Resuelta"><i class="bi bi-check-circle-fill"></i></a>
                                <?php else: ?>
                                    <a href="reportes.php?accion_queja_estado=pendiente&id_queja=<?= $q['id'] ?>" class="btn btn-sm btn-warning" title="Marcar como Pendiente"><i class="bi bi-arrow-counterclockwise"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <footer class="text-center py-3">
        Red de Donaciones - Unidos por una causa 🫶
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicialización de gráficos (sin cambios, ya estaban concisos)
            const tiposDonacionLabels = <?= $chart_labels_tipos ?>;
            const tiposDonacionData = <?= $chart_data_tipos ?>;
            const backgroundColors = [
                'rgba(255, 99, 132, 0.7)', 
                'rgba(54, 162, 235, 0.7)', 
                'rgba(255, 206, 86, 0.7)', 
                'rgba(75, 192, 192, 0.7)', 
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)' 
            ];
            const borderColors = [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ];

            const ctx1 = document.getElementById('tiposDonacionChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'pie', 
                    data: {
                        labels: tiposDonacionLabels,
                        datasets: [{
                            label: 'Número de Donaciones',
                            data: tiposDonacionData,
                            backgroundColor: backgroundColors.slice(0, tiposDonacionLabels.length),
                            borderColor: borderColors.slice(0, tiposDonacionLabels.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            title: {
                                display: true,
                                text: 'Donaciones por Tipo de Categoría',
                                font: { size: 18, weight: 'bold' },
                                color: 'var(--color-principal)'
                            }
                        }
                    }
                });
            }

            const orgEstadoLabels = <?= $chart_labels_org_estado ?>;
            const orgEstadoData = <?= $chart_data_org_estado ?>;
            const orgEstadoColors = {
                'activa': 'rgba(40, 167, 69, 0.7)', 
                'inactiva': 'rgba(220, 53, 69, 0.7)'
            };
            const orgEstadoBorderColors = {
                'activa': 'rgba(40, 167, 69, 1)',
                'inactiva': 'rgba(220, 53, 69, 1)'
            };

            const ctx2 = document.getElementById('organizacionesEstadoChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'bar', 
                    data: {
                        labels: orgEstadoLabels,
                        datasets: [{
                            label: 'Número de Organizaciones',
                            data: orgEstadoData,
                            backgroundColor: orgEstadoLabels.map(label => orgEstadoColors[label] || 'rgba(108, 117, 125, 0.7)'),
                            borderColor: orgEstadoLabels.map(label => orgEstadoBorderColors[label] || 'rgba(108, 117, 125, 1)'),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'Organizaciones por Estado',
                                font: { size: 18, weight: 'bold' },
                                color: 'var(--color-principal)'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }

            // JavaScript para Editar Organización (integrado en cada fila)
            document.querySelectorAll('.edit-organizacion-btn-inline').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.dataset.id;
                    const campo = prompt("¿Qué campo de la organización deseas editar? (nombre, tipo_necesidad, descripcion, contacto, correo, fecha_final)");
                    if (campo) {
                        const valor = prompt(`Introduce el nuevo valor para ${campo}:`);
                        if (valor !== null) {
                            window.location.href = `reportes.php?accion_org_editar=true&id_org_edit=${id}&campo=${campo}&valor=${encodeURIComponent(valor)}`;
                        }
                    } else if (campo !== null) { 
                        alert("Debes ingresar un campo para editar.");
                    }
                });
            });

            // JavaScript para Eliminar Organización (integrado en cada fila)
            document.querySelectorAll('.delete-organizacion-btn-inline').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.dataset.id;
                    if (confirm(`¿Estás seguro de que quieres eliminar la organización con ID ${id}? Esta acción no se puede deshacer.`)) {
                        window.location.href = `reportes.php?accion_org_eliminar=true&id_org_del=${id}`;
                    }
                });
            });

            // JavaScript para Editar Donación (integrado en cada fila)
            document.querySelectorAll('.edit-donacion-btn-inline').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.dataset.id;
                    const campo = prompt("¿Qué campo de la donación deseas editar? (descripcion, lugar_entrega, fecha_entrega, tipo_donacion)"); 
                    if (campo) {
                        const valor = prompt(`Introduce el nuevo valor para ${campo}:`);
                        if (valor !== null) {
                            window.location.href = `reportes.php?accion_don_editar=true&id_don_edit=${id}&campo=${campo}&valor=${encodeURIComponent(valor)}`;
                        }
                    } else if (campo !== null) {
                        alert("Debes ingresar un campo para editar.");
                    }
                });
            });

            // JavaScript para Eliminar Donación (integrado en cada fila)
            document.querySelectorAll('.delete-donacion-btn-inline').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.dataset.id;
                    if (confirm(`¿Estás seguro de que quieres eliminar la donación con ID ${id}? Esta acción no se puede deshacer.`)) {
                        window.location.href = `reportes.php?accion_don_eliminar=true&id_don_del=${id}`;
                    }
                });
            });

            // JavaScript para Editar Queja (integrado en cada fila)
            document.querySelectorAll('.edit-queja-btn-inline').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.dataset.id;
                    const campo = prompt("¿Qué campo de la queja deseas editar? (titulo, descripcion)");
                    if (campo) {
                        const valor = prompt(`Introduce el nuevo valor para ${campo}:`);
                        if (valor !== null) {
                            window.location.href = `reportes.php?accion_queja_editar=true&id_queja_edit=${id}&campo=${campo}&valor=${encodeURIComponent(valor)}`;
                        }
                    } else if (campo !== null) {
                        alert("Debes ingresar un campo para editar.");
                    }
                });
            });

            // JavaScript para Eliminar Queja (integrado en cada fila)
            document.querySelectorAll('.delete-queja-btn-inline').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.dataset.id;
                    if (confirm(`¿Estás seguro de que quieres eliminar la queja con ID ${id}? Esta acción no se puede deshacer.`)) {
                        window.location.href = `reportes.php?accion_queja_eliminar=true&id_queja_del=${id}`;
                    }
                });
            });
        });
    </script>
</body>
</html>