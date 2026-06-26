<?php
session_start();
if (!isset($_SESSION["id_usuario"])) { header("Location: ../index.php?error=acceso"); exit(); }
$_nombreSidebar = $_SESSION["nombre"];
$_emailSidebar  = $_SESSION["email"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ViáticosApp — Sistema de Gastos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f0f2f5;
      height: 100vh;
      display: flex;
      width: 100%;
      align-items: center;
      justify-content: center;
    }
 
    .app {
      display: flex;
      width: 100vw;
      max-width: none;
      height: 100vh;
      border-radius: 0;
      overflow: hidden;
      border: 1px solid #dde1e7;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
 
    /* ── SIDEBAR ── */
    .sidebar {
      width: 270px;
      min-width: 270px;
      background: #1a2232;
      display: flex;
      flex-direction: column;
      color: #c8d0e0;
      transition: all .3s ease;
    }
 
    .sidebar-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 18px 16px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
 
    .logo-icon {
      width: 36px; height: 36px;
      background: #1db87a;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 18px;
    }
 
    .sidebar-brand { font-size: 14px; font-weight: 600; color: #fff; line-height: 1.2; }
    .sidebar-brand span { font-size: 13px; color: #8a95a8; font-weight: 400; display: block; }
 
    .sidebar-close {
      margin-left: auto;
      color: #8a95a8;
      font-size: 16px;
      cursor: pointer;
    }

    .sidebar.collapsed{
      width: 110px;
      min-width: 110px;
    }

    .sidebar.collapsed .sidebar-brand,
    .sidebar.collapsed .nav-item span,
    .sidebar.collapsed .user-info,
    .sidebar.collapsed .logout-btn span{
        display: none;
    }

    .sidebar.collapsed .sidebar-header{
        justify-content: center;
    }

    .sidebar.collapsed .nav-item{
        justify-content: center;
        padding: 18px 0;
    }

    .sidebar.collapsed .sidebar-footer{
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .sidebar.collapsed .logout-btn{
        justify-content: center;
    }

    .sidebar.collapsed .user-card{
        justify-content: center;
    }

    .nav { padding: 12px 8px; flex: 1; }
 
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px;
      border-radius: 8px;
      font-size: 13px;
      color: #8a95a8;
      cursor: pointer;
      transition: background 0.15s;
      margin-bottom: 2px;
      text-decoration: none;
      font-size: 16px;
    }
    .nav-item:hover { background: rgba(255,255,255,0.06); color: #c8d0e0; }
    .nav-item.active { background: #1db87a; color: #fff; }
    .nav-item i { font-size: 22px; }
 
    .sidebar-footer {
      padding: 12px 16px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
 
    .user-card {
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 12px;
    }
 
    .user-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: #253044;
      display: flex; align-items: center; justify-content: center;
      color: #8a95a8;
      font-size: 18px;
    }
 
    .user-info { flex: 1; min-width: 0; }
 
    .user-name-placeholder {
      height: 12px;
      border-radius: 4px;
      background: #253044;
      width: 100px;
      margin-bottom: 5px;
    }
 
    .user-email-placeholder {
      height: 10px;
      border-radius: 4px;
      background: #253044;
      width: 70px;
    }
 
    .logout-btn {
      display: flex; align-items: center; gap: 8px;
      font-size: 12px;
      color: #8a95a8;
      cursor: pointer;
      padding: 6px 0;
      background: none;
      border: none;
      width: 100%;
    }
    .logout-btn i { font-size: 15px; }
 
    /* ── MAIN ── */
    .main {
      flex: 1;
      background: #f0f2f5;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      width: 100%;
    }
 
    .main-header { padding: 24px 28px 16px; }
    .main-title { font-size: 32px; font-weight: 600; color: #1a2232; }
    .main-subtitle { font-size: 16px; color: #8a95a8; margin-top: 2px; }
    .main-inner{
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px 32px;
        overflow-y: auto;
    }
    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 13px; color: #8a95a8; cursor: pointer;
      margin-bottom: 14px; background: none; border: none;
      transition: color 0.15s; padding: 0;
    }
    .back-link:hover { color: #1db87a; }
    .back-link i { font-size: 16px; }
 
    /* FORM CARD */
    .form-card {
      background: #fff; border: 1px solid #e5e8ed;
      border-radius: 12px; padding: 28px;
      display: flex; flex-direction: column; gap: 22px;
    }
    .top-row {
      display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;
    }
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field-label { font-size: 13px; font-weight: 500; color: #1a2232; }
    .field-label .req { color: #e53e3e; margin-left: 2px; }
    .input-wrap {
      display: flex; align-items: center;
      border: 1px solid #dde1e7; border-radius: 8px;
      padding: 9px 12px; background: #fff; gap: 8px;
      transition: border-color 0.15s;
    }
    .input-wrap:focus-within {
      border-color: #1db87a;
      box-shadow: 0 0 0 3px rgba(29,184,122,0.1);
    }
    .input-wrap i { font-size: 16px; color: #b0b8c8; flex-shrink: 0; }
    .input-wrap input {
      border: none; outline: none;
      font-size: 13px; color: #1a2232;
      width: 100%; background: transparent;
    }
    .input-wrap input::placeholder { color: #b0b8c8; }
 
    /* GASTOS */
    .gastos-header {
      display: flex; align-items: center; justify-content: space-between;
    }
    .gastos-label { font-size: 13px; font-weight: 500; color: #1a2232; }
    .gastos-label .req { color: #e53e3e; margin-left: 2px; }
    .btn-agregar {
      display: flex; align-items: center; gap: 5px;
      font-size: 13px; color: #1db87a; font-weight: 500;
      background: none; border: none; cursor: pointer;
      transition: color 0.15s;
    }
    .btn-agregar:hover { color: #17a06a; }
    .btn-agregar i { font-size: 16px; }
    .gastos-list { display: flex; flex-direction: column; gap: 10px; }
    
    .gasto-row { 
        display: grid; 
        align-items: center; 
        gap: 12px; 
        grid-template-columns: 40px minmax(300px, 1fr) 180px 90px;
    }
    .gasto-num { font-size: 13px; color: #b0b8c8; width: 20px; flex-shrink: 0; text-align: right; }
    
    .gasto-nombre {
      flex: 1; border: 1px solid #dde1e7; border-radius: 8px;
      padding: 9px 12px; font-size: 13px; color: #1a2232;
      outline: none; background: #fff; transition: border-color 0.15s;
    }
    .gasto-nombre::placeholder { color: #b0b8c8; }
    .gasto-nombre:focus {
      border-color: #1db87a;
      box-shadow: 0 0 0 3px rgba(29,184,122,0.1);
    }
    .gasto-monto-wrap {
      display: flex; align-items: center;
      border: 1px solid #dde1e7; border-radius: 8px;
      padding: 9px 12px; gap: 6px; background: #fff; transition: border-color 0.15s;
    }
    .gasto-monto-wrap:focus-within {
      border-color: #1db87a;
      box-shadow: 0 0 0 3px rgba(29,184,122,0.1);
    }
    .gasto-monto-wrap span { font-size: 13px; color: #b0b8c8; }
    .gasto-monto-wrap input {
      border: none; outline: none;
      font-size: 13px; color: #1a2232;
      width: 100%; background: transparent;
    }
    .gasto-actions { display: flex; justify-content: flex-end; align-items: center; gap: 6px; flex-shrink: 0; }
    .btn-icon {
      width: 30px; height: 30px; border-radius: 6px;
      border: 1px solid #e5e8ed; background: #fff;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: #b0b8c8; font-size: 16px;
      transition: color 0.15s, border-color 0.15s;
    }
    .btn-icon:hover { color: #1a2232; border-color: #b0b8c8; }
    .btn-icon.danger:hover { color: #e53e3e; border-color: #e53e3e; }
 
    /* TOTAL */
    .total-box {
      background: #f8fafb; border: 1px solid #e5e8ed;
      border-radius: 8px; padding: 14px 18px;
      display: flex; justify-content: flex-end; align-items: center; gap: 10px;
      width: 100%; margin-top: 10px; padding-right: 30px;
    }
    .total-label { font-size: 14px; font-weight: 500; color: #8a95a8; }
    .total-value { font-size: 20px; font-weight: 700; color: #1db87a; }
 
    /* INFO BANNER */
    .info-banner {
      background: #eaf6f2; border: 1px solid #b6e4d2;
      border-radius: 8px; padding: 12px 16px;
      display: flex; align-items: flex-start; gap: 10px;
      font-size: 12px; color: #2d6a4f; line-height: 1.6;
    }
    .info-banner i { font-size: 16px; color: #1db87a; flex-shrink: 0; margin-top: 1px; }
    .info-banner strong { font-weight: 600; }
 
    /* FOOTER BOTONES */
    .form-footer {
      display: flex; justify-content: flex-end; align-items: center; gap: 15px;
      margin-top: 10px;
    }
    .btn-cancel {
      padding: 10px 22px; border-radius: 8px;
      border: 1px solid #dde1e7; background: #fff;
      font-size: 13px; font-weight: 500; color: #1a2232;
      cursor: pointer; transition: background 0.15s;
    }
    .btn-cancel:hover { background: #f0f2f5; }
    .btn-save {
      display: flex; align-items: center; gap: 7px;
      padding: 10px 22px; border-radius: 8px; border: none;
      background: #1db87a; font-size: 13px; font-weight: 600; color: #fff;
      cursor: pointer; transition: background 0.15s;
    }
    .btn-save:hover { background: #17a06a; }
    .btn-save i { font-size: 16px; }
 
    .main-inner::-webkit-scrollbar { width: 6px; }
    .main-inner::-webkit-scrollbar-track { background: transparent; }
    .main-inner::-webkit-scrollbar-thumb { background: #dde1e7; border-radius: 3px; }

    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator{
        display: none;
    }

    input[type="date"],
    input[type="time"]{
        -webkit-appearance: none;
        appearance: none;
    }

    .input-wrap i{
        cursor: pointer;
        color: #b0b8c8;
        font-size: 18px;
    }
    .input-wrap i:hover{ color: #1db87a; }

    /* ── Preview comprobante ── */
    .preview-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        padding: 6px 10px;
        background: #f0faf5;
        border: 1px solid #b6e4d2;
        border-radius: 8px;
        grid-column: 2 / -1;
    }

    .preview-img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dde1e7;
        cursor: pointer;
    }

    .preview-img:hover {
        transform: scale(1.05);
        transition: transform 0.15s;
    }

    .preview-pdf {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #2d6a4f;
    }

    .preview-pdf i { font-size: 22px; color: #e53e3e; }
    .preview-pdf span {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .preview-remove {
        margin-left: auto;
        background: none;
        border: none;
        cursor: pointer;
        color: #b0b8c8;
        font-size: 14px;
        display: flex;
        align-items: center;
        padding: 2px;
        border-radius: 4px;
        transition: color 0.15s;
    }
    .preview-remove:hover { color: #e53e3e; }
</style>
</head>
<body>
<div class="app">
 
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-icon"><i class="ti ti-circle-dollar-sign"></i></div>
      <div class="sidebar-brand">
        SYSTEM VIATIC
        <span>Sistema de gastos</span>
      </div>
     <i class="ti ti-chevrons-left sidebar-close"></i> 
    </div>
 
    <nav class="nav">
      <a href="/SDC2/principal.php" class="nav-item">
        <i class="ti ti-briefcase"></i>
        <span> Mis Viáticos </span>
      </a>
      <a href="/SDC2/vista_usuarios/formulario.php" class="nav-item active">
        <i class="ti ti-circle-plus"></i>
        <span> Nuevo Viático </span>
      </a>
      <a href="../vista_usuarios/perfil.php" class="nav-item">
        <i class="ti ti-user"></i>
        <span> Mi Perfil </span>
      </a>
    </nav>
 
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar" style="width:36px;height:36px;border-radius:50%;background:#1db87a22;border:2px solid #1db87a55;display:flex;align-items:center;justify-content:center;color:#1db87a;font-size:16px;font-weight:700;flex-shrink:0;">
          <?php echo strtoupper(substr($_nombreSidebar, 0, 1)); ?>
        </div>
        <div class="user-info">
          <div style="font-size:13px;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($_nombreSidebar); ?></div>
          <div style="font-size:11px;color:#8a95a8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;"><?php echo htmlspecialchars($_emailSidebar); ?></div>
        </div>
      </div>
     <button class="logout-btn" onclick="window.location.href='/SDC2/verificardatos/terminarseccion.php'">
        <i class="ti ti-logout"></i><span>Cerrar Sesión</span>
      </button>
    </div>
  </aside>
 
  <main class="main">
    <div class="main-inner">

      <button type="button" class="back-link" onclick="window.location.href='/SDC2/principal.php'">
        <i class="ti ti-arrow-left"></i>
        Volver al panel
      </button>

      <h1 class="page-title" style="margin-bottom: 20px;">Nuevo Viático</h1>
   
      <form action="insertar.php" method="POST" enctype="multipart/form-data">
        <div class="form-card">

          <div class="top-row">
            <div class="field">
              <label class="field-label">Nombre de la Salida <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="ti ti-map-pin"></i>
                <input type="text" name="NombreSalida" placeholder="Ej: Visita a Planta Monterrey" required />
              </div>
            </div>

            <div class="field">
              <label class="field-label">Fecha <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="ti ti-calendar" onclick="document.getElementById('Fecha').showPicker()"></i>
                <input type="date" id="Fecha" name="Fecha" required>
              </div>
            </div>

            <div class="field">
              <label class="field-label">Hora <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="ti ti-clock" onclick="document.getElementById('Hora').showPicker()"></i>
                <input type="time" id="Hora" name="Hora" required>
              </div>
            </div>
          </div>

          <div class="gastos-header">
            <span class="gastos-label">Gastos <span class="req">*</span></span>
            <button type="button" class="btn-agregar" id="Agregar">
              <i class="ti ti-circle-plus"></i>
              Agregar Gasto
            </button>
          </div>

          <div class="gastos-list" id="gastosList">
            <div class="gasto-row">
              <span class="gasto-num">1.</span>
              <input class="gasto-nombre" type="text" name="NombreGasto[]" placeholder="Nombre del gasto" required />
              <div class="gasto-monto-wrap">
                <span>$</span>
                <input type="number" name="Monto[]" placeholder="0.00" min="0" step="0.01" class="monto-input" required />
              </div>
              <div class="gasto-actions">
                <input type="file" name="Comprobante[]" accept="image/*,.pdf" hidden class="archivo-input">
                <button type="button" class="btn-icon btn-foto" title="Adjuntar comprobante">
                  <i class="ti ti-photo"></i>
                </button>
                <button type="button" class="btn-icon danger btn-remove" title="Eliminar gasto">
                  <i class="ti ti-x"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="total-box">
            <span class="total-label">Total:</span>
            <span class="total-value" id="total">$0.00</span>
          </div>

          <div class="info-banner">
            <i class="ti ti-info-circle"></i>
            <span>
              Agrega comprobantes en formato <strong>imagen</strong> (JPG, PNG, WebP, GIF)
              o <strong>PDF</strong> para cada gasto. Tamaño máximo: 10 MB por archivo.
            </span>
          </div>

          <div class="form-footer">
            <button type="button" class="btn-cancel" onclick="window.location.href='/SDC2/principal.php'">
              Cancelar
            </button>
            <button type="submit" class="btn-save">
              <i class="ti ti-device-floppy"></i>
              Guardar Viático
            </button>
          </div>

        </div>
      </form>
    </div>
  </main>
</div>

<script>
const gastosList = document.getElementById("gastosList");
const btnAgregar = document.getElementById("Agregar");
const totalDisplay = document.getElementById("total");

// 1. Agregar nueva fila de gastos dinámicamente
btnAgregar.addEventListener("click", () => {
    const numero = document.querySelectorAll(".gasto-row").length + 1;
    const row = document.createElement("div");
    row.classList.add("gasto-row");

    row.innerHTML = `
        <span class="gasto-num">${numero}.</span>
        <input class="gasto-nombre" type="text" name="NombreGasto[]" placeholder="Nombre del gasto" required />
        <div class="gasto-monto-wrap">
            <span>$</span>
            <input type="number" name="Monto[]" placeholder="0.00" min="0" step="0.01" class="monto-input" required />
        </div>
        <div class="gasto-actions">
            <input type="file" name="Comprobante[]" accept="image/*,.pdf" hidden class="archivo-input">
            <button type="button" class="btn-icon btn-foto" title="Adjuntar comprobante">
                <i class="ti ti-photo"></i>
            </button>
            <button type="button" class="btn-icon danger btn-remove" title="Eliminar gasto">
                <i class="ti ti-x"></i>
            </button>
        </div>
    `;
    gastosList.appendChild(row);
    actualizarEventosFila(row);
});

// 2. Escuchar eventos en las filas (Delegación de eventos por fila)
function actualizarEventosFila(fila) {
    const btnFoto = fila.querySelector(".btn-foto");
    const inputArchivo = fila.querySelector(".archivo-input");
    const btnRemove = fila.querySelector(".btn-remove");
    const montoInput = fila.querySelector(".monto-input");

    // Click en icono de galería para abrir explorador de archivos
    btnFoto.onclick = function() {
        inputArchivo.click();
    };

    // Cambios en el input file (Muestra el preview al seleccionar un archivo)
    inputArchivo.onchange = function() {
        const file = this.files[0];
        const actions = this.parentElement;

        // Limpiar preview viejo de esta fila si existía
        const previoExistente = fila.querySelector(".preview-wrap");
        if (previoExistente) previoExistente.remove();

        if (!file) return;

        const wrap = document.createElement("div");
        wrap.className = "preview-wrap";

        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = function(e) {
                wrap.innerHTML = `
                    <img src="${e.target.result}" class="preview-img" title="${file.name}">
                    <button type="button" class="preview-remove" title="Quitar archivo"><i class="ti ti-x"></i></button>
                `;
                fila.appendChild(wrap);
                bindPreviewRemove(wrap, inputArchivo, btnFoto);
            };
            reader.readAsDataURL(file);
        } else {
            wrap.innerHTML = `
                <div class="preview-pdf">
                    <i class="ti ti-file-type-pdf"></i>
                    <span title="${file.name}">${file.name.length > 20 ? file.name.substring(0,18)+'…' : file.name}</span>
                </div>
                <button type="button" class="preview-remove" title="Quitar archivo"><i class="ti ti-x"></i></button>
            `;
            fila.appendChild(wrap);
            bindPreviewRemove(wrap, inputArchivo, btnFoto);
        }

        // Marcar botón en verde indicando que ya tiene archivo adjunto
        btnFoto.style.borderColor = "#1db87a";
        btnFoto.style.color = "#1db87a";
    };

    // Eliminar fila de gastos
    btnRemove.onclick = function() {
        // Evitar dejar el formulario sin filas obligatorias
        if (document.querySelectorAll(".gasto-row").length > 1) {
            fila.remove();
            renumerar();
            calcularTotal();
        } else {
            alert("Debes ingresar al menos un gasto.");
        }
    };

    // Calcular en tiempo real mientras el usuario escribe la cantidad
    montoInput.oninput = calcularTotal;
}

// 3. Quitar archivo del preview
function bindPreviewRemove(wrap, input, btnFoto) {
    wrap.querySelector(".preview-remove").onclick = function() {
        input.value = "";
        btnFoto.style.borderColor = "";
        btnFoto.style.color = "";
        wrap.remove();
    };
}

// 4. Calcular sumatoria total del formulario
function calcularTotal() {
    let total = 0;
    document.querySelectorAll(".monto-input").forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) {
            total += val;
        }
    });
    totalDisplay.textContent = `$${total.toFixed(2)}`;
}

// 5. Ajustar los índices visibles (1., 2., 3...)
function renumerar() {
    document.querySelectorAll(".gasto-row").forEach((fila, index) => {
        fila.querySelector(".gasto-num").textContent = (index + 1) + ".";
    });
}

// Manejar el Toggle de la barra lateral (Sidebar)
const sidebar = document.querySelector('.sidebar');
const toggleBtn = document.querySelector('.sidebar-close');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    if (sidebar.classList.contains('collapsed')) {
        toggleBtn.classList.replace('ti-chevrons-left', 'ti-chevrons-right');
    } else {
        toggleBtn.classList.replace('ti-chevrons-right', 'ti-chevrons-left');
    }
});

// Inicializar la primera fila nativa al cargar la página
document.querySelectorAll(".gasto-row").forEach(fila => actualizarEventosFila(fila));
</script>
</body>
</html>