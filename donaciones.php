<?php
require 'db/conexion.php'; // Asegúrate de que esta ruta sea correcta
$mensaje = '';

// Obtener organizaciones registradas
// Traer todas las organizaciones para ser filtradas en el lado del cliente con JavaScript
$stmt = $pdo->query("SELECT * FROM organizaciones");
$organizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtener como array asociativo para facilitar el manejo con JS

// Procesar donación si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizacion = $_POST['organizacion'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $estado = 'en_proceso'; // estado por defecto
    $fecha_entrega = $_POST['fecha_entrega'] ?? null;
    $lugar_entrega = $_POST['lugar_entrega'] ?? null;

    // Validación básica del lado del servidor (puede expandirse)
    if (empty($organizacion) || empty($nombre) || empty($correo) || empty($fecha) || empty($tipo) || empty($descripcion)) {
        $mensaje = "Error: Todos los campos obligatorios deben ser rellenados.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO donaciones (organizacion, nombre_donante, correo, fecha_donacion, tipo_donacion, descripcion, estado, fecha_entrega, lugar_entrega)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$organizacion, $nombre, $correo, $fecha, $tipo, $descripcion, $estado, $fecha_entrega, $lugar_entrega])) {
            $mensaje = "Donación registrada con éxito 🙌";
            // Limpiar campos del formulario después del envío exitoso (opcional, para mejor UX)
            // Podrías redirigir o reiniciar el formulario aquí si es necesario.
        } else {
            $mensaje = "Error al registrar la donación. Por favor, inténtelo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Red de Donaciones - ¡Ayuda Hoy!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
    :root {
        --color-principal:rgb(8, 107, 56);
        --color-secundario:rgb(20, 115, 58);
        --color-terciario: #e0f2f1; /* Verde claro para fondos de tarjetas por defecto */

        /* Colores de fondo específicos para cada tipo de tarjeta */
        --bg-alimentos: rgb(250, 255, 235); /* Un tono más suave para alimentos */
        --bg-ropa: #e3f2fd;    /* Azul claro */
        --bg-dinero: #e8f5e9;  /* Verde claro */
        --bg-juguetes: #fffde7; /* Amarillo muy claro */
        --bg-libros: #f3e5f5;  /* Púrpura claro */
        --bg-artefactos: #e0f7fa; /* Cian muy claro */
    }

    body {
        background: url('https://static.vecteezy.com/system/resources/thumbnails/011/787/776/small/abstract-green-and-blue-fluid-wave-background-free-vector.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Segoe UI', sans-serif;
        min-height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .navbar {
        background-color: var(--color-principal);
        /* Eliminamos padding-top y padding-bottom aquí y usamos clases de Bootstrap en HTML */
    }

    .navbar-brand {
        color: white;
        font-weight: bold;
    }

    .btn-volver {
        background-color: white;
        color: var(--color-principal);
        font-weight: bold;
        border: 2px solid white;
        transition: all 0.3s ease;
    }

    .btn-volver:hover {
        background-color: var(--color-secundario);
        color: white;
        border-color: var(--color-secundario);
    }

    .container {
        padding-top: 10px; /* Reducir padding superior */
        padding-bottom: 10px; /* Reducir padding inferior */
        flex-grow: 1; /* Permite que el contenedor crezca y empuje el pie de página hacia abajo */
    }

    /* Ajuste para el contenedor de las columnas para asegurar la misma altura en las filas */
    .row {
        align-items: stretch; /* ESTO ES CLAVE: Asegura que todas las columnas dentro de una fila tengan la misma altura */
    }

    /* Estilos para las tarjetas de categoría */
    .category-card {
        background-color: var(--color-terciario); /* Fondo por defecto */
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra más suave */
        text-align: center;
        margin-bottom: 20px; /* Espacio entre tarjetas, ligeramente reducido */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        /* eliminamos min-height aquí para dejar que la altura se defina por el contenido y flexbox */
        height: 380px; /* **ALTURA FIJA** para todas las tarjetas */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-bottom: 5px solid; /* Borde inferior para color */
    }
    
    /* Colores de borde y fondo para cada tipo de categoría */
    .category-card[data-type="Alimentos"] { border-color: #f44336; background-color: var(--bg-alimentos); } /* Rojo y fondo claro */
    .category-card[data-type="Ropa"] { border-color: #2196f3; background-color: var(--bg-ropa); } /* Azul y fondo claro */
    .category-card[data-type="Dinero"] { border-color: #4CAF50; background-color: var(--bg-dinero); } /* Verde y fondo claro */
    .category-card[data-type="Juguetes"] { border-color: #ffc107; background-color: var(--bg-juguetes); } /* Amarillo y fondo claro */
    .category-card[data-type="Libros"] { border-color: #9c27b0; background-color: var(--bg-libros); } /* Púrpura y fondo claro */
    .category-card[data-type="Artefactos"] { border-color: #00bcd4; background-color: var(--bg-artefactos); } /* Cian y fondo claro */


    .category-card:hover {
        transform: translateY(-8px); /* Animación al pasar el ratón */
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.25); /* Sombra más pronunciada al hover */
    }

    .category-card h3 {
        color: var(--color-principal);
        font-weight: bold;
        margin-bottom: 15px;
        font-size: 1.8rem;
    }

    .category-card .icon-img {
        max-width: 90px;
        height: 100px; /* **ALTURA FIJA** para las imágenes */
        object-fit: contain; /* Ajusta la imagen dentro del contenedor sin recortar, mostrando la imagen completa */
        margin: 0 auto 20px;
        display: block;
    }

    .category-card p {
        color: #333;
        font-size: 0.95rem;
        flex-grow: 1; /* Permite que el párrafo ocupe el espacio disponible */
        margin-bottom: 15px; /* Reducir espacio debajo del párrafo */
    }

    .card-buttons {
        margin-top: auto; /* Empuja los botones al final del contenedor flex */
    }

    .btn-outline-success {
        color: var(--color-principal);
        border-color: var(--color-principal);
        transition: all 0.3s ease;
    }
    .btn-outline-success:hover {
        background-color: var(--color-principal);
        color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-donar {
        background-color: var(--color-principal);
        color: white;
        font-weight: bold;
        border: none;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-donar:hover {
        background-color: var(--color-secundario);
        color: white; /* Cambiado a blanco para mejor contraste */
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    /* Estilos para las secciones dinámicas (detalles y formulario) */
    .dynamic-section {
        background-color: rgba(255, 255, 255, 0.98);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        margin-top: 30px; /* Reducir margen superior */
        display: none; /* Oculto por defecto */
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .btn-close-section {
        background-color: var(--color-secundario);
        color: white;
        font-weight: bold;
        border: none;
        transition: background-color 0.3s ease;
    }

    .btn-close-section:hover {
        background-color: var(--color-principal);
    }

    /* Estilos específicos de tabla para la sección dinámica */
    .table-responsive .table {
        margin-bottom: 0; /* Eliminar margen de tabla por defecto */
    }

    table.table-bordered thead th {
        background-color: var(--color-principal);
        color: white;
        font-weight: bold;
    }
    table.table-bordered tbody td {
        font-size: 0.9rem;
    }

    /* Estilos específicos del formulario */
    #formDonacion input,
    #formDonacion select,
    #formDonacion textarea {
        background-color: white !important;
        border: 1.5px solid var(--color-principal);
        border-radius: 6px;
        color: black;
    }
    /* Estilo para mensajes de feedback inválidos - ahora SOLO se muestran cuando son inválidos */
    .form-control:not(.is-valid):not(.is-invalid) + .invalid-feedback,
    .form-select:not(.is-valid):not(.is-invalid) + .invalid-feedback {
        display: none; /* Ocultar feedback inicialmente */
    }
    .form-control.is-invalid ~ .invalid-feedback,
    .form-select.is-invalid ~ .invalid-feedback {
        display: block; /* Mostrar feedback solo cuando es inválido */
    }
    .form-control.is-valid, .form-select.is-valid {
        border-color: #28a745 !important; /* Borde verde para válido */
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545 !important; /* Borde rojo para inválido */
    }


    /* Estilo de iconos */
    .bi {
        margin-right: 8px; /* Espaciado para iconos junto al texto */
    }

    footer {
        background-color: var(--color-principal);
        color: white;
        text-align: center;
        padding: 15px;
        font-size: 25px;
        margin-top: auto; /* Empuja el pie de página hacia abajo */
    }
</style>
        
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark py-2"> <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-heart-fill"></i> Red de Donaciones</a>
            <button class="btn btn-volver ms-auto" onclick="window.location.href='menu.php'">
                <i class="bi bi-house-door-fill"></i> Menú Principal
            </button>
        </div>
    </nav>

    <div class="container">
        <?php if ($mensaje): ?>
            <div class="alert alert-info text-center mt-3 mb-2 fade show" role="alert"> <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="text-center mb-4"><i class="bi bi-grid-fill"></i> Explora Nuestras Necesidades por Categoría</h2> <div class="row">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="category-card" data-type="Alimentos">
                    <h3>Alimentos</h3>
                    <img src="https://jappi.com.co/wp-content/uploads/2020/08/Clasificacion-de-los-alimentos-imagen-destacada.jpg" alt="Icono Alimentos" class="icon-img">
                    <p>¡Dona alimentos no perecederos y ayuda a combatir el hambre en nuestra comunidad!</p>
                    <div class="card-buttons">
                        <button class="btn btn-outline-success w-100 mb-2 view-details-btn" data-type="Alimentos">
                            <i class="bi bi-search"></i> Ver Detalles
                        </button>
                        <button class="btn btn-donar w-100 donate-btn" data-type="Alimentos">
                            <i class="bi bi-bag-plus-fill"></i> Donar Alimentos
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="category-card" data-type="Ropas">
                    <h3>Ropas</h3>
                    <img src="https://traperosdeemauslimaperu.org/images/2019/10/16/BLOG002.png" alt="Icono Ropa" class="icon-img">
                    <p>Ofrece abrigo y dignidad: dona ropa en buen estado para todas las edades.</p>
                    <div class="card-buttons">
                        <button class="btn btn-outline-success w-100 mb-2 view-details-btn" data-type="Ropas">
                            <i class="bi bi-search"></i> Ver Detalles
                        </button>
                        <button class="btn btn-donar w-100 donate-btn" data-type="Ropas">
                            <i class="bi bi-bag-plus-fill"></i> Donar Ropa
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="category-card" data-type="Dinero">
                    <h3>Dinero</h3>
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSqJaDqvUsDn3auqrwigox3sIvPCXFQz3bF9A&s" alt="Icono Dinero" class="icon-img">
                    <p>Tu contribución financiera impulsa directamente nuestras causas más urgentes.</p>
                    <div class="card-buttons">
                        <button class="btn btn-outline-success w-100 mb-2 view-details-btn" data-type="Dinero">
                            <i class="bi bi-search"></i> Ver Detalles
                        </button>
                        <button class="btn btn-donar w-100 donate-btn" data-type="Dinero">
                            <i class="bi bi-bag-plus-fill"></i> Donar Dinero
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="category-card" data-type="Juguetes">
                    <h3>Juguetes</h3>
                    <img src="https://img.freepik.com/vector-premium/caja-donacion-ilustracion-vector-concepto-caridad-juguetes-ninos_313437-1183.jpg" alt="Icono Juguetes" class="icon-img">
                    <p>Regala una sonrisa: dona juguetes nuevos o en excelente estado para los niños.</p>
                    <div class="card-buttons">
                        <button class="btn btn-outline-success w-100 mb-2 view-details-btn" data-type="Juguetes">
                            <i class="bi bi-search"></i> Ver Detalles
                        </button>
                        <button class="btn btn-donar w-100 donate-btn" data-type="Juguetes">
                            <i class="bi bi-bag-plus-fill"></i> Donar Juguetes
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="category-card" data-type="Libros">
                    <h3>Libros</h3>
                    <img src="https://reciclamosparaayudar.org/images/2020/03/24/03-768x598.png" alt="Icono Libros" class="icon-img">
                    <p>Abre mundos de conocimiento: dona libros que inspiren y eduquen.</p>
                    <div class="card-buttons">
                        <button class="btn btn-outline-success w-100 mb-2 view-details-btn" data-type="Libros">
                            <i class="bi bi-search"></i> Ver Detalles
                        </button>
                        <button class="btn btn-donar w-100 donate-btn" data-type="Libros">
                            <i class="bi bi-bag-plus-fill"></i> Donar Libros
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="category-card" data-type="Artefactos">
                    <h3>Artefactos</h3>
                    <img src="https://www.traperosdeemaussanpablo.org/images/2022/06/13/donar-artefactos---001.jpg" alt="Icono Artefactos" class="icon-img">
                    <p>Facilita la vida diaria: dona electrodomésticos y dispositivos funcionales.</p>
                    <div class="card-buttons">
                        <button class="btn btn-outline-success w-100 mb-2 view-details-btn" data-type="Artefactos">
                            <i class="bi bi-search"></i> Ver Detalles
                        </button>
                        <button class="btn btn-donar w-100 donate-btn" data-type="Artefactos">
                            <i class="bi bi-bag-plus-fill"></i> Donar Artefactos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        ---

        <div id="detailsSection" class="dynamic-section">
            <h3 class="text-center text-success mb-4" id="detailsTitle"><i class="bi bi-info-circle-fill"></i></h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>Organización</th>
                            <th>Tipo de Necesidad</th>
                            <th>Descripción</th>
                            <th>Contacto</th>
                            <th>Correo</th>
                            <th>Fecha Registro</th>
                            <th>Fecha Final</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="detailsTableBody">
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-close-section" id="closeDetailsBtn">
                    <i class="bi bi-x-circle-fill"></i> Cerrar Detalles
                </button>
            </div>
        </div>

        ---

        <div id="donationFormSection" class="dynamic-section">
            <h3 class="text-center text-success mb-4"><i class="bi bi-hand-heart-fill"></i> Formulario de Donación para <span id="selectedDonationTypeDisplay"></span></h3>
            <form id="formDonacion" method="POST" novalidate> 
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Tu Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
                        <div class="invalid-feedback">Por favor, ingresa tu nombre completo (mínimo dos palabras).</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label">Tu Correo Electrónico</label>
                        <input type="email" name="correo" id="correo" class="form-control" required placeholder="Ej: tu.correo@example.com">
                        <div class="invalid-feedback">Por favor, ingresa un correo electrónico válido (debe contener '@').</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">Fecha de Tu Donación</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" required>
                        <div class="invalid-feedback">Por favor, selecciona una fecha válida (no puede ser en el pasado).</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Donación</label>
                        <select name="tipo" id="donationTypeSelect" class="form-select" required>
                            <option value="">Seleccione el tipo</option>
                            <option value="Alimentos">Alimentos</option>
                            <option value="Ropas">Ropas</option>
                            <option value="Dinero">Dinero</option>
                            <option value="Juguetes">Juguetes</option>
                            <option value="Libros">Libros</option>
                            <option value="Artefactos">Artefactos</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecciona un tipo de donación.</div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="descripcion" class="form-label">Descripción Detallada de Tu Donación</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3" required placeholder="Ej: 5 kg de arroz, 3 abrigos para niño, 1 caja de paracetamol..."></textarea>
                        <div class="invalid-feedback">Por favor, describe tu donación.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lugar_entrega" class="form-label">Lugar de Entrega (Opcional)</label>
                        <input type="text" name="lugar_entrega" id="lugar_entrega" class="form-control" placeholder="Ej: Mi domicilio, local de la organización...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_entrega" class="form-label">Fecha Estimada de Entrega (Opcional)</label>
                        <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control">
                        <div class="invalid-feedback">La fecha estimada de entrega no puede ser en el pasado.</div>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="organizationSelect" class="form-label">Organización a la que deseas Donar</label>
                    <select name="organizacion" id="organizationSelect" class="form-select" required>
                        <option value="">Seleccione una organización</option>
                        </select>
                    <div class="invalid-feedback">Por favor, selecciona una organización.</div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-donar py-2">
                        <i class="bi bi-check2-circle"></i> Confirmar Mi Donación
                    </button>
                </div>
            </form>
        </div>

        <div id="donationStatusDisplay" class="alert alert-info text-center mt-3"> Estado de la Donación: <strong>En Proceso</strong> <i class="bi bi-hourglass-split"></i>
        </div>

    </div>

    <footer>
        <p>Gracias por tu apoyo solidario 💚</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pasar datos de organizaciones PHP a JavaScript
        const organizaciones = <?= json_encode($organizaciones) ?>;
        
        // Elementos del DOM
        const detailsSection = document.getElementById('detailsSection');
        const detailsTitle = document.getElementById('detailsTitle');
        const detailsTableBody = document.getElementById('detailsTableBody');
        const closeDetailsBtn = document.getElementById('closeDetailsBtn');

        const donationFormSection = document.getElementById('donationFormSection');
        const selectedDonationTypeDisplay = document.getElementById('selectedDonationTypeDisplay');
        const donationTypeSelect = document.getElementById('donationTypeSelect');
        const organizationSelect = document.getElementById('organizationSelect');
        const formDonacion = document.getElementById('formDonacion');

        // Campos de entrada para validación en tiempo real
        const nombreInput = document.getElementById('nombre');
        const correoInput = document.getElementById('correo');
        const fechaInput = document.getElementById('fecha');
        const fechaEntregaInput = document.getElementById('fecha_entrega'); // Nuevo elemento para la fecha de entrega
        
        // Establecer la fecha mínima para la fecha de donación a hoy
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normalizar al inicio del día
        fechaInput.min = today.toISOString().split('T')[0];
        fechaEntregaInput.min = today.toISOString().split('T')[0]; // Establecer fecha mínima para la fecha de entrega

        // Función para mostrar/ocultar secciones y desplazarse a la vista
        function showSection(sectionElement) {
            // Ocultar todas las secciones dinámicas primero
            detailsSection.style.display = 'none';
            donationFormSection.style.display = 'none';

            // Mostrar la sección objetivo
            sectionElement.style.display = 'block';
            sectionElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // --- Event Listeners para Tarjetas de Categoría ---

        // Botones "Ver Detalles"
        document.querySelectorAll('.view-details-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Convertir el tipo de dato del botón a minúsculas para la comparación
                const type = this.dataset.type.toLowerCase(); 
                detailsTitle.innerHTML = `<i class="bi bi-info-circle-fill"></i> Detalles de Necesidades de ${this.dataset.type}`; // Mostrar capitalización original
                detailsTableBody.innerHTML = ''; // Limpiar detalles anteriores

                // Filtrar organizaciones, convirtiendo org.tipo_necesidad a minúsculas para la comparación
                const filteredOrgs = organizaciones.filter(org => 
                    org.tipo_necesidad && org.tipo_necesidad.toLowerCase() === type
                );

                if (filteredOrgs.length > 0) {
                    filteredOrgs.forEach(org => {
                        const row = `
                            <tr>
                                <td>${org.nombre ? htmlspecialchars(org.nombre) : 'N/A'}</td>
                                <td>${org.tipo_necesidad ? htmlspecialchars(org.tipo_necesidad) : 'N/A'}</td>
                                <td>${org.descripcion ? htmlspecialchars(org.descripcion) : 'N/A'}</td>
                                <td>${org.contacto ? htmlspecialchars(org.contacto) : 'N/A'}</td>
                                <td>${org.correo ? htmlspecialchars(org.correo) : 'N/A'}</td>
                                <td>${org.fecha_registro ? htmlspecialchars(org.fecha_registro) : 'N/A'}</td>
                                <td>${org.fecha_final ? htmlspecialchars(org.fecha_final) : 'N/A'}</td>
                                <td>${org.estado ? htmlspecialchars(org.estado) : 'N/A'}</td>
                            </tr>
                        `;
                        detailsTableBody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    detailsTableBody.innerHTML = `<tr><td colspan="8" class="text-muted py-4">No hay necesidades registradas para "${this.dataset.type}" en este momento. ¡Sé el primero en donar!</td></tr>`;
                }
                showSection(detailsSection);
            });
        });

        // Botón "Cerrar Detalles"
        closeDetailsBtn.addEventListener('click', () => {
            detailsSection.style.display = 'none';
            // Opcional: Desplazarse de nuevo a la sección de tarjetas de categoría
            document.querySelector('.container h2').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Botones "Donar [Tipo]"
        document.querySelectorAll('.donate-btn').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type; // Mantener la capitalización original para la visualización
                const typeLowerCase = type.toLowerCase(); // Convertir a minúsculas para filtrar
                selectedDonationTypeDisplay.textContent = type;
                
                // Preseleccionar el tipo de donación en el menú desplegable del formulario
                donationTypeSelect.value = type;

                // Rellenar el menú desplegable "Organización a Donar" con organizaciones filtradas
                organizationSelect.innerHTML = '<option value="">Seleccione una organización</option>';
                // Filtrar organizaciones, convirtiendo org.tipo_necesidad a minúsculas para la comparación
                const filteredOrgsForDonation = organizaciones.filter(org => 
                    org.tipo_necesidad && org.tipo_necesidad.toLowerCase() === typeLowerCase
                );
                
                if (filteredOrgsForDonation.length > 0) {
                    filteredOrgsForDonation.forEach(org => {
                        const option = document.createElement('option');
                        option.value = org.nombre;
                        option.textContent = org.nombre;
                        organizationSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = `No hay organizaciones activas para ${type}`;
                    option.disabled = true; // Deshabilitar selección si no hay organizaciones
                    organizationSelect.appendChild(option);
                }

                // Mostrar el formulario de donación
                showSection(donationFormSection);
            });
        });

        // --- Validación de Formulario del Lado del Cliente ---

        // Función para validar un solo campo
        function validateField(field) {
            if (field.checkValidity()) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
            }
        }

        // Lógica de validación específica para cada campo
        function validateNombre() {
            const value = nombreInput.value.trim();
            const words = value.split(/\s+/).filter(word => word.length > 0);
            if (words.length >= 2) {
                nombreInput.setCustomValidity('');
            } else {
                nombreInput.setCustomValidity('Por favor, ingresa tu nombre completo (mínimo dos palabras).');
            }
            validateField(nombreInput);
        }

        function validateCorreo() {
            const value = correoInput.value.trim();
            // Comprobación simple para '@'
            if (value.includes('@')) {
                correoInput.setCustomValidity('');
            } else {
                correoInput.setCustomValidity('Por favor, ingresa un correo electrónico válido (debe contener "@").');
            }
            validateField(correoInput);
        }

        function validateFecha(inputElement) {
            const selectedDate = new Date(inputElement.value);
            const currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0); // Normalizar al inicio del día para una comparación precisa

            if (selectedDate < currentDate) {
                inputElement.setCustomValidity('La fecha no puede ser en el pasado.');
            } else {
                inputElement.setCustomValidity('');
            }
            validateField(inputElement);
        }
        
        // Event Listeners para validación en tiempo real
        nombreInput.addEventListener('input', validateNombre);
        correoInput.addEventListener('input', validateCorreo);
        fechaInput.addEventListener('change', () => validateFecha(fechaInput)); 
        fechaEntregaInput.addEventListener('change', () => validateFecha(fechaEntregaInput)); // Validar fecha de entrega
        donationTypeSelect.addEventListener('change', () => validateField(donationTypeSelect));
        organizationSelect.addEventListener('change', () => validateField(organizationSelect));
        document.getElementById('descripcion').addEventListener('input', () => validateField(document.getElementById('descripcion')));


        formDonacion.addEventListener('submit', function(event) {
            // Volver a ejecutar todas las validaciones personalizadas y marcar el formulario como validado
            validateNombre();
            validateCorreo();
            validateFecha(fechaInput);
            
            // Validar fecha_entrega solo si tiene un valor
            if (fechaEntregaInput.value) {
                validateFecha(fechaEntregaInput);
            } else {
                // Si el campo opcional está vacío, limpiar cualquier estado de validación existente
                fechaEntregaInput.classList.remove('is-valid', 'is-invalid');
            }

            validateField(donationTypeSelect);
            validateField(organizationSelect);
            validateField(document.getElementById('descripcion'));

            // Marcar el formulario como validado para mostrar el feedback integrado de Bootstrap
            formDonacion.classList.add('was-validated');

            // Verificar si el formulario es válido después de todas las validaciones
            if (!formDonacion.checkValidity()) {
                event.preventDefault(); // Detener el envío del formulario si hay errores de validación
                event.stopPropagation();
            }
        });

        // Función auxiliar para escape de HTML en JS (similar a htmlspecialchars de PHP)
        function htmlspecialchars(str) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    </script>
</body>
</html>