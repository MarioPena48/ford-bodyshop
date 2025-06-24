<?php
// --- AJAX para scroll infinito ---
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    require_once __DIR__ . '/../config.php';
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 40;
    $rol = $_SESSION['rol'] ?? ($_COOKIE['usuario_rol'] ?? null);
    $sql = "SELECT s.*, v.marca, v.modelo, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido";
    if ($rol === 'Administrador') {
        $sql .= ", u.nombre_usuario AS creado_por_nombre";
    }
    $sql .= " FROM servicios s JOIN vehiculos v ON s.vehiculo_id = v.vehiculo_id JOIN clientes c ON v.cliente_id = c.cliente_id";
    if ($rol === 'Administrador') {
        $sql .= " LEFT JOIN usuarios u ON s.creado_por = u.usuario_id";
    }
    $sql .= " ORDER BY s.fecha_recepcion DESC LIMIT :limit OFFSET :offset";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($servicios);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

require_once __DIR__ . '/../config.php'; // Incluye el archivo de configuración
require_once __DIR__ . '/../header.php'; // Incluye el encabezado
$rol = $_SESSION['rol'] ?? ($_COOKIE['usuario_rol'] ?? null);

// Lógica para eliminar un servicio
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $servicio_id = $_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM servicios WHERE servicio_id = ?"); //
        $stmt->execute([$servicio_id]);
        echo '<div class="alert alert-success" role="alert">Servicio eliminado correctamente.</div>';
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger" role="alert">Error al eliminar el servicio: ' . $e->getMessage() . '</div>';
    }
}

// Obtener todos los servicios
try {
    $sql = "SELECT s.*, v.marca, v.modelo, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido";
    if ($rol === 'Administrador') {
        $sql .= ", u.nombre_usuario AS creado_por_nombre";
    }
    $sql .= " FROM servicios s JOIN vehiculos v ON s.vehiculo_id = v.vehiculo_id JOIN clientes c ON v.cliente_id = c.cliente_id";
    if ($rol === 'Administrador') {
        $sql .= " LEFT JOIN usuarios u ON s.creado_por = u.usuario_id";
    }
    $sql .= " ORDER BY s.fecha_recepcion DESC";
    $stmt = $pdo->query($sql);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">Error al cargar los servicios: ' . $e->getMessage() . '</div>';
    $servicios = [];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 justify-content-center">
            <img src="/assets/img/ford.jpg" alt="Ford" style="height:100px;width:auto;border-radius:10px;box-shadow:0 1px 4px #0002;">
            <h1 class="fw-light mb-0 text-center" style="font-size:2rem; width:100%;">Gestión de Servicios</h1>
        </div>
        <a href="crear.php" class="btn btn-success shadow-none ms-md-4 mt-3 mt-md-0" style="border-radius: 20px; background: #2ecc40; border: none; white-space:nowrap;">
            <i class="fas fa-plus me-2"></i>Agregar
        </a>
    </div>
    <input type="text" id="busquedaServicios" class="form-control mb-3 shadow-none" placeholder="Buscar en servicios..." style="border-radius: 20px; border: 1px solid #e0e0e0;">
    <div class="card border-0 shadow-sm" style="background: #fafbfc;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaServicios" style="background: transparent;">
                    <thead style="background: #f4f6f8;">
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <th class="fw-normal small">ID</th>
                            <th class="fw-normal small">Vehículo</th>
                            <th class="fw-normal small">Cliente</th>
                            <th class="fw-normal small">Tipo</th>
                            <th class="fw-normal small">Descripción</th>
                            <th class="fw-normal small">Recepción</th>
                            <th class="fw-normal small">Estimada</th>
                            <th class="fw-normal small">Entrega</th>
                            <th class="fw-normal small">Estimado</th>
                            <th class="fw-normal small">Final</th>
                            <th class="fw-normal small">Estado</th>
                            <?php if ($rol === 'Administrador'): ?>
                            <th class="fw-normal small">Creado por</th>
                            <?php endif; ?>
                            <th class="fw-normal small text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyServicios">
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
const limit = 50;
let cargando = false;
let fin = false;
const tablaServicios = document.getElementById('tbodyServicios');
const inputServicios = document.getElementById('busquedaServicios');

function formatearServicio(servicio) {
    // Modal trigger for client name
    return `<tr style="border-bottom: 1px solid #f0f0f0;">
        <td class="text-muted">${servicio.servicio_id}</td>
        <td class="text-truncate" style="max-width:120px;">
            <span title="${servicio.marca} ${servicio.modelo}">${servicio.marca} ${servicio.modelo}</span>
        </td>
        <td style="max-width:180px;">
            <a href="#" class="cliente-link" data-servicio='${JSON.stringify(servicio).replace(/'/g, "&apos;")}' style="text-decoration:underline; color:#0d6efd;" title="Ver información">
                <span style="white-space:normal; word-break:break-word;">${servicio.cliente_nombre} ${servicio.cliente_apellido}</span>
            </a>
        </td>
        <td>${servicio.tipo_servicio || ''}</td>
        <td class="text-truncate" style="max-width:180px;">${(servicio.descripcion_problema || '').substring(0, 50)}${(servicio.descripcion_problema && servicio.descripcion_problema.length > 50 ? '...' : '')}</td>
        <td class="text-muted">${servicio.fecha_recepcion ? new Date(servicio.fecha_recepcion).toLocaleString('es-MX') : '-'}</td>
        <td class="text-muted">${servicio.fecha_estimada_entrega ? new Date(servicio.fecha_estimada_entrega).toLocaleDateString('es-MX') : '-'}</td>
        <td class="text-muted">${servicio.fecha_real_entrega ? new Date(servicio.fecha_real_entrega).toLocaleDateString('es-MX') : '-'}</td>
        <td class="text-success">${servicio.costo_estimado ? '$' + Number(servicio.costo_estimado).toFixed(2) : '-'}</td>
        <td class="text-success">${servicio.costo_final ? '$' + Number(servicio.costo_final).toFixed(2) : '-'}</td>
        <td>
            <span class="badge rounded-pill" style="background:#e0e0e0; color:#333; font-weight:400;">
                ${servicio.estado_servicio || ''}
            </span>
        </td>
        <?php if ($rol === 'Administrador'): ?>
        <td class="text-muted" style="font-size:0.9rem;">${servicio.creado_por_nombre || '-'}</td>
        <?php endif; ?>
        <td class="text-center" style="white-space: nowrap;">
            <a href="editar.php?id=${servicio.servicio_id}" class="btn btn-light btn-sm shadow-none me-2" style="border-radius: 16px; border: 1px solid #e0e0e0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#0d6efd" class="bi bi-pencil" viewBox="0 0 16 16">
                  <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zm.708-.708a1.5 1.5 0 0 0-2.121 0l-10 10a1.5 1.5 0 0 0-.328.497l-2 5a1.5 1.5 0 0 0 1.95 1.95l5-2a1.5 1.5 0 0 0 .497-.328l10-10a1.5 1.5 0 0 0 0-2.121l-2.292-2.292z"/>
                </svg>
            </a>
            <a href="index.php?eliminar=${servicio.servicio_id}" class="btn btn-light btn-sm shadow-none" style="border-radius: 16px; border: 1px solid #e0e0e0;" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dc3545" class="bi bi-trash" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6zm2.5-.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5z"/>
                  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2h3.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1H14.5a1 1 0 0 1 1 1zm-11-1a.5.5 0 0 0-.5.5V4h11V2.5a.5.5 0 0 0-.5-.5h-10z"/>
                </svg>
            </a>
        </td>
    </tr>`;
}

function mostrarError(msg) {
    let errorDiv = document.getElementById('errorServicios');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'errorServicios';
        errorDiv.className = 'alert alert-danger border-0 rounded-3 mt-3 text-center';
        errorDiv.style.background = '#ffeaea';
        tablaServicios.parentElement.parentElement.insertBefore(errorDiv, tablaServicios.parentElement);
    }
    errorDiv.textContent = msg;
}

function cargarServicios() {
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
            data.forEach(servicio => {
                tablaServicios.insertAdjacentHTML('beforeend', formatearServicio(servicio));
            });
            offset += data.length;
            cargando = false;
        })
        .catch(err => {
            mostrarError('No se pudieron cargar los servicios: ' + err.message);
            cargando = false;
        });
}

// Limpiar la tabla al cargar la página
tablaServicios.innerHTML = '';
cargarServicios();

// Scroll infinito
window.addEventListener('scroll', function() {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
        cargarServicios();
    }
});

