<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /login.php');
    exit;
}

// --- AJAX para scroll infinito ---
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    require_once '../config.php';
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 40;
    try {
        $stmt = $pdo->prepare('SELECT v.vehiculo_id, v.marca, v.modelo, v.placas, v.vin, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido FROM vehiculos v LEFT JOIN clientes c ON v.cliente_id = c.cliente_id ORDER BY v.vehiculo_id DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($vehiculos);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

require_once '../config.php';
require_once '../header.php';
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 justify-content-center">
            <img src="/assets/img/ford.jpg" alt="Ford" style="height:100px;width:auto;border-radius:10px;box-shadow:0 1px 4px #0002;">
            <h1 class="fw-light mb-0 text-center" style="font-size:2rem; width:100%;">Gestión de Vehículos</h1>
        </div>
        <a href="crear.php" class="btn btn-success shadow-none ms-md-4 mt-3 mt-md-0" style="border-radius: 20px; background: #2ecc40; border: none; white-space:nowrap;">
            <i class="fas fa-plus me-2"></i>Agregar
        </a>
    </div>
    <input type="text" id="busquedaVehiculos" class="form-control mb-3 shadow-none" placeholder="Buscar en vehículos..." style="border-radius: 20px; border: 1px solid #e0e0e0;">
    <div class="card border-0 shadow-sm" style="background: #fafbfc;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaVehiculos" style="background: transparent;">
                    <thead style="background: #f4f6f8;">
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <th class="fw-normal small">ID</th>
                            <th class="fw-normal small">Cliente</th>
                            <th class="fw-normal small">Marca</th>
                            <th class="fw-normal small">Modelo</th>
                            <th class="fw-normal small">Placas</th>
                            <th class="fw-normal small">VIN</th>
                            <th class="fw-normal small text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyVehiculos">
                        <!-- Las filas se cargarán por AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
// Scroll infinito y carga AJAX con manejo de errores
let offset = 0;
const limit = 40;
let cargando = false;
let fin = false;
const tablaVehiculos = document.getElementById('tbodyVehiculos');
const inputVehiculos = document.getElementById('busquedaVehiculos');

function formatearVehiculo(vehiculo) {
    return `<tr style="border-bottom: 1px solid #f0f0f0;">
        <td class="text-muted">${vehiculo.vehiculo_id}</td>
        <td style="max-width:180px;">
            <span style="white-space:normal; word-break:break-word;">${(vehiculo.cliente_nombre || '') + ' ' + (vehiculo.cliente_apellido || '')}</span>
        </td>
        <td>${vehiculo.marca || ''}</td>
        <td>${vehiculo.modelo || ''}</td>
        <td>${vehiculo.placas || ''}</td>
        <td>${vehiculo.vin || ''}</td>
        <td class="text-center" style="white-space: nowrap;">
            <a href="editar.php?id=${vehiculo.vehiculo_id}" class="btn btn-light btn-sm shadow-none me-2" style="border-radius: 16px; border: 1px solid #e0e0e0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#0d6efd" class="bi bi-pencil" viewBox="0 0 16 16">
                  <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zm.708-.708a1.5 1.5 0 0 0-2.121 0l-10 10a1.5 1.5 0 0 0-.328.497l-2 5a1.5 1.5 0 0 0 1.95 1.95l5-2a1.5 1.5 0 0 0 .497-.328l10-10a1.5 1.5 0 0 0 0-2.121l-2.292-2.292z"/>
                </svg>
            </a>
            <a href="eliminar.php?id=${vehiculo.vehiculo_id}" class="btn btn-light btn-sm shadow-none" style="border-radius: 16px; border: 1px solid #e0e0e0;" onclick="return confirm('¿Estás seguro de que deseas eliminar este vehículo?');">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dc3545" class="bi bi-trash" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6zm2.5-.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5z"/>
                  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2h3.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1H14.5a1 1 0 0 1 1 1zm-11-1a.5.5 0 0 0-.5.5V4h11V2.5a.5.5 0 0 0-.5-.5h-10z"/>
                </svg>
            </a>
        </td>
    </tr>`;
}

function mostrarError(msg) {
    let errorDiv = document.getElementById('errorVehiculos');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'errorVehiculos';
        errorDiv.className = 'alert alert-danger border-0 rounded-3 mt-3 text-center';
        errorDiv.style.background = '#ffeaea';
        tablaVehiculos.parentElement.parentElement.insertBefore(errorDiv, tablaVehiculos.parentElement);
    }
    errorDiv.textContent = msg;
}

function cargarVehiculos() {
    if (cargando || fin) return;
    cargando = true;
    fetch('index.php?ajax=1&offset=' + offset + '&limit=' + limit)
        .then(res => {
            if (!res.ok) throw new Error('Error en la respuesta del servidor');
            return res.json();
        })
        .then(data => {
            if (!Array.isArray(data)) {
                mostrarError('Respuesta inesperada del servidor.');
                cargando = false;
                return;
            }
            if (data.length < limit) fin = true;
            data.forEach(vehiculo => {
                tablaVehiculos.insertAdjacentHTML('beforeend', formatearVehiculo(vehiculo));
            });
            offset += data.length;
            cargando = false;
        })
        .catch(err => {
            mostrarError('No se pudieron cargar los vehículos: ' + err.message);
            cargando = false;
        });
}

tablaVehiculos.innerHTML = '';
cargarVehiculos();

window.addEventListener('scroll', function() {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
        cargarVehiculos();
    }
});

inputVehiculos.addEventListener('keyup', function() {
    let filtro = inputVehiculos.value.toLowerCase();
    let filas = tablaVehiculos.getElementsByTagName('tr');
    for (let i = 0; i < filas.length; i++) {
        let textoFila = filas[i].textContent.toLowerCase();
        filas[i].style.display = textoFila.includes(filtro) ? '' : 'none';
    }
});
</script>
<style>
body {
    background: #f7f8fa;
}
.table > :not(:last-child) > :last-child > * {
    border-bottom-color: #f0f0f0;
}
.table th, .table td {
    border-top: none;
    vertical-align: middle;
}
input:focus, .btn:focus {
    box-shadow: none !important;
}
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
<?php require_once '../footer.php'; ?>