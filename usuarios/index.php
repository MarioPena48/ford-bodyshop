<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /login.php');
    exit;
}
// --- AJAX para scroll infinito ---
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    require_once __DIR__ . '/../config.php';
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 40;
    try {
        $stmt = $pdo->prepare('SELECT usuario_id, nombre_usuario, rol, nombre, apellido, email FROM usuarios ORDER BY usuario_id DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($usuarios);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../header.php';
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 justify-content-center">
            <img src="/assets/img/ford.jpg" alt="Ford" style="height:100px;width:auto;border-radius:10px;box-shadow:0 1px 4px #0002;">
            <h1 class="fw-light mb-0 text-center" style="font-size:2rem; width:100%;">Gestión de Usuarios</h1>
        </div>
        <a href="crear.php" class="btn btn-success shadow-none ms-md-4 mt-3 mt-md-0" style="border-radius: 20px; background: #2ecc40; border: none; white-space:nowrap;">
            <i class="fas fa-plus me-2"></i>Agregar
        </a>
    </div>
    <input type="text" id="busquedaUsuarios" class="form-control mb-3 shadow-none" placeholder="Buscar en usuarios..." style="border-radius: 20px; border: 1px solid #e0e0e0;">
    <div class="card border-0 shadow-sm" style="background: #fafbfc;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaUsuarios" style="background: transparent;">
                    <thead style="background: #f4f6f8;">
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <th class="fw-normal small">ID</th>
                            <th class="fw-normal small">Usuario</th>
                            <th class="fw-normal small">Nombre</th>
                            <th class="fw-normal small">Rol</th>
                            <th class="fw-normal small">Email</th>
                            <th class="fw-normal small text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyUsuarios">
                        <!-- Las filas se cargarán por AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
let offset = 0;
const limit = 40;
let cargando = false;
let fin = false;
const tablaUsuarios = document.getElementById('tbodyUsuarios');
const inputUsuarios = document.getElementById('busquedaUsuarios');

function formatearUsuario(usuario) {
    return `<tr style="border-bottom: 1px solid #f0f0f0;">
        <td class="text-muted">${usuario.usuario_id}</td>
        <td>${usuario.nombre_usuario}</td>
        <td>${(usuario.nombre || '') + ' ' + (usuario.apellido || '')}</td>
        <td><span class="badge rounded-pill px-3 py-2" style="background:#e0e0e0; color:#333; font-weight:500;">${usuario.rol}</span></td>
        <td>${usuario.email || ''}</td>
        <td class="text-center" style="white-space: nowrap;">
            <a href="editar.php?id=${usuario.usuario_id}" class="btn btn-light btn-sm shadow-none me-2" style="border-radius: 16px; border: 1px solid #e0e0e0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#0d6efd" class="bi bi-pencil" viewBox="0 0 16 16">
                  <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zm.708-.708a1.5 1.5 0 0 0-2.121 0l-10 10a1.5 1.5 0 0 0-.328.497l-2 5a1.5 1.5 0 0 0 1.95 1.95l5-2a1.5 1.5 0 0 0 .497-.328l10-10a1.5 1.5 0 0 0 0-2.121l-2.292-2.292z"/>
                </svg>
            </a>
            <a href="eliminar.php?id=${usuario.usuario_id}" class="btn btn-light btn-sm shadow-none" style="border-radius: 16px; border: 1px solid #e0e0e0;" onclick="return confirm('¿Seguro de eliminar este usuario?')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dc3545" class="bi bi-trash" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6zm2.5-.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5z"/>
                  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2h3.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1H14.5a1 1 0 0 1 1 1zm-11-1a.5.5 0 0 0-.5.5V4h11V2.5a.5.5 0 0 0-.5-.5h-10z"/>
                </svg>
            </a>
        </td>
    </tr>`;
}

function mostrarError(msg) {
    let errorDiv = document.getElementById('errorUsuarios');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'errorUsuarios';
        errorDiv.className = 'alert alert-danger border-0 rounded-3 mt-3 text-center';
        errorDiv.style.background = '#ffeaea';
        tablaUsuarios.parentElement.parentElement.insertBefore(errorDiv, tablaUsuarios.parentElement);
    }
    errorDiv.textContent = msg;
}

function cargarUsuarios() {
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
            data.forEach(usuario => {
                tablaUsuarios.insertAdjacentHTML('beforeend', formatearUsuario(usuario));
            });
            offset += data.length;
            cargando = false;
        })
        .catch(err => {
            mostrarError('No se pudieron cargar los usuarios: ' + err.message);
            cargando = false;
        });
}

tablaUsuarios.innerHTML = '';
cargarUsuarios();

window.addEventListener('scroll', function() {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
        cargarUsuarios();
    }
});

inputUsuarios.addEventListener('keyup', function() {
    let filtro = inputUsuarios.value.toLowerCase();
    let filas = tablaUsuarios.getElementsByTagName('tr');
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
<?php require_once __DIR__ . '/../footer.php'; ?>