// Filtro de búsqueda en el frontend
inputServicios.addEventListener('keyup', function() {
    let filtro = inputServicios.value.toLowerCase();
    let filas = tablaServicios.getElementsByTagName('tr');
    for (let i = 0; i < filas.length; i++) {
        let textoFila = filas[i].textContent.toLowerCase();
        filas[i].style.display = textoFila.includes(filtro) ? '' : 'none';
    }
});
</script>
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; background: #f9fafb;">
      <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; background: #f4f6f8; border-top-left-radius: 18px; border-top-right-radius: 18px;">
        <h5 class="modal-title fw-semibold" id="modalClienteLabel" style="color: #222;">Información del Servicio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalClienteBody" style="padding: 2rem 1.5rem;">
        <!-- Aquí se carga la info -->
      </div>
    </div>
  </div>
</div>
<script>
// Modal handler
document.addEventListener('click', function(e) {
    // Soporta clicks en el <a> o en el <span> dentro del <a>
    let target = e.target;
    if (target.classList.contains('cliente-link') || target.closest('.cliente-link')) {
        e.preventDefault();
        let link = target.classList.contains('cliente-link') ? target : target.closest('.cliente-link');
        let servicio = link.getAttribute('data-servicio');
        if (servicio) {
            try {
                servicio = JSON.parse(servicio.replace(/&apos;/g, "'"));
                let html = `
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <div style="background:#e0e7ef; border-radius:50%; width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#0d6efd" class="bi bi-person" viewBox="0 0 16 16">
                              <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm4-3a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/>
                              <path d="M14 14s-1-1.5-6-1.5S2 14 2 14v1h12v-1z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.1rem;">${servicio.cliente_nombre} ${servicio.cliente_apellido}</div>
                            <div class="text-muted" style="font-size:0.95rem;">${servicio.marca} ${servicio.modelo} ${servicio.placas ? ' - ' + servicio.placas : ''}</div>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="small text-muted">Tipo de Servicio</div>
                            <div class="fw-semibold">${servicio.tipo_servicio || '-'}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Estado</div>
                            <span class="badge rounded-pill px-3 py-2" style="background:#e0e0e0; color:#333; font-weight:500;">
                                ${servicio.estado_servicio || '-'}
                            </span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Descripción</div>
                        <div class="fw-normal" style="font-size:0.98rem;">${servicio.descripcion_problema || '-'}</div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="small text-muted">Recepción</div>
                            <div>${servicio.fecha_recepcion ? new Date(servicio.fecha_recepcion).toLocaleString('es-MX') : '-'}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Estimada Entrega</div>
                            <div>${servicio.fecha_estimada_entrega ? new Date(servicio.fecha_estimada_entrega).toLocaleDateString('es-MX') : '-'}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Real Entrega</div>
                            <div>${servicio.fecha_real_entrega ? new Date(servicio.fecha_real_entrega).toLocaleDateString('es-MX') : '-'}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Notas</div>
                            <div>${servicio.notas || '-'}</div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="small text-muted">Costo Estimado</div>
                            <div class="fw-semibold text-success">${servicio.costo_estimado ? '$' + Number(servicio.costo_estimado).toFixed(2) : '-'}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Costo Final</div>
                            <div class="fw-semibold text-success">${servicio.costo_final ? '$' + Number(servicio.costo_final).toFixed(2) : '-'}</div>
                        </div>
                    </div>
                `;
                document.getElementById('modalClienteBody').innerHTML = html;
                // Bootstrap 5 modal
                var modalEl = document.getElementById('modalCliente');
                if (window.bootstrap && bootstrap.Modal) {
                    let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                } else if (typeof $ !== 'undefined' && $(modalEl).modal) {
                    $(modalEl).modal('show');
                } else {
                    alert('No se pudo abrir el modal. Asegúrate de tener Bootstrap 5 JS cargado.');
                }
            } catch (err) {
                alert('No se pudo mostrar la información.');
            }
        }
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
#modalCliente .modal-content {
    border-radius: 18px;
    background: #f9fafb;
    border: none;
}
#modalCliente .modal-header {
    border-bottom: 1px solid #e0e0e0;
    background: #f4f6f8;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
}
#modalCliente .modal-title {
    color: #222;
    font-weight: 600;
}
#modalCliente .modal-body {
    padding: 2rem 1.5rem;
}
#modalCliente .badge {
    font-size: 0.95rem;
}
</style>
<?php require_once __DIR__ . '/../footer.php'; // Incluye el pie de página ?>