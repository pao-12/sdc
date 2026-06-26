<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php?error=acceso"); exit();
}
include("../vista_usuarios/conexion.php");

// Stats globales
$sTotal    = mysqli_fetch_assoc(mysqli_query($conect,"SELECT COUNT(*) AS n FROM viatico"));
$sPend     = mysqli_fetch_assoc(mysqli_query($conect,"SELECT COUNT(*) AS n FROM viatico WHERE Estado='pendiente'"));
$sApro     = mysqli_fetch_assoc(mysqli_query($conect,"SELECT COUNT(*) AS n FROM viatico WHERE Estado='aprobado'"));
$sEmp      = mysqli_fetch_assoc(mysqli_query($conect,"SELECT COUNT(*) AS n FROM empleado WHERE Activo=1"));
$sGasto    = mysqli_fetch_assoc(mysqli_query($conect,"SELECT COALESCE(SUM(Monto),0) AS n FROM gasto"));

// Viáticos recientes
$resRecientes = mysqli_query($conect,"
    SELECT v.IDViatico, v.NombreSalida, v.Fecha, v.Estado, e.Nombre AS Empleado,
           COALESCE(SUM(g.Monto),0) AS Total
    FROM viatico v
    JOIN empleado e ON v.IDEmpleado=e.IDEmpleado
    LEFT JOIN gasto g ON v.IDViatico=g.IDViatico
    GROUP BY v.IDViatico
    ORDER BY v.FechaRegistro DESC LIMIT 8");
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard Admin — ViáticosApp</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f2f5;display:flex;height:100vh;width:100vw;overflow:hidden;}
.app{display:flex;width:100%;height:100%;}

