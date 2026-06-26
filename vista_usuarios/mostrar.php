<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
include("conexion.php");

if (!isset($_GET['id'])) {
    header("Location: ../principal.php");
    exit();
}

$idEmpleado = $_SESSION['id_usuario'];
$idViatico  = (int) $_GET['id'];

// Verificar que el viático pertenece al empleado logueado
$resV = mysqli_query($conect, "SELECT * FROM viatico WHERE IDViatico = '$idViatico' AND IDEmpleado = '$idEmpleado'");
$viatico = mysqli_fetch_assoc($resV);

if (!$viatico) {
    header("Location: ../principal.php");
    exit();
}

$resG = mysqli_query($conect, "SELECT * FROM gasto WHERE IDViatico = '$idViatico'");
$estado = strtolower($viatico['Estado'] ?? 'pendiente');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle Viático — ViáticosApp</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; display: flex; height: 100vh; width: 100vw; overflow: hidden; }
.app { display: flex; width: 100%; height: 100%; }
.sidebar { width: 270px; min-width: 270px; background: #1a2232; display: flex; flex-direction: column; color: #c8d0e0; }
.sidebar-header { display: flex; align-items: center; gap: 10px; padding: 18px 16px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.logo-icon { width: 36px; height: 36px; background: #1db87a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0; }
.sidebar-brand { font-size: 14px; font-weight: 600; color: #fff; line-height: 1.2; }
.sidebar-brand span { font-size: 13px; color: #8a95a8; font-weight: 400; display: block; }
.nav { padding: 12px 8px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; font-size: 15px; color: #8a95a8; text-decoration: none; margin-bottom: 2px; }
.nav-item:hover { background: rgba(255,255,255,0.06); color: #c8d0e0; }
.nav-item.active { background: #1db87a; color: #fff; }
.nav-item i { font-size: 20px; }
.sidebar-footer { padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.08); }
.user-card { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.user-avatar { width: 36px; height: 36px; border-radius: 50%; background: #1db87a22; border: 2px solid #1db87a55; display: flex; align-items: center; justify-content: center; color: #1db87a; font-size: 16px; font-weight: 700; flex-shrink: 0; }
.user-info { flex: 1; min-width: 0; }
.user-name { font-size: 13px; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-email { font-size: 11px; color: #8a95a8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.logout-btn { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8a95a8; cursor: pointer; padding: 6px 0; background: none; border: none; width: 100%; }
.logout-btn:hover { color: #e53e3e; }

.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.main-inner { width: 100%; max-width: 900px; margin: 0 auto; padding: 28px 32px; overflow-y: auto; height: 100%; }
.back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #8a95a8; text-decoration: none; margin-bottom: 18px; transition: color 0.15s; }
.back-link:hover { color: #1db87a; }

.detail-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.detail-title { font-size: 26px; font-weight: 700; color: #1a2232; }
.detail-date { font-size: 13px; color: #8a95a8; margin-top: 4px; }

.badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
.badge-pendiente  { background: #fef3c7; color: #d97706; }
.badge-aprobado   { background: #d1fae5; color: #059669; }
.badge-rechazado  { background: #fee2e2; color: #dc2626; }

.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
.info-card { background: #fff; border: 1px solid #e5e8ed; border-radius: 10px; padding: 14px 18px; }
.info-card-label { font-size: 11px; color: #8a95a8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; font-weight: 600; }
.info-card-value { font-size: 15px; font-weight: 600; color: #1a2232; }

.gastos-card { background: #fff; border: 1px solid #e5e8ed; border-radius: 12px; overflow: hidden; }
.gastos-card-header { padding: 14px 18px; border-bottom: 1px solid #e5e8ed; font-size: 13px; font-weight: 600; color: #1a2232; }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th { background: #f8fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #8a95a8; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #e5e8ed; }
td { padding: 14px 16px; color: #4a5568; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
tr:last-child td { border-bottom: none; }

.comp-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #dde1e7; cursor: pointer; transition: transform 0.15s; }
.comp-thumb:hover { transform: scale(1.1); }
.comp-pdf { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #2d6a4f; text-decoration: none; }
.comp-pdf i { font-size: 20px; color: #e53e3e; }
.comp-none { color: #b0b8c8; font-size: 12px; display: flex; align-items: center; gap: 5px; }

.total-row td { background: #f0fdf4; font-weight: 700; color: #065f46; font-size: 15px; }

.footer-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
.btn-back { padding: 9px 20px; border-radius: 8px; border: 1px solid #e5e8ed; background: #fff; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; color: #1a2232; }
.btn-edit { display: flex; align-items: center; gap: 6px; padding: 9px 20px; border-radius: 8px; background: #1db87a; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; }
.btn-edit:hover { background: #17a06a; }

/* Lightbox */
.lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.82); z-index: 2000; align-items: center; justify-content: center; }
.lightbox.open { display: flex; }
.lightbox img { max-width: 90vw; max-height: 88vh; border-radius: 8px; box-shadow: 0 0 40px rgba(0,0,0,0.5); }
.lightbox-close { position: fixed; top: 18px; right: 24px; color: #fff; font-size: 28px; cursor: pointer; }
</style>
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-icon"><i class="ti ti-circle-dollar-sign"></i></div>
      <div class="sidebar-brand">SYSTEM VIATIC<span>Sistema de gastos</span></div>
    </div>
    <nav class="nav">
      <a href="principal.php" class="nav-item active"><i class="ti ti-briefcase"></i><span>Mis Viáticos</span></a>
      <a href="../vista_usuarios/formulario.php" class="nav-item"><i class="ti ti-circle-plus"></i><span>Nuevo Viático</span></a>
      <a href="../vista_usuarios/perfil.php" class="nav-item"><i class="ti ti-user"></i><span>Mi Perfil</span></a>
    </nav>
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></div>
          <div class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
        </div>
      </div>
      <button class="logout-btn" onclick="window.location.href='../verificardatos/terminarseccion.php'">
        <i class="ti ti-logout"></i><span>Cerrar Sesión</span>
      </button>
    </div>
  </aside>

  <main class="main">
    <div class="main-inner">
      <a href="mostrar.php" class="back-link"><i class="ti ti-arrow-left"></i> Volver al panel</a>

      <div class="detail-header">
        <div>
          <h1 class="detail-title"><?php echo htmlspecialchars($viatico['NombreSalida']); ?></h1>
          <p class="detail-date">Registrado el <?php echo date('d \d\e F \d\e Y', strtotime($viatico['FechaRegistro'])); ?></p>
        </div>
        <span class="badge badge-<?php echo $estado; ?>"><?php echo ucfirst($estado); ?></span>
      </div>

      <div class="info-grid">
        <div class="info-card">
          <div class="info-card-label">Nombre de la Salida</div>
          <div class="info-card-value"><?php echo htmlspecialchars($viatico['NombreSalida']); ?></div>
        </div>
        <div class="info-card">
          <div class="info-card-label">Fecha</div>
          <div class="info-card-value"><?php echo htmlspecialchars($viatico['Fecha']); ?></div>
        </div>
        <div class="info-card">
          <div class="info-card-label">Hora</div>
          <div class="info-card-value"><?php echo htmlspecialchars($viatico['Hora']); ?></div>
        </div>
      </div>

      <div class="gastos-card">
        <div class="gastos-card-header">Gastos Registrados</div>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Concepto</th>
              <th>Comprobante</th>
              <th style="text-align:right">Precio</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $totalGastos = 0;
            $contador = 1;
            while ($gasto = mysqli_fetch_assoc($resG)):
              $totalGastos += $gasto['Monto'];
            ?>
            <tr>
              <td><?php echo $contador++; ?></td>
              <td><?php echo htmlspecialchars($gasto['NombreGasto']); ?></td>
              <td>
                <?php if (!empty($gasto['Comprobante'])): ?>
                  <?php
                    $ext = strtolower(pathinfo($gasto['Comprobante'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])):
                  ?>
                    <img src="uploads/<?php echo htmlspecialchars($gasto['Comprobante']); ?>"
                         class="comp-thumb"
                         onclick="abrirLightbox(this.src)"
                         alt="Comprobante">
                  <?php else: ?>
                    <a href="uploads/<?php echo htmlspecialchars($gasto['Comprobante']); ?>" target="_blank" class="comp-pdf">
                      <i class="ti ti-file-type-pdf"></i> Ver PDF
                    </a>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="comp-none"><i class="ti ti-photo-off"></i> Sin adjuntar</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right"><strong>$<?php echo number_format($gasto['Monto'], 2); ?></strong></td>
            </tr>
            <?php endwhile; ?>
            <tr class="total-row">
              <td colspan="3"><strong>Total</strong></td>
              <td style="text-align:right">$<?php echo number_format($totalGastos, 2); ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="footer-actions">
        <a href="/SDC2/principal.php" class="btn-back">Volver</a>
        <a href="actualizar.php?id=<?php echo $idViatico; ?>" class="btn-edit"><i class="ti ti-pencil"></i> Editar</a>
      </div>
    </div>
  </main>
</div>

<!-- Lightbox imágenes -->
<div class="lightbox" id="lightbox" onclick="cerrarLightbox()">
  <i class="ti ti-x lightbox-close"></i>
  <img id="lightboxImg" src="" alt="">
</div>

<script>
function abrirLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
}
function cerrarLightbox() {
  document.getElementById('lightbox').classList.remove('open');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLightbox(); });
</script>
</body>
</html>
