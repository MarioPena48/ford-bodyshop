<?php
// --- Endpoints AJAX para FullCalendar ---
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    require_once __DIR__ . '/../config.php';
    header('Content-Type: application/json');
    // Obtener eventos
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query('SELECT c.cita_id, c.fecha_hora_cita, c.notas, cl.nombre, cl.apellido FROM citas c LEFT JOIN clientes cl ON c.cliente_id = cl.cliente_id');
        $eventos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $cita) {
            $eventos[] = [
                'id' => $cita['cita_id'],
                'title' => ($cita['nombre'] ? $cita['nombre'] . ' ' . $cita['apellido'] : 'Sin cliente'),
                'start' => $cita['fecha_hora_cita'],
                'extendedProps' => [
                    'notas' => $cita['notas'],
                    'cliente_nombre' => ($cita['nombre'] ? $cita['nombre'] . ' ' . $cita['apellido'] : 'Sin cliente')
                ]
            ];
        }
        echo json_encode($eventos);
        exit;
    }
    // Crear, editar, eliminar eventos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        require_once __DIR__ . '/../config.php';
        if (isset($data['action'])) {
            if ($data['action'] === 'create') {
                $stmt = $pdo->prepare('INSERT INTO citas (cliente_id, fecha_hora_cita, notas) VALUES (?, ?, ?)');
                $stmt->execute([
                    $data['cliente_id'],
                    $data['fecha_hora_cita'],
                    $data['notas']
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                exit;
            } elseif ($data['action'] === 'update') {
                $stmt = $pdo->prepare('UPDATE citas SET cliente_id=?, fecha_hora_cita=?, notas=? WHERE cita_id=?');
                $stmt->execute([
                    $data['cliente_id'],
                    $data['fecha_hora_cita'],
                    $data['notas'],
                    $data['id']
                ]);
                echo json_encode(['success' => true]);
                exit;
            } elseif ($data['action'] === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM citas WHERE cita_id=?');
                $stmt->execute([$data['id']]);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false]);
        exit;
    }
    exit;
}
if (!isset($_GET['ajax'])) {
    require_once __DIR__ . '/../header.php';
    require_once __DIR__ . '/../config.php';
}
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 justify-content-center">
            <img src="/assets/img/ford.jpg" alt="Ford" style="height:100px;width:auto;border-radius:10px;box-shadow:0 1px 4px #0002;">
            <h1 class="fw-light mb-0 text-center" style="font-size:2rem; width:100%;">Calendario de Citas</h1>
        </div>
        <button id="btnNuevaCita" class="btn btn-success shadow-none ms-md-4 mt-3 mt-md-0" style="border-radius: 20px; background: #2ecc40; border: none; white-space:nowrap;"><i class="fas fa-plus me-2"></i>Nueva Cita</button>
    </div>
    <div class="card border-0 shadow-sm" style="background: #fafbfc;">
        <div class="card-body p-0">
            <div id='calendarioCitas'></div>
        </div>
    </div>
</div>
<!-- Modal para crear/editar cita -->
<div class="modal fade" id="modalCita" tabindex="-1" aria-labelledby="modalCitaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; background: #f9fafb;">
      <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; background: #f4f6f8; border-top-left-radius: 18px; border-top-right-radius: 18px;">
        <h5 class="modal-title fw-semibold" id="modalCitaLabel" style="color: #222;">Cita</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" style="padding: 2rem 1.5rem;">
        <form id="formCita">
          <input type="hidden" id="citaId">
          <div class="mb-3">
            <label for="clienteInput" class="form-label">Cliente</label>
            <input type="text" class="form-control" id="clienteInput" autocomplete="off" required>
            <div id="clienteList" class="list-group position-absolute w-100" style="z-index:10;"></div>
          </div>
          <div class="mb-3">
            <label for="fechaHoraInput" class="form-label">Fecha y Hora</label>
            <input type="datetime-local" class="form-control" id="fechaHoraInput" required>
          </div>
          <div class="mb-3">
            <label for="notasInput" class="form-label">Notas</label>
            <textarea class="form-control" id="notasInput"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger d-none" id="btnEliminarCita">Eliminar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarCita">Guardar</button>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
let clientes = [];
let clienteSeleccionado = null;

function mostrarMensajeModal(msg, tipo = 'danger') {
    let alerta = document.getElementById('alertaModalCita');
    if (!alerta) {
        alerta = document.createElement('div');
        alerta.id = 'alertaModalCita';
        alerta.className = 'alert mt-2';
        document.querySelector('#modalCita .modal-body').prepend(alerta);
    }
    alerta.className = 'alert alert-' + tipo + ' mt-2';
    alerta.textContent = msg;
}

function cargarClientes() {
    fetch('clientes_lista.php', {headers: {'Accept': 'application/json'}})
        .then(r => r.text())
        .then(txt => {
            try {
                clientes = JSON.parse(txt);
                if (clientes.error) mostrarMensajeModal('Error: ' + clientes.error);
            } catch (e) {
                mostrarMensajeModal('Respuesta inesperada del servidor:<br><pre style="white-space:pre-wrap">' + txt + '</pre>');
            }
        })
        .catch(e => { mostrarMensajeModal('Error cargando clientes: ' + e); });
}

function autocompletarCliente() {
    const input = document.getElementById('clienteInput');
    const list = document.getElementById('clienteList');
    input.addEventListener('input', function() {
        const val = input.value.toLowerCase();
        list.innerHTML = '';
        if (val.length < 2) return;
        const resultados = clientes.filter(c => (c.nombre + ' ' + c.apellido).toLowerCase().includes(val)).slice(0,5);
        if (resultados.length === 0) {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-danger';
            item.textContent = 'No encontrado';
            list.appendChild(item);
        }
        resultados.forEach(c => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';
            item.textContent = c.nombre + ' ' + c.apellido;
            item.onclick = () => {
                input.value = c.nombre + ' ' + c.apellido;
                clienteSeleccionado = c;
                list.innerHTML = '';
            };
            list.appendChild(item);
        });
    });
    input.addEventListener('blur', () => setTimeout(()=>{list.innerHTML='';},200));
}

function abrirModalCita({id, cliente_id, cliente_nombre, fecha_hora_cita, notas}, modo) {
    document.getElementById('citaId').value = id || '';
    document.getElementById('clienteInput').value = cliente_nombre || '';
    document.getElementById('fechaHoraInput').value = fecha_hora_cita ? fecha_hora_cita.replace(' ', 'T').slice(0,16) : '';
    document.getElementById('notasInput').value = notas || '';
    clienteSeleccionado = cliente_id ? {cliente_id, nombre: cliente_nombre} : null;
    document.getElementById('btnEliminarCita').classList.toggle('d-none', !id);
    mostrarMensajeModal('', 'info');
    const modal = new bootstrap.Modal(document.getElementById('modalCita'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    cargarClientes();
    autocompletarCliente();
    var calendarEl = document.getElementById('calendarioCitas');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'index.php?ajax=1',
        eventClick: function(info) {
            const [nombre, ...rest] = info.event.title.split(' - ');
            abrirModalCita({
                id: info.event.id,
                cliente_id: null,
                cliente_nombre: nombre,
                fecha_hora_cita: info.event.startStr,
                notas: info.event.extendedProps.notas
            }, 'edit');
        },
        dateClick: function(info) {
            abrirModalCita({fecha_hora_cita: info.dateStr + 'T00:00'}, 'create');
        }
    });
    calendar.render();
    document.getElementById('btnNuevaCita').onclick = function() {
        abrirModalCita({}, 'create');
    };
    document.getElementById('btnGuardarCita').onclick = function() {
        const id = document.getElementById('citaId').value;
        const cliente = clientes.find(c => (c.nombre + ' ' + c.apellido) === document.getElementById('clienteInput').value);
        const cliente_id = cliente ? cliente.cliente_id : '';
        const fecha_hora_cita = document.getElementById('fechaHoraInput').value;
        const notas = document.getElementById('notasInput').value;
        if (!cliente_id || !fecha_hora_cita) { mostrarMensajeModal('Completa todos los campos obligatorios.'); return; }
        fetch('agregar-cita.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: id ? 'update' : 'create',
                id,
                cliente_id,
                fecha_hora_cita,
                notas
            })
        }).then(r=>r.json()).then((resp) => {
            if (!resp.success) { mostrarMensajeModal('Error al guardar la cita.'); return; }
            bootstrap.Modal.getInstance(document.getElementById('modalCita')).hide(); calendar.refetchEvents();
        }).catch(e => mostrarMensajeModal('Error de red: ' + e));
    };
    document.getElementById('btnEliminarCita').onclick = function() {
        const id = document.getElementById('citaId').value;
        if (!id) return;
        if (confirm('¿Seguro de eliminar esta cita?')) {
            fetch('agregar-cita.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'delete', id})
            }).then(r=>r.json()).then((resp) => {
                if (!resp.success) { mostrarMensajeModal('Error al eliminar la cita.'); return; }
                bootstrap.Modal.getInstance(document.getElementById('modalCita')).hide(); calendar.refetchEvents();
            }).catch(e => mostrarMensajeModal('Error de red: ' + e));
        }
    };
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
#modalCita .modal-content {
    border-radius: 18px;
    background: #f9fafb;
    border: none;
}
#modalCita .modal-header {
    border-bottom: 1px solid #e0e0e0;
    background: #f4f6f8;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
}
#modalCita .modal-title {
    color: #222;
    font-weight: 600;
}
#modalCita .modal-body {
    padding: 2rem 1.5rem;
}
#modalCita .badge {
    font-size: 0.95rem;
}
</style>
<?php if (!isset($_GET['ajax'])) {
    require_once __DIR__ . '/../footer.php';
} ?>
