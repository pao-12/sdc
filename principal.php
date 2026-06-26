<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}
include("vista_usuarios/conexion.php");

$idEmpleado = $_SESSION['id_usuario'];
$nombreUsuario = $_SESSION['nombre'];
$emailUsuario  = $_SESSION['email'];

// ── Stats ──────────────────────────────────────────────────────────────
$sqlStats = "SELECT 
    COALESCE(SUM(g.Monto), 0)                                     AS TotalGastado,
    SUM(CASE WHEN v.Estado = 'pendiente'  THEN 1 ELSE 0 END)      AS Pendientes,
    SUM(CASE WHEN v.Estado = 'aprobado'   THEN 1 ELSE 0 END)      AS Aprobados,
    SUM(CASE WHEN v.Estado = 'rechazado'  THEN 1 ELSE 0 END)      AS Rechazados
FROM viatico v
LEFT JOIN gasto g ON v.IDViatico = g.IDViatico
WHERE v.IDEmpleado = '$idEmpleado'";
$resStats = mysqli_query($conect, $sqlStats);
$stats = mysqli_fetch_assoc($resStats);

// ── Lista de viáticos ──────────────────────────────────────────────────
$sqlViaticos = "SELECT v.*, COALESCE(SUM(g.Monto), 0) AS Total
    FROM viatico v
    LEFT JOIN gasto g ON v.IDViatico = g.IDViatico
    WHERE v.IDEmpleado = '$idEmpleado'
    GROUP BY v.IDViatico
    ORDER BY v.FechaRegistro DESC";
$resViaticos = mysqli_query($conect, $sqlViaticos);