/* SIDEBAR */
.sidebar{width:260px;min-width:260px;background:#1a2232;display:flex;flex-direction:column;color:#c8d0e0;transition:width .25s ease,min-width .25s ease;}
.sidebar-header{display:flex;align-items:center;gap:10px;padding:18px 14px 16px;border-bottom:1px solid rgba(255,255,255,.08);}
.logo-icon{width:34px;height:34px;background:#7c3aed;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;flex-shrink:0;}
.sidebar-brand{font-size:13px;font-weight:600;color:#fff;line-height:1.2;flex:1;}
.sidebar-brand span{font-size:11px;color:#8a95a8;font-weight:400;display:block;}
.sidebar-toggle{margin-left:auto;color:#8a95a8;font-size:18px;cursor:pointer;flex-shrink:0;}
.nav{padding:10px 6px;flex:1;}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 11px;border-radius:8px;font-size:14px;color:#8a95a8;text-decoration:none;margin-bottom:2px;transition:background .15s;}
.nav-item:hover{background:rgba(255,255,255,.06);color:#c8d0e0;}
.nav-item.active{background:#7c3aed;color:#fff;}
.nav-item i{font-size:19px;flex-shrink:0;}
.sidebar-footer{padding:10px 14px;border-top:1px solid rgba(255,255,255,.08);}
.user-card{display:flex;align-items:center;gap:9px;margin-bottom:10px;}
.user-avatar{width:34px;height:34px;border-radius:50%;background:#7c3aed22;border:2px solid #7c3aed55;display:flex;align-items:center;justify-content:center;color:#a78bfa;font-size:14px;font-weight:700;flex-shrink:0;}
.user-info{flex:1;min-width:0;}
.user-name{font-size:12px;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.user-email{font-size:10px;color:#8a95a8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;}
.logout-btn{display:flex;align-items:center;gap:7px;font-size:12px;color:#8a95a8;cursor:pointer;padding:5px 0;background:none;border:none;width:100%;}
.logout-btn:hover{color:#ef4444;}

/* Collapsed */
.sidebar.collapsed{width:62px;min-width:62px;}
.sidebar.collapsed .sidebar-brand,.sidebar.collapsed .nav-item span,.sidebar.collapsed .user-info,.sidebar.collapsed .logout-btn span{display:none;}
.sidebar.collapsed .sidebar-header{justify-content:center;}
.sidebar.collapsed .nav-item{justify-content:center;}
.sidebar.collapsed .user-card{justify-content:center;}
.sidebar.collapsed .logout-btn{justify-content:center;}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
.main-inner{width:100%;max-width:1300px;margin:0 auto;padding:24px 28px;overflow-y:auto;height:100%;}
.main-inner::-webkit-scrollbar{width:5px;}
.main-inner::-webkit-scrollbar-thumb{background:#dde1e7;border-radius:3px;}

/* PAGE HEADER */
.page-header{margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.page-title{font-size:22px;font-weight:700;color:#1a2232;}
.page-sub{font-size:13px;color:#8a95a8;margin-top:3px;}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;}
.stat-card{background:#fff;border:1px solid #e5e8ed;border-radius:12px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;}
.stat-label{font-size:11px;color:#8a95a8;margin-bottom:5px;font-weight:500;}
.stat-value{font-size:20px;font-weight:700;color:#1a2232;}
.stat-icon{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.ico-purple{background:#ede9fe;color:#7c3aed;}
.ico-green{background:#d1fae5;color:#059669;}
.ico-amber{background:#fef3c7;color:#d97706;}
.ico-red{background:#fee2e2;color:#dc2626;}
.ico-blue{background:#dbeafe;color:#2563eb;}

/* CARD */
.card{background:#fff;border:1px solid #e5e8ed;border-radius:12px;overflow:hidden;margin-bottom:16px;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f0f2f5;}
.card-title{font-size:14px;font-weight:600;color:#1a2232;display:flex;align-items:center;gap:8px;}
.card-title i{color:#7c3aed;font-size:18px;}

/* TABLE */
table{width:100%;border-collapse:collapse;font-size:13px;}
thead th{background:#f8fafb;padding:11px 14px;text-align:left;font-size:11px;font-weight:600;color:#8a95a8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e5e8ed;}
tbody td{padding:12px 14px;color:#4a5568;border-bottom:1px solid #f0f2f5;vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover td{background:#fdfefe;}

/* BADGES */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-pendiente{background:#fef3c7;color:#d97706;}
.badge-aprobado{background:#d1fae5;color:#059669;}
.badge-rechazado{background:#fee2e2;color:#dc2626;}
.badge-admin{background:#ede9fe;color:#7c3aed;}
.badge-empleado{background:#f0f2f5;color:#6b7280;}
.badge-activo{background:#d1fae5;color:#059669;}
.badge-inactivo{background:#fee2e2;color:#dc2626;}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .15s;}
.btn-primary{background:#7c3aed;color:#fff;}
.btn-primary:hover{background:#6d28d9;}
.btn-success{background:#059669;color:#fff;}
.btn-success:hover{background:#047857;}
.btn-danger{background:#dc2626;color:#fff;}
.btn-danger:hover{background:#b91c1c;}
.btn-outline{background:#fff;color:#4a5568;border:1px solid #dde1e7;}
.btn-outline:hover{background:#f0f2f5;}
.btn-sm{padding:5px 10px;font-size:12px;}

/* ACTION ICONS */
.act-cell{display:flex;gap:5px;}
.btn-action{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;border:1px solid #e5e8ed;background:#fff;color:#8a95a8;cursor:pointer;text-decoration:none;font-size:15px;transition:all .15s;}
.btn-action.view:hover{color:#2563eb;border-color:#2563eb;background:#eff6ff;}
.btn-action.edit:hover{color:#7c3aed;border-color:#7c3aed;background:#ede9fe;}
.btn-action.del:hover{color:#dc2626;border-color:#dc2626;background:#fee2e2;}

/* FORMS */
.field{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.field-label{font-size:12px;font-weight:500;color:#374151;}
.input-wrap{display:flex;align-items:center;border:1px solid #dde1e7;border-radius:8px;padding:9px 12px;gap:8px;transition:border-color .15s;}
.input-wrap:focus-within{border-color:#7c3aed;}
.input-wrap i{color:#b0b8c8;font-size:16px;flex-shrink:0;}
.input-wrap input,.input-wrap select{border:none;outline:none;font-size:13px;width:100%;color:#1a2232;background:transparent;}
.input-wrap select option{background:#fff;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

/* FLASH */
.flash{display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;font-weight:500;}
.flash.ok{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;}
.flash.error{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}

/* TOOLBAR */
.toolbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.search-box{flex:1;min-width:180px;background:#fff;border:1px solid #e5e8ed;border-radius:8px;padding:8px 12px;display:flex;align-items:center;gap:7px;color:#b0b8c8;font-size:13px;}
.search-box input{border:none;outline:none;font-size:13px;width:100%;color:#1a2232;background:transparent;}
.pill{padding:6px 12px;border-radius:20px;font-size:12px;border:1px solid #dde1e7;color:#8a95a8;cursor:pointer;background:#fff;transition:all .15s;}
.pill.active,.pill:hover{background:#1a2232;color:#fff;border-color:#1a2232;}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:14px;padding:0;max-width:480px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:hidden;}
.modal-head{display:flex;align-items:center;gap:10px;padding:18px 22px;border-bottom:1px solid #f0f2f5;}
.modal-head i{font-size:20px;color:#7c3aed;}
.modal-head h3{font-size:16px;font-weight:700;color:#1a2232;}
.modal-close{margin-left:auto;background:none;border:none;font-size:20px;color:#8a95a8;cursor:pointer;}
.modal-body{padding:18px 22px;}
.modal-foot{padding:14px 22px;border-top:1px solid #f0f2f5;display:flex;justify-content:flex-end;gap:8px;}

/* CONFIRM MODAL */
.confirm-modal{background:#fff;border-radius:14px;padding:28px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.18);text-align:center;}
.confirm-icon{width:52px;height:52px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#dc2626;}
.confirm-modal h3{font-size:17px;font-weight:700;color:#1a2232;margin-bottom:8px;}
.confirm-modal p{font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:20px;}
.confirm-actions{display:flex;gap:10px;}

/* EMPTY */
.empty-state{text-align:center;padding:50px 0;color:#b0b8c8;}
.empty-state i{font-size:38px;display:block;margin-bottom:8px;opacity:.4;}
.empty-state p{font-size:13px;}

/* BACK LINK */
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#8a95a8;text-decoration:none;margin-bottom:14px;background:none;border:none;cursor:pointer;}
.back-link:hover{color:#7c3aed;}
</style>
</head><body>
<div class="app">
<?php include("sidebar.php"); ?>
<main class="main"><div class="main-inner">

  <div class="page-header">
    <div><h1 class="page-title">Dashboard</h1><p class="page-sub">Resumen general del sistema</p></div>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div><div class="stat-label">Total Viáticos</div><div class="stat-value"><?php echo $sTotal['n']; ?></div></div><div class="stat-icon ico-purple"><i class="ti ti-briefcase"></i></div></div>
    <div class="stat-card"><div><div class="stat-label">Pendientes</div><div class="stat-value"><?php echo $sPend['n']; ?></div></div><div class="stat-icon ico-amber"><i class="ti ti-clock"></i></div></div>
    <div class="stat-card"><div><div class="stat-label">Aprobados</div><div class="stat-value"><?php echo $sApro['n']; ?></div></div><div class="stat-icon ico-green"><i class="ti ti-checks"></i></div></div>
    <div class="stat-card"><div><div class="stat-label">Total Gastado</div><div class="stat-value">$<?php echo number_format($sGasto['n'],0); ?></div></div><div class="stat-icon ico-blue"><i class="ti ti-circle-dollar-sign"></i></div></div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="ti ti-clock"></i> Viáticos Recientes</span>
      <a href="viaticos.php" class="btn btn-outline btn-sm">Ver todos</a>
    </div>
    <table>
      <thead><tr><th>Empleado</th><th>Salida</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php if(mysqli_num_rows($resRecientes)>0): while($r=mysqli_fetch_assoc($resRecientes)): $e=strtolower($r['Estado']); ?>
      <tr>
        <td><?php echo htmlspecialchars($r['Empleado']); ?></td>
        <td><strong><?php echo htmlspecialchars($r['NombreSalida']); ?></strong></td>
        <td><?php echo $r['Fecha']; ?></td>
        <td><strong>$<?php echo number_format($r['Total'],2); ?></strong></td>
        <td><span class="badge badge-<?php echo $e; ?>"><?php echo ucfirst($e); ?></span></td>
        <td class="act-cell">
          <a href="ver_viaticos.php?id=<?php echo $r['IDViatico']; ?>" class="btn-action view" title="Ver"><i class="ti ti-eye"></i></a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="6"><div class="empty-state"><i class="ti ti-file-off"></i><p>Sin registros</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

</div></main>
</div>
<script>
const sb=document.getElementById('sidebar'),btn=document.getElementById('sidebarToggle');
btn.addEventListener('click',()=>{sb.classList.toggle('collapsed');btn.classList.toggle('ti-chevrons-left');btn.classList.toggle('ti-chevrons-right');});
</script>
</body></html>