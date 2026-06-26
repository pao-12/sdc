<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php?error=acceso"); exit();
}
include("../vista_usuarios/conexion.php");

// ── Filtros ────────────────────────────────────────────────────────────
$filtroEmp    = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
$filtroEst    = $_GET['estado']   ?? 'todos';
$filtroMes    = $_GET['mes']      ?? '';
$filtroAnio   = $_GET['anio']     ?? date('Y');

$whereV = "WHERE 1";
if ($filtroEmp)           $whereV .= " AND v.IDEmpleado='$filtroEmp'";
if ($filtroEst!=='todos') $whereV .= " AND v.Estado='$filtroEst'";
if ($filtroMes)           $whereV .= " AND MONTH(v.Fecha)='$filtroMes'";
if ($filtroAnio)          $whereV .= " AND YEAR(v.Fecha)='$filtroAnio'";

// ── Datos para gráficas ────────────────────────────────────────────────

// 1. Gastos por mes (año seleccionado)
$resMes = mysqli_query($conect, "
    SELECT MONTH(v.Fecha) AS Mes, COALESCE(SUM(g.Monto),0) AS Total
    FROM viatico v LEFT JOIN gasto g ON v.IDViatico=g.IDViatico
    WHERE YEAR(v.Fecha)='$filtroAnio'
    " . ($filtroEmp ? "AND v.IDEmpleado='$filtroEmp'" : "") . "
    GROUP BY MONTH(v.Fecha) ORDER BY Mes");
$datosMes = array_fill(1, 12, 0);
while($r=mysqli_fetch_assoc($resMes)) $datosMes[(int)$r['Mes']] = (float)$r['Total'];

// 2. Viáticos por estado
$resEst = mysqli_query($conect, "SELECT Estado, COUNT(*) AS n FROM viatico $whereV GROUP BY Estado");
$datosEst = ['pendiente'=>0,'aprobado'=>0,'rechazado'=>0];
while($r=mysqli_fetch_assoc($resEst)) $datosEst[$r['Estado']] = (int)$r['n'];

// 3. Top empleados por gasto
$resTop = mysqli_query($conect, "
    SELECT e.Nombre, COALESCE(SUM(g.Monto),0) AS Total
    FROM empleado e
    JOIN viatico v ON e.IDEmpleado=v.IDEmpleado
    LEFT JOIN gasto g ON v.IDViatico=g.IDViatico
    WHERE YEAR(v.Fecha)='$filtroAnio'
    GROUP BY e.IDEmpleado ORDER BY Total DESC LIMIT 6");
$nombresTop=[]; $totalesTop=[];
while($r=mysqli_fetch_assoc($resTop)){$nombresTop[]=$r['Nombre'];$totalesTop[]=(float)$r['Total'];}

// ── Tabla de reporte ───────────────────────────────────────────────────
$resTabla = mysqli_query($conect, "
    SELECT v.NombreSalida, v.Fecha, v.Hora, v.Estado, e.Nombre AS Empleado,
           COALESCE(SUM(g.Monto),0) AS Total
    FROM viatico v
    JOIN empleado e ON v.IDEmpleado=e.IDEmpleado
    LEFT JOIN gasto g ON v.IDViatico=g.IDViatico
    $whereV GROUP BY v.IDViatico ORDER BY v.Fecha DESC");

// Stats resumen
$resResumen = mysqli_query($conect, "
    SELECT COUNT(DISTINCT v.IDViatico) AS TotalV,
           COALESCE(SUM(g.Monto),0) AS TotalG,
           COUNT(DISTINCT v.IDEmpleado) AS TotalEmp
    FROM viatico v LEFT JOIN gasto g ON v.IDViatico=g.IDViatico $whereV");
$resumen = mysqli_fetch_assoc($resResumen);

// Lista empleados para filtro
$resEmps = mysqli_query($conect, "SELECT IDEmpleado,Nombre FROM empleado WHERE Activo=1 ORDER BY Nombre");

$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reportes — ViáticosApp Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="admin_style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<style>
.filters-card{background:#fff;border:1px solid #e5e8ed;border-radius:12px;padding:16px 20px;margin-bottom:16px;}
.filters-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;}
.ffield{display:flex;flex-direction:column;gap:4px;}
.ffield label{font-size:11px;font-weight:600;color:#8a95a8;text-transform:uppercase;letter-spacing:.04em;}
.fselect{border:1px solid #dde1e7;border-radius:8px;padding:8px 12px;font-size:13px;color:#1a2232;background:#fff;outline:none;min-width:150px;}
.fselect:focus{border-color:#7c3aed;}
.btn-filter{display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;background:#7c3aed;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;align-self:flex-end;}
.btn-filter:hover{background:#6d28d9;}

.resumen-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;}

.charts-row{display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px;}
.chart-card{background:#fff;border:1px solid #e5e8ed;border-radius:12px;padding:18px;}
.chart-title{font-size:13px;font-weight:600;color:#1a2232;margin-bottom:14px;display:flex;align-items:center;gap:7px;}
.chart-title i{font-size:18px;color:#7c3aed;}
.chart-wrap{position:relative;height:220px;}

.charts-row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;}

.tabla-card{background:#fff;border:1px solid #e5e8ed;border-radius:12px;overflow:hidden;margin-bottom:16px;}
.tabla-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f0f2f5;}
.tabla-title{font-size:14px;font-weight:600;color:#1a2232;display:flex;align-items:center;gap:8px;}
.tabla-title i{color:#7c3aed;font-size:18px;}
.btn-pdf{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#ef4444;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;}
.btn-pdf:hover{background:#dc2626;}
.badge-t{background:#d1fae5;color:#065f46;font-weight:700;padding:3px 9px;border-radius:6px;font-size:12px;}

/* Print / PDF styles */
@media print{
  .sidebar,.filters-card,.btn-pdf,.btn-filter,.page-header button{display:none!important;}
  .main{margin:0;padding:0;}
  body{background:#fff;}
}
</style>
</head><body>
<div class="app">
<?php include("sidebar.php"); ?>
<main class="main"><div class="main-inner">

  <div class="page-header">
    <div><h1 class="page-title">Reportes</h1><p class="page-sub">Análisis y estadísticas del sistema</p></div>
  </div>

  <!-- Filtros -->
  <div class="filters-card">
    <form method="GET" class="filters-row">
      <div class="ffield">
        <label>Empleado</label>
        <select name="empleado" class="fselect">
          <option value="0">Todos</option>
          <?php while($e=mysqli_fetch_assoc($resEmps)): ?>
          <option value="<?php echo $e['IDEmpleado']; ?>" <?php echo $filtroEmp==$e['IDEmpleado']?'selected':''; ?>>
            <?php echo htmlspecialchars($e['Nombre']); ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="ffield">
        <label>Estado</label>
        <select name="estado" class="fselect">
          <option value="todos"     <?php echo $filtroEst==='todos'?'selected':''; ?>>Todos</option>
          <option value="pendiente" <?php echo $filtroEst==='pendiente'?'selected':''; ?>>Pendiente</option>
          <option value="aprobado"  <?php echo $filtroEst==='aprobado'?'selected':''; ?>>Aprobado</option>
          <option value="rechazado" <?php echo $filtroEst==='rechazado'?'selected':''; ?>>Rechazado</option>
        </select>
      </div>
      <div class="ffield">
        <label>Mes</label>
        <select name="mes" class="fselect">
          <option value="">Todos</option>
          <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?php echo $m; ?>" <?php echo $filtroMes==$m?'selected':''; ?>><?php echo $meses[$m]; ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="ffield">
        <label>Año</label>
        <select name="anio" class="fselect">
          <?php for($a=date('Y');$a>=date('Y')-4;$a--): ?>
          <option value="<?php echo $a; ?>" <?php echo $filtroAnio==$a?'selected':''; ?>><?php echo $a; ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button type="submit" class="btn-filter"><i class="ti ti-filter"></i> Filtrar</button>
      <a href="reportes.php" style="display:flex;align-items:center;gap:5px;padding:9px 14px;border-radius:8px;border:1px solid #dde1e7;background:#fff;font-size:13px;color:#4a5568;text-decoration:none;align-self:flex-end;"><i class="ti ti-refresh"></i> Limpiar</a>
    </form>
  </div>

  <!-- Stats resumen -->
  <div class="resumen-row">
    <div class="stat-card"><div><div class="stat-label">Total Viáticos</div><div class="stat-value"><?php echo (int)$resumen['TotalV']; ?></div></div><div class="stat-icon ico-purple"><i class="ti ti-briefcase"></i></div></div>
    <div class="stat-card"><div><div class="stat-label">Total Gastado</div><div class="stat-value">$<?php echo number_format($resumen['TotalG'],2); ?></div></div><div class="stat-icon ico-green"><i class="ti ti-circle-dollar-sign"></i></div></div>
    <div class="stat-card"><div><div class="stat-label">Empleados con viáticos</div><div class="stat-value"><?php echo (int)$resumen['TotalEmp']; ?></div></div><div class="stat-icon ico-blue"><i class="ti ti-users"></i></div></div>
  </div>

  <!-- Gráficas fila 1 -->
  <div class="charts-row">
    <div class="chart-card">
      <div class="chart-title"><i class="ti ti-chart-bar"></i> Gasto mensual <?php echo $filtroAnio; ?></div>
      <div class="chart-wrap"><canvas id="chartMes"></canvas></div>
    </div>
    <div class="chart-card">
      <div class="chart-title"><i class="ti ti-chart-pie"></i> Viáticos por estado</div>
      <div class="chart-wrap"><canvas id="chartEstado"></canvas></div>
    </div>
  </div>

  <!-- Gráficas fila 2 -->
  <div class="charts-row2">
    <div class="chart-card">
      <div class="chart-title"><i class="ti ti-chart-bar"></i> Top empleados por gasto</div>
      <div class="chart-wrap"><canvas id="chartTop"></canvas></div>
    </div>
    <div class="chart-card">
      <div class="chart-title"><i class="ti ti-chart-donut"></i> Distribución por estado</div>
      <div class="chart-wrap"><canvas id="chartDona"></canvas></div>
    </div>
  </div>

  <!-- Tabla detallada -->
  <div class="tabla-card">
    <div class="tabla-header">
      <span class="tabla-title"><i class="ti ti-table"></i> Detalle de Viáticos</span>
      <button class="btn-pdf" onclick="descargarPDF()"><i class="ti ti-file-type-pdf"></i> Descargar PDF</button>
    </div>
    <table id="tablaReporte">
      <thead>
        <tr>
          <th>#</th>
          <th>Empleado</th>
          <th>Nombre de Salida</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Total</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if(mysqli_num_rows($resTabla)>0): $n=1; while($r=mysqli_fetch_assoc($resTabla)): $e=strtolower($r['Estado']); ?>
        <tr>
          <td><?php echo $n++; ?></td>
          <td><?php echo htmlspecialchars($r['Empleado']); ?></td>
          <td><strong><?php echo htmlspecialchars($r['NombreSalida']); ?></strong></td>
          <td><?php echo $r['Fecha']; ?></td>
          <td><?php echo $r['Hora']; ?></td>
          <td><span class="badge-t">$<?php echo number_format($r['Total'],2); ?></span></td>
          <td><span class="badge badge-<?php echo $e; ?>"><?php echo ucfirst($e); ?></span></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="7"><div class="empty-state"><i class="ti ti-file-off"></i><p>Sin datos con los filtros seleccionados.</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div></main>
</div>

<script>
// ── Datos PHP → JS ─────────────────────────────────────────────────────
const datosMes   = <?php echo json_encode(array_values($datosMes)); ?>;
const datosEst   = <?php echo json_encode(array_values($datosEst)); ?>;
const nombresTop = <?php echo json_encode($nombresTop); ?>;
const totalesTop = <?php echo json_encode($totalesTop); ?>;
const meses      = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const anio       = <?php echo $filtroAnio; ?>;

const colores = {
  purple:'#7c3aed', purpleL:'#ede9fe',
  green:'#059669',  greenL:'#d1fae5',
  amber:'#d97706',  amberL:'#fef3c7',
  red:'#dc2626',    redL:'#fee2e2',
  blue:'#2563eb',   blueL:'#dbeafe',
};

// Chart 1 — Barras por mes
new Chart(document.getElementById('chartMes'), {
  type: 'bar',
  data: {
    labels: meses,
    datasets:[{
      label:'Gasto ($)',
      data: datosMes,
      backgroundColor: '#7c3aed33',
      borderColor: '#7c3aed',
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{y:{beginAtZero:true, ticks:{callback:v=>'$'+v.toLocaleString()}}}
  }
});

// Chart 2 — Pie estados
new Chart(document.getElementById('chartEstado'), {
  type:'pie',
  data:{
    labels:['Pendiente','Aprobado','Rechazado'],
    datasets:[{
      data: datosEst,
      backgroundColor:['#fef3c7','#d1fae5','#fee2e2'],
      borderColor:['#d97706','#059669','#dc2626'],
      borderWidth:2
    }]
  },
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}
});

// Chart 3 — Barras horizontales top empleados
new Chart(document.getElementById('chartTop'), {
  type:'bar',
  data:{
    labels: nombresTop,
    datasets:[{
      label:'Total gastado ($)',
      data: totalesTop,
      backgroundColor:'#7c3aed55',
      borderColor:'#7c3aed',
      borderWidth:2,
      borderRadius:5
    }]
  },
  options:{
    indexAxis:'y',
    responsive:true, maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{x:{beginAtZero:true,ticks:{callback:v=>'$'+v.toLocaleString()}}}
  }
});

// Chart 4 — Dona
new Chart(document.getElementById('chartDona'), {
  type:'doughnut',
  data:{
    labels:['Pendiente','Aprobado','Rechazado'],
    datasets:[{
      data: datosEst,
      backgroundColor:['#d97706','#059669','#dc2626'],
      borderWidth:0,
      hoverOffset:6
    }]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    cutout:'65%',
    plugins:{legend:{position:'bottom'}}
  }
});

// ── Descargar PDF ───────────────────────────────────────────────────────
function descargarPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

  // Encabezado
  doc.setFillColor(28, 34, 50);
  doc.rect(0, 0, 297, 22, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('ViáticosApp — Reporte de Viáticos', 14, 13);
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  doc.text('Generado el ' + new Date().toLocaleDateString('es-MX', {day:'2-digit',month:'long',year:'numeric'}), 200, 13);

  // Subtítulo filtros
  doc.setTextColor(100, 100, 100);
  doc.setFontSize(9);
  doc.text(`Año: <?php echo $filtroAnio; ?>  |  Estado: <?php echo ucfirst($filtroEst); ?>  |  Total viáticos: <?php echo (int)$resumen['TotalV']; ?>  |  Total: $<?php echo number_format($resumen['TotalG'],2); ?>`, 14, 30);

  // Tabla
  const tabla = document.getElementById('tablaReporte');
  const headers = [];
  const rows    = [];

  tabla.querySelectorAll('thead th').forEach(th => headers.push(th.innerText));

  tabla.querySelectorAll('tbody tr').forEach(tr => {
    const celdas = [];
    tr.querySelectorAll('td').forEach(td => celdas.push(td.innerText.trim()));
    if (celdas.length > 1) rows.push(celdas);
  });

  doc.autoTable({
    head: [headers],
    body: rows,
    startY: 35,
    styles: { fontSize: 9, cellPadding: 3 },
    headStyles: { fillColor: [124, 58, 237], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [248, 250, 251] },
    columnStyles: { 5: { halign: 'right' }, 6: { halign: 'center' } },
    margin: { left: 14, right: 14 },
  });

  // Pie de página
  const totalPags = doc.internal.getNumberOfPages();
  for (let i = 1; i <= totalPags; i++) {
    doc.setPage(i);
    doc.setFontSize(8);
    doc.setTextColor(150);
    doc.text(`Página ${i} de ${totalPags}`, 270, doc.internal.pageSize.height - 8);
  }

  doc.save(`Reporte_Viaticos_<?php echo $filtroAnio; ?>.pdf`);
}

// Sidebar
const sb=document.getElementById('sidebar'),btn=document.getElementById('sidebarToggle');
btn.addEventListener('click',()=>{sb.classList.toggle('collapsed');btn.classList.toggle('ti-chevrons-left');btn.classList.toggle('ti-chevrons-right');});
</script>
</body></html>