// Mensaje flash
$msg = '';
if (isset($_GET['ok'])) {
    if ($_GET['ok'] == 'insert') $msg = '✓ Viático registrado correctamente.';
    if ($_GET['ok'] == 'update') $msg = '✓ Viático actualizado correctamente.';
    if ($_GET['ok'] == 'delete') $msg = '✓ Viático eliminado correctamente.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Viáticos — ViáticosApp</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; display: flex; height: 100vh; width: 100vw; overflow: hidden; }
.app { display: flex; width: 100%; height: 100%; }

/* ── SIDEBAR ── */
.sidebar { width: 270px; min-width: 270px; background: #1a2232; display: flex; flex-direction: column; color: #c8d0e0; transition: all .3s ease; }
.sidebar-header { display: flex; align-items: center; gap: 10px; padding: 18px 16px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.logo-icon { width: 36px; height: 36px; background: #1db87a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0; }
.sidebar-brand { font-size: 14px; font-weight: 600; color: #fff; line-height: 1.2; }
.sidebar-brand span { font-size: 13px; color: #8a95a8; font-weight: 400; display: block; }
.sidebar-close { margin-left: auto; color: #8a95a8; font-size: 20px; cursor: pointer; }
.nav { padding: 12px 8px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; font-size: 15px; color: #8a95a8; cursor: pointer; transition: background 0.15s; margin-bottom: 2px; text-decoration: none; }
.nav-item:hover { background: rgba(255,255,255,0.06); color: #c8d0e0; }
.nav-item.active { background: #1db87a; color: #fff; }
.nav-item i { font-size: 20px; }
.sidebar-footer { padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.08); }
.user-card { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.user-avatar { width: 36px; height: 36px; border-radius: 50%; background: #1db87a22; border: 2px solid #1db87a55; display: flex; align-items: center; justify-content: center; color: #1db87a; font-size: 16px; font-weight: 700; flex-shrink: 0; }
.user-info { flex: 1; min-width: 0; }
.user-name { font-size: 13px; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-email { font-size: 11px; color: #8a95a8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.logout-btn { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8a95a8; cursor: pointer; padding: 6px 0; background: none; border: none; width: 100%; transition: color 0.15s; }
.logout-btn:hover { color: #e53e3e; }
.logout-btn i { font-size: 16px; }

/* Collapsed sidebar */
.sidebar.collapsed { width: 68px; min-width: 68px; }
.sidebar.collapsed .sidebar-brand, .sidebar.collapsed .nav-item span,
.sidebar.collapsed .user-info, .sidebar.collapsed .logout-btn span { display: none; }
.sidebar.collapsed .sidebar-header { justify-content: center; }
.sidebar.collapsed .nav-item { justify-content: center; padding: 12px 0; }
.sidebar.collapsed .sidebar-footer { align-items: center; }
.sidebar.collapsed .logout-btn { justify-content: center; }
.sidebar.collapsed .user-card { justify-content: center; }

/* ── MAIN ── */
.main { flex: 1; background: #f0f2f5; display: flex; flex-direction: column; overflow: hidden; }
.main-inner { width: 100%; max-width: 1400px; margin: 0 auto; padding: 28px 32px; overflow-y: auto; height: 100%; }
.main-inner::-webkit-scrollbar { width: 6px; }
.main-inner::-webkit-scrollbar-thumb { background: #dde1e7; border-radius: 3px; }

.page-header { margin-bottom: 22px; }
.main-title { font-size: 26px; font-weight: 700; color: #1a2232; }
.main-subtitle { font-size: 14px; color: #8a95a8; margin-top: 3px; }

/* ── STATS ── */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.stat-card { background: #fff; border-radius: 12px; border: 1px solid #e5e8ed; padding: 16px 18px; display: flex; align-items: center; justify-content: space-between; }
.stat-label { font-size: 12px; color: #8a95a8; margin-bottom: 6px; font-weight: 500; }
.stat-value { font-size: 22px; font-weight: 700; color: #1a2232; }
.stat-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.stat-icon.gray  { background: #f0f2f5; color: #8a95a8; }
.stat-icon.amber { background: #fef3c7; color: #d97706; }
.stat-icon.green { background: #d1fae5; color: #059669; }
.stat-icon.red   { background: #fee2e2; color: #dc2626; }

/* ── TOOLBAR ── */
.toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.search-box { flex: 1; min-width: 200px; background: #fff; border: 1px solid #e5e8ed; border-radius: 8px; padding: 9px 14px; display: flex; align-items: center; gap: 8px; color: #b0b8c8; font-size: 13px; }
.search-box input { border: none; outline: none; font-size: 13px; width: 100%; color: #1a2232; background: transparent; }
.search-box input::placeholder { color: #b0b8c8; }
.filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.pill { padding: 7px 14px; border-radius: 20px; font-size: 12px; border: 1px solid #dde1e7; color: #8a95a8; cursor: pointer; background: #fff; transition: all 0.15s; }
.pill:hover { background: #f0f2f5; }
.pill.active { background: #1a2232; color: #fff; border-color: #1a2232; }
.btn-new { display: flex; align-items: center; gap: 6px; background: #1db87a; color: #fff; border: none; border-radius: 8px; padding: 9px 16px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; text-decoration: none; transition: background 0.15s; }
.btn-new:hover { background: #17a06a; }

/* ── TABLE ── */
.table-wrap { background: #fff; border: 1px solid #e5e8ed; border-radius: 12px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
thead th { background: #f8fafb; padding: 13px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #8a95a8; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e8ed; }
tbody td { padding: 14px 16px; color: #4a5568; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #fdfefe; }

.badge-total { background: #d1fae5; color: #065f46; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 13px; display: inline-block; }

/* Status badges */
.badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-pendiente  { background: #fef3c7; color: #d97706; }
.badge-aprobado   { background: #d1fae5; color: #059669; }
.badge-rechazado  { background: #fee2e2; color: #dc2626; }

/* Action buttons */
.actions-cell { display: flex; gap: 6px; }
.btn-action { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 7px; border: 1px solid #e5e8ed; background: #fff; color: #8a95a8; cursor: pointer; text-decoration: none; transition: all 0.15s; font-size: 16px; }
.btn-action.view:hover   { color: #2563eb; border-color: #2563eb; background: #eff6ff; }
.btn-action.edit:hover   { color: #1db87a; border-color: #1db87a; background: #d1fae5; }
.btn-action.delete:hover { color: #dc2626; border-color: #dc2626; background: #fee2e2; }

.empty-state { text-align: center; padding: 60px 0; color: #b0b8c8; }
.empty-state i { font-size: 40px; display: block; margin-bottom: 10px; opacity: 0.4; }
.empty-state p { font-size: 14px; }

/* Flash message */
.flash { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 16px; margin-bottom: 16px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; }

/* ── MODAL ELIMINAR ── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 14px; padding: 28px 28px 24px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.18); }
.modal-icon { width: 52px; height: 52px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; color: #dc2626; }
.modal h3 { font-size: 18px; font-weight: 700; color: #1a2232; text-align: center; margin-bottom: 8px; }
.modal p { font-size: 13px; color: #6b7280; text-align: center; line-height: 1.6; margin-bottom: 22px; }
.modal-actions { display: flex; gap: 10px; }
.btn-modal-cancel { flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #e5e8ed; background: #fff; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.btn-modal-cancel:hover { background: #f0f2f5; }
.btn-modal-confirm { flex: 1; padding: 10px; border-radius: 8px; border: none; background: #dc2626; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.btn-modal-confirm:hover { background: #b91c1c; }
</style>
</head>
<body>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo-icon"><i class="ti ti-circle-dollar-sign"></i></div>
      <div class="sidebar-brand">SYSTEM VIATIC<span>Sistema de gastos</span></div>
      <i class="ti ti-chevrons-left sidebar-close" id="sidebarToggle"></i>
    </div>
    <nav class="nav">
      <a href="principal.php" class="nav-item active"><i class="ti ti-briefcase"></i><span>Mis Viáticos</span></a>
      <a href="vista_usuarios/formulario.php" class="nav-item"><i class="ti ti-circle-plus"></i><span>Nuevo Viático</span></a>
      <a href="vista_usuarios/perfil.php" class="nav-item"><i class="ti ti-user"></i><span>Mi Perfil</span></a>
    </nav>
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar"><?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?></div>
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
          <div class="user-email"><?php echo htmlspecialchars($emailUsuario); ?></div>
        </div>
      </div>
      <button class="logout-btn" onclick="window.location.href='verificardatos/terminarseccion.php'">
        <i class="ti ti-logout"></i><span>Cerrar Sesión</span>
      </button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <div class="main-inner">
      <div class="page-header">
        <h1 class="main-title">Mis Viáticos</h1>
        <p class="main-subtitle">Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?> · <?php echo htmlspecialchars($emailUsuario); ?></p>
      </div>

      <?php if ($msg): ?>
      <div class="flash"><i class="ti ti-circle-check"></i><?php echo $msg; ?></div>
      <?php endif; ?>

      <!-- STATS -->
      <div class="stats-row">
        <div class="stat-card">
          <div>
            <div class="stat-label">Total Gastado</div>
            <div class="stat-value">$<?php echo number_format($stats['TotalGastado'], 2); ?></div>
          </div>
          <div class="stat-icon gray"><i class="ti ti-circle-dollar-sign"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Pendientes</div>
            <div class="stat-value"><?php echo (int)$stats['Pendientes']; ?></div>
          </div>
          <div class="stat-icon amber"><i class="ti ti-clock"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Aprobados</div>
            <div class="stat-value"><?php echo (int)$stats['Aprobados']; ?></div>
          </div>
          <div class="stat-icon green"><i class="ti ti-checks"></i></div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Rechazados</div>
            <div class="stat-value"><?php echo (int)$stats['Rechazados']; ?></div>
          </div>
          <div class="stat-icon red"><i class="ti ti-circle-x"></i></div>
        </div>
      </div>

      <!-- TOOLBAR -->
      <div class="toolbar">
        <div class="search-box">
          <i class="ti ti-search"></i>
          <input type="text" id="searchInput" placeholder="Buscar por nombre de salida...">
        </div>
        <div class="filter-pills">
          <button class="pill active" data-filter="todos">Todos</button>
          <button class="pill" data-filter="pendiente">Pendiente</button>
          <button class="pill" data-filter="aprobado">Aprobado</button>
          <button class="pill" data-filter="rechazado">Rechazado</button>
        </div>
        <a href="vista_usuarios/formulario.php" class="btn-new"><i class="ti ti-plus"></i> Nuevo Viático</a>
      </div>

      <!-- TABLE -->
      <div class="table-wrap">
        <table id="viaticoTable">
          <thead>
            <tr>
              <th>Nombre de la Salida</th>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($resViaticos) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($resViaticos)): ?>
              <?php 
                $estado = strtolower($row['Estado'] ?? 'pendiente');
                $badgeClass = 'badge-' . $estado;
                $estadoLabel = ucfirst($estado);
              ?>
              <tr data-estado="<?php echo $estado; ?>">
                <td><strong><?php echo htmlspecialchars($row['NombreSalida']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['Fecha']); ?></td>
                <td><?php echo htmlspecialchars($row['Hora']); ?></td>
                <td><span class="badge-total">$<?php echo number_format($row['Total'], 2); ?></span></td>
                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $estadoLabel; ?></span></td>
                <td class="actions-cell">
                  <a href="vista_usuarios/mostrar.php?id=<?php echo $row['IDViatico']; ?>" class="btn-action view" title="Ver detalles"><i class="ti ti-eye"></i></a>
                  <a href="vista_usuarios/actualizar.php?id=<?php echo $row['IDViatico']; ?>" class="btn-action edit" title="Editar"><i class="ti ti-pencil"></i></a>
                  <button onclick="abrirModalEliminar(<?php echo $row['IDViatico']; ?>, '<?php echo htmlspecialchars(addslashes($row['NombreSalida'])); ?>')" class="btn-action delete" title="Eliminar"><i class="ti ti-trash"></i></button>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <i class="ti ti-file-off"></i>
                    <p>Sin registros aún. Crea tu primer viático.</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal-overlay" id="modalEliminar">
  <div class="modal">
    <div class="modal-icon"><i class="ti ti-trash"></i></div>
    <h3>¿Eliminar viático?</h3>
    <p id="modalMsg">Esta acción eliminará el viático y todos sus gastos asociados permanentemente. No se puede deshacer.</p>
    <div class="modal-actions">
      <button class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
      <a href="#" id="btnConfirmDelete" class="btn-modal-confirm">Sí, eliminar</a>
    </div>
  </div>
</div>

<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
toggleBtn.addEventListener('click', () => {
  sidebar.classList.toggle('collapsed');
  toggleBtn.classList.toggle('ti-chevrons-left');
  toggleBtn.classList.toggle('ti-chevrons-right');
});

// Filtros por estado
document.querySelectorAll('.pill').forEach(pill => {
  pill.addEventListener('click', () => {
    document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    const filter = pill.dataset.filter;
    document.querySelectorAll('#viaticoTable tbody tr').forEach(row => {
      if (!row.dataset.estado) return;
      row.style.display = (filter === 'todos' || row.dataset.estado === filter) ? '' : 'none';
    });
  });
});

// Búsqueda
document.getElementById('searchInput').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#viaticoTable tbody tr[data-estado]').forEach(row => {
    const nombre = row.cells[0].textContent.toLowerCase();
    row.style.display = nombre.includes(q) ? '' : 'none';
  });
});

// Modal eliminar
function abrirModalEliminar(id, nombre) {
  document.getElementById('modalMsg').textContent =
    `¿Seguro que deseas eliminar "${nombre}" y todos sus gastos? Esta acción no se puede deshacer.`;
  document.getElementById('btnConfirmDelete').href = '/SDC2/vista_usuarios/eliminar.php?id=' + id;
  document.getElementById('modalEliminar').classList.add('open');
}
function cerrarModal() {
  document.getElementById('modalEliminar').classList.remove('open');
}
document.getElementById('modalEliminar').addEventListener('click', function(e) {
  if (e.target === this) cerrarModal();
});
</script>
</body>
</html>
