<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit();
}
include("conexion.php");

if (!isset($_GET['id'])) {
    header("Location: principal.php");
    exit;
}

$idViatico = mysqli_real_escape_string($conect, $_GET['id']);

// Obtener datos del viático
$resV = mysqli_query($conect, "SELECT * FROM viatico WHERE IDViatico = '$idViatico'");
$viatico = mysqli_fetch_assoc($resV);

if (!$viatico) {
    echo "El registro no existe.";
    exit;
}

// Obtener gastos asociados
$resG = mysqli_query($conect, "SELECT * FROM gasto WHERE IDViatico = '$idViatico'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modificar Viático</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; height: 100vh; display: flex; width: 100%; align-items: center; justify-content: center; }
    .app { display: flex; width: 100vw; height: 100vh; overflow: hidden; }
    .sidebar { width: 270px; background: #1a2232; display: flex; flex-direction: column; color: #c8d0e0; }
    .sidebar-header { display: flex; align-items: center; gap: 10px; padding: 18px 16px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .logo-icon { width: 36px; height: 36px; background: #1db87a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; }
    .sidebar-brand { font-size: 14px; font-weight: 600; color: #fff; line-height: 1.2; }
    .sidebar-brand span { font-size: 13px; color: #8a95a8; font-weight: 400; display: block; }
    .nav { padding: 12px 8px; flex: 1; }
    .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: #8a95a8; text-decoration: none; font-size: 16px; }
    .nav-item.active { background: #1db87a; color: #fff; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .main-inner { width: 100%; max-width: 1400px; margin: 0 auto; padding: 24px 32px; overflow-y: auto; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #8a95a8; cursor: pointer; margin-bottom: 14px; background: none; border: none; }
    .form-card { background: #fff; border: 1px solid #e5e8ed; border-radius: 12px; padding: 28px; display: flex; flex-direction: column; gap: 22px; }
    .top-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field-label { font-size: 13px; font-weight: 500; color: #1a2232; }
    .input-wrap { display: flex; align-items: center; border: 1px solid #dde1e7; border-radius: 8px; padding: 9px 12px; background: #fff; gap: 8px; }
    .input-wrap input { border: none; outline: none; font-size: 13px; width: 100%; }
    .gastos-header { display: flex; align-items: center; justify-content: space-between; }
    .btn-agregar { display: flex; align-items: center; gap: 5px; font-size: 13px; color: #1db87a; background: none; border: none; cursor: pointer; }
    .gastos-list { display: flex; flex-direction: column; gap: 10px; }
    .gasto-row { display: grid; align-items: center; gap: 12px; grid-template-columns: 40px minmax(300px, 1fr) 180px 90px; }
    .gasto-num { font-size: 13px; color: #b0b8c8; text-align: right; }
    .gasto-nombre { border: 1px solid #dde1e7; border-radius: 8px; padding: 9px 12px; font-size: 13px; outline: none; }
    .gasto-monto-wrap { display: flex; align-items: center; border: 1px solid #dde1e7; border-radius: 8px; padding: 9px 12px; gap: 6px; }
    .gasto-monto-wrap input { border: none; outline: none; font-size: 13px; width: 100%; }
    .gasto-actions { display: flex; align-items: center; gap: 6px; }
    .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e5e8ed; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #8a95a8; background: #fff; font-size: 16px; transition: all 0.15s; }
    .btn-icon:hover { background: #f0f2f5; color: #1a2232; border-color: #c8d0e0; }
    .btn-icon.danger:hover { color: #e53e3e; border-color: #fee2e2; background: #fff5f5; }
    .btn-icon.active-file { color: #1db87a !important; border-color: #1db87a !important; background: #f0fdf4 !important; }
    .total-box { background: #f8fafb; border: 1px solid #e5e8ed; border-radius: 8px; padding: 14px 18px; display: flex; justify-content: flex-end; align-items: center; gap: 10px; }
    .total-value { font-size: 20px; font-weight: 700; color: #1db87a; }
    .form-footer { display: flex; justify-content: flex-end; align-items: center; gap: 15px; }
    .btn-cancel { padding: 10px 22px; border-radius: 8px; border: 1px solid #dde1e7; background: #fff; cursor: pointer; }
    .btn-save { display: flex; align-items: center; gap: 7px; padding: 10px 22px; border-radius: 8px; border: none; background: #1db87a; color: #fff; font-weight: 600; cursor: pointer; }
    .file-status { font-size: 11px; color: #1db87a; display: block; margin-top: 2px; }
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
      <a href="principal.php" class="nav-item active"><i class="ti ti-briefcase"></i><span> Mis Viáticos </span></a>
      <a href="vista_usuarios/formulario.php" class="nav-item"><i class="ti ti-circle-plus"></i><span> Nuevo Viático </span></a>
      <a href="../vista_usuarios/perfil.php" class="nav-item"><i class="ti ti-user"></i><span>Mi Perfil</span></a>
      
    </nav>
    <div style="padding:12px 16px;border-top:1px solid rgba(255,255,255,0.08);">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div style="width:36px;height:36px;border-radius:50%;background:#1db87a22;border:2px solid #1db87a55;display:flex;align-items:center;justify-content:center;color:#1db87a;font-size:16px;font-weight:700;flex-shrink:0;">
          <?php echo isset($_SESSION['nombre']) ? strtoupper(substr($_SESSION['nombre'], 0, 1)) : 'U'; ?>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : ''; ?></div>
          <div style="font-size:11px;color:#8a95a8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?></div>
        </div>
      </div>
      <button style="display:flex;align-items:center;gap:8px;font-size:13px;color:#8a95a8;cursor:pointer;padding:6px 0;background:none;border:none;width:100%;" onclick="window.location.href='../verificardatos/terminarseccion.php'">
        <i class="ti ti-logout" style="font-size:16px;"></i><span>Cerrar Sesión</span>
      </button>
    </div>
  </aside>
 
  <main class="main">
    <div class="main-inner">
      <button type="button" class="back-link" onclick="window.location.href='mostrar.php'"><i class="ti ti-arrow-left"></i> Volver</button>
      <h1 style="margin-bottom: 20px; font-size:22px; color:#1a2232;">Editar Viático</h1>
   
      <form action="borrar.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="IDViatico" value="<?php echo $viatico['IDViatico']; ?>">
        
        <div class="form-card">
          <div class="top-row">
            <div class="field">
              <label class="field-label">Nombre de la Salida</label>
              <div class="input-wrap">
                <i class="ti ti-map-pin"></i>
                <input type="text" name="NombreSalida" value="<?php echo htmlspecialchars($viatico['NombreSalida']); ?>" required />
              </div>
            </div>
            <div class="field">
              <label class="field-label">Fecha</label>
              <div class="input-wrap">
                <i class="ti ti-calendar"></i>
                <input type="date" name="Fecha" value="<?php echo $viatico['Fecha']; ?>" required>
              </div>
            </div>
            <div class="field">
              <label class="field-label">Hora</label>
              <div class="input-wrap">
                <i class="ti ti-clock"></i>
                <input type="time" name="Hora" value="<?php echo $viatico['Hora']; ?>" required>
              </div>
            </div>
          </div>

          <div class="gastos-header">
            <span class="field-label">Gastos Registrados</span>
            <button type="button" class="btn-agregar" id="Agregar"><i class="ti ti-circle-plus"></i> Agregar Gasto</button>
          </div>

          <div class="gastos-list" id="gastosList">
            <?php 
            $contador = 1;
            while($gasto = mysqli_fetch_assoc($resG)): 
            ?>
            <div class="gasto-row">
              <span class="gasto-num"><?php echo $contador; ?>.</span>
              <div>
                <input class="gasto-nombre" type="text" name="NombreGasto[]" value="<?php echo htmlspecialchars($gasto['NombreGasto']); ?>" style="width:100%" required />
              </div>
              <div class="gasto-monto-wrap">
                <span>$</span>
                <input type="number" name="Monto[]" value="<?php echo $gasto['Monto']; ?>" step="0.01" class="monto-input" required />
              </div>
              <div class="gasto-actions">
                <input type="file" name="Comprobante[]" accept="image/*,.pdf" hidden class="archivo-input">
                <input type="hidden" name="ComprobanteActual[]" value="<?php echo $gasto['Comprobante']; ?>">
                <button type="button" class="btn-icon btn-foto<?php echo !empty($gasto['Comprobante']) ? ' active-file' : ''; ?>" title="Adjuntar comprobante">
                  <i class="ti ti-photo"></i>
                </button>
                <button type="button" class="btn-icon danger btn-remove" title="Eliminar gasto"><i class="ti ti-x"></i></button>
              </div>
              <?php if(!empty($gasto['Comprobante'])): ?>
                <span class="file-status" style="grid-column: 2 / -1;">Archivo cargado: <a href="uploads/<?php echo $gasto['Comprobante']; ?>" target="_blank"><?php echo $gasto['Comprobante']; ?></a></span>
              <?php endif; ?>
            </div>
            <?php 
            $contador++;
            endwhile; 
            ?>
          </div>

          <div class="total-box">
            <span>Total:</span>
            <span class="total-value" id="total">$0.00</span>
          </div>

          <div class="form-footer">
            <button type="button" class="btn-cancel" onclick="window.location.href='mostrar.php'">Cancelar</button>
            <button type="submit" class="btn-save"><i class="ti ti-device-floppy"></i> Guardar Cambios</button>
          </div>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
// Lógica JS idéntica para controlar filas dinámicas y cálculos
const gastosList = document.getElementById("gastosList");
const btnAgregar = document.getElementById("Agregar");
const totalDisplay = document.getElementById("total");

btnAgregar.addEventListener("click", () => {
    const numero = document.querySelectorAll(".gasto-row").length + 1;
    const row = document.createElement("div");
    row.classList.add("gasto-row");
    row.innerHTML = `
        <span class="gasto-num">${numero}.</span>
        <input class="gasto-nombre" type="text" name="NombreGasto[]" placeholder="Nombre del gasto" required />
        <div class="gasto-monto-wrap">
            <span>$</span>
            <input type="number" name="Monto[]" placeholder="0.00" step="0.01" class="monto-input" required />
        </div>
        <div class="gasto-actions">
            <input type="file" name="Comprobante[]" accept="image/*,.pdf" hidden class="archivo-input">
            <input type="hidden" name="ComprobanteActual[]" value="">
            <button type="button" class="btn-icon btn-foto" title="Adjuntar comprobante"><i class="ti ti-photo"></i></button>
            <button type="button" class="btn-icon danger btn-remove" title="Eliminar gasto"><i class="ti ti-x"></i></button>
        </div>
    `;
    gastosList.appendChild(row);
    actualizarEventosFila(row);
    calcularTotal();
});

function actualizarEventosFila(fila) {
    const btnFoto = fila.querySelector(".btn-foto");
    const inputArchivo = fila.querySelector(".archivo-input");
    const btnRemove = fila.querySelector(".btn-remove");
    const montoInput = fila.querySelector(".monto-input");

    btnFoto.onclick = () => inputArchivo.click();
    inputArchivo.onchange = function() {
        if(this.files[0]) { btnFoto.classList.add('active-file'); }
    };
    btnRemove.onclick = () => {
        if (document.querySelectorAll(".gasto-row").length > 1) { fila.remove(); renumerar(); calcularTotal(); }
    };
    montoInput.oninput = calcularTotal;
}

function calcularTotal() {
    let total = 0;
    document.querySelectorAll(".monto-input").forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) total += val;
    });
    totalDisplay.textContent = `$${total.toFixed(2)}`;
}

function renumerar() {
    document.querySelectorAll(".gasto-row").forEach((f, i) => { f.querySelector(".gasto-num").textContent = (i + 1) + "."; });
}

document.querySelectorAll(".gasto-row").forEach(fila => actualizarEventosFila(fila));
calcularTotal();
</script>
</body>
</html>