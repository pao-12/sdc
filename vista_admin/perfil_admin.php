<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php?error=acceso"); exit();
}
include("../vista_usuarios/conexion.php");

$id  = (int)$_SESSION['id_usuario'];
$res = mysqli_query($conect, "SELECT * FROM empleado WHERE IDEmpleado='$id'");
$emp = mysqli_fetch_assoc($res);

$msg = ''; $msgTipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'datos') {
        $nombre = mysqli_real_escape_string($conect, trim($_POST['Nombre']));
        $email  = mysqli_real_escape_string($conect, trim($_POST['Email']));
        if (empty($nombre) || empty($email)) {
            $msg = 'Completa todos los campos.'; $msgTipo = 'error';
        } else {
            $ck = mysqli_query($conect, "SELECT IDEmpleado FROM empleado WHERE Email='$email' AND IDEmpleado!='$id'");
            if (mysqli_num_rows($ck) > 0) {
                $msg = 'Ese correo ya está en uso.'; $msgTipo = 'error';
            } else {
                mysqli_query($conect, "UPDATE empleado SET Nombre='$nombre', Email='$email' WHERE IDEmpleado='$id'");
                $_SESSION['nombre'] = $nombre;
                $_SESSION['email']  = $email;
                $emp['Nombre'] = $nombre;
                $emp['Email']  = $email;
                $msg = 'Datos actualizados correctamente.'; $msgTipo = 'ok';
            }
        }
    }

    if ($accion === 'password') {
        $actual    = $_POST['PassActual']    ?? '';
        $nueva     = $_POST['PassNueva']     ?? '';
        $confirmar = $_POST['PassConfirmar'] ?? '';
        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            $msg = 'Completa todos los campos de contraseña.'; $msgTipo = 'error';
        } elseif (strlen($nueva) < 6) {
            $msg = 'La nueva contraseña debe tener al menos 6 caracteres.'; $msgTipo = 'error';
        } elseif ($nueva !== $confirmar) {
            $msg = 'Las contraseñas no coinciden.'; $msgTipo = 'error';
        } elseif (!password_verify($actual, $emp['Password'])) {
            $msg = 'La contraseña actual es incorrecta.'; $msgTipo = 'error';
        } else {
            $hash = mysqli_real_escape_string($conect, password_hash($nueva, PASSWORD_DEFAULT));
            mysqli_query($conect, "UPDATE empleado SET Password='$hash' WHERE IDEmpleado='$id'");
            $msg = 'Contraseña actualizada correctamente.'; $msgTipo = 'ok';
        }
    }
}

// Stats del sistema para mostrar en perfil
$sTotalEmp  = mysqli_fetch_assoc(mysqli_query($conect, "SELECT COUNT(*) AS n FROM empleado WHERE Rol='empleado' AND Activo=1"));
$sTotalV    = mysqli_fetch_assoc(mysqli_query($conect, "SELECT COUNT(*) AS n FROM viatico"));
$sTotalG    = mysqli_fetch_assoc(mysqli_query($conect, "SELECT COALESCE(SUM(Monto),0) AS n FROM gasto"));
$inicial    = strtoupper(substr($emp['Nombre'], 0, 1));
$fechaAlta  = date('d/m/Y', strtotime($emp['FechaAlta']));
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Mi Perfil — Admin ViáticosApp</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="admin_style.css">
<style>
.hero-card{background:linear-gradient(135deg,#1a2232 60%,#2d1b69);border-radius:14px;padding:28px;display:flex;align-items:center;gap:22px;margin-bottom:18px;position:relative;overflow:hidden;}
.hero-card::before{content:'';position:absolute;right:-50px;top:-50px;width:220px;height:220px;background:#7c3aed18;border-radius:50%;}
.hero-card::after{content:'';position:absolute;right:80px;bottom:-70px;width:160px;height:160px;background:#7c3aed0d;border-radius:50%;}
.hero-av{width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4c1d95);display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;font-weight:700;flex-shrink:0;z-index:1;box-shadow:0 4px 20px rgba(124,58,237,.45);}
.hero-info{z-index:1;flex:1;}
.hero-name{font-size:22px;font-weight:700;color:#fff;margin-bottom:4px;}
.hero-email{font-size:13px;color:#a78bfa;margin-bottom:10px;}
.hero-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#7c3aed33;color:#c4b5fd;border:1px solid #7c3aed55;}
.hero-meta{z-index:1;text-align:right;}
.hero-meta-label{font-size:11px;color:#8a95a8;margin-bottom:4px;}
.hero-meta-val{font-size:13px;color:#c8d0e0;font-weight:500;}
.stats-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px;}
.stat-mini{background:#fff;border:1px solid #e5e8ed;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;}
.stat-mini-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.stat-mini-val{font-size:20px;font-weight:700;color:#1a2232;line-height:1;}
.stat-mini-label{font-size:11px;color:#8a95a8;margin-top:3px;}
.cards-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.pcard{background:#fff;border:1px solid #e5e8ed;border-radius:12px;overflow:hidden;}
.pcard-header{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid #f0f2f5;}
.pcard-header i{font-size:20px;color:#7c3aed;}
.pcard-title{font-size:14px;font-weight:600;color:#1a2232;}
.pcard-body{padding:18px;}
.pfield{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.pfield-label{font-size:11px;font-weight:600;color:#8a95a8;text-transform:uppercase;letter-spacing:.04em;}
.pinput{display:flex;align-items:center;border:1px solid #dde1e7;border-radius:8px;padding:9px 12px;gap:8px;transition:border-color .15s;}
.pinput:focus-within{border-color:#7c3aed;}
.pinput i{font-size:16px;color:#b0b8c8;flex-shrink:0;}
.pinput input{border:none;outline:none;font-size:13px;width:100%;color:#1a2232;background:transparent;}
.pinput.disabled{background:#f8fafb;}
.pinput.disabled input{color:#8a95a8;}
.eye-btn{background:none;border:none;cursor:pointer;color:#b0b8c8;font-size:16px;padding:0;display:flex;align-items:center;}
.eye-btn:hover{color:#6b7280;}
.btn-guardar{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:10px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-weight:600;font-size:13px;cursor:pointer;transition:background .15s;margin-top:4px;}
.btn-guardar:hover{background:#6d28d9;}
.str-bar{height:4px;border-radius:2px;background:#e5e8ed;margin:5px 0 3px;overflow:hidden;}
.str-fill{height:100%;width:0;border-radius:2px;transition:width .3s,background .3s;}
.str-label{font-size:11px;color:#8a95a8;}
.hint{font-size:11px;color:#b0b8c8;margin-top:-10px;margin-bottom:6px;}
</style>
</head><body>
<div class="app">
<?php include("sidebar.php"); ?>
<main class="main"><div class="main-inner">

  <div class="page-header">
    <div><h1 class="page-title">Mi Perfil</h1><p class="page-sub">Información y configuración del administrador</p></div>
  </div>

  <?php if($msg): ?>
  <div class="flash <?php echo $msgTipo; ?>">
    <i class="ti ti-<?php echo $msgTipo==='ok'?'circle-check':'alert-circle'; ?>"></i>
    <?php echo htmlspecialchars($msg); ?>
  </div>
  <?php endif; ?>

  <!-- Hero -->
  <div class="hero-card">
    <div class="hero-av"><?php echo $inicial; ?></div>
    <div class="hero-info">
      <div class="hero-name"><?php echo htmlspecialchars($emp['Nombre']); ?></div>
      <div class="hero-email"><?php echo htmlspecialchars($emp['Email']); ?></div>
      <span class="hero-badge"><i class="ti ti-shield-check"></i> Administrador</span>
    </div>
    <div class="hero-meta">
      <div class="hero-meta-label">Administrador desde</div>
      <div class="hero-meta-val"><?php echo $fechaAlta; ?></div>
    </div>
  </div>

  <!-- Stats sistema -->
  <div class="stats-mini">
    <div class="stat-mini">
      <div class="stat-mini-icon ico-purple"><i class="ti ti-users"></i></div>
      <div><div class="stat-mini-val"><?php echo (int)$sTotalEmp['n']; ?></div><div class="stat-mini-label">Empleados activos</div></div>
    </div>
    <div class="stat-mini">
      <div class="stat-mini-icon ico-blue"><i class="ti ti-briefcase"></i></div>
      <div><div class="stat-mini-val"><?php echo (int)$sTotalV['n']; ?></div><div class="stat-mini-label">Viáticos registrados</div></div>
    </div>
    <div class="stat-mini">
      <div class="stat-mini-icon ico-green"><i class="ti ti-circle-dollar-sign"></i></div>
      <div><div class="stat-mini-val">$<?php echo number_format($sTotalG['n'],0); ?></div><div class="stat-mini-label">Total gestionado</div></div>
    </div>
  </div>

  <!-- Formularios -->
  <div class="cards-row">

    <!-- Datos personales -->
    <div class="pcard">
      <div class="pcard-header"><i class="ti ti-user-edit"></i><span class="pcard-title">Datos personales</span></div>
      <div class="pcard-body">
        <form method="POST">
          <input type="hidden" name="accion" value="datos">
          <div class="pfield">
            <label class="pfield-label">Nombre completo</label>
            <div class="pinput"><i class="ti ti-user"></i><input type="text" name="Nombre" value="<?php echo htmlspecialchars($emp['Nombre']); ?>" required></div>
          </div>
          <div class="pfield">
            <label class="pfield-label">Correo electrónico</label>
            <div class="pinput"><i class="ti ti-mail"></i><input type="email" name="Email" value="<?php echo htmlspecialchars($emp['Email']); ?>" required></div>
          </div>
          <div class="pfield">
            <label class="pfield-label">Rol</label>
            <div class="pinput disabled"><i class="ti ti-shield-check"></i><input type="text" value="Administrador" disabled></div>
          </div>
          <button type="submit" class="btn-guardar"><i class="ti ti-device-floppy"></i> Guardar cambios</button>
        </form>
      </div>
    </div>

    <!-- Cambiar contraseña -->
    <div class="pcard">
      <div class="pcard-header"><i class="ti ti-lock"></i><span class="pcard-title">Cambiar contraseña</span></div>
      <div class="pcard-body">
        <form method="POST">
          <input type="hidden" name="accion" value="password">
          <div class="pfield">
            <label class="pfield-label">Contraseña actual</label>
            <div class="pinput"><i class="ti ti-lock"></i><input type="password" name="PassActual" id="pA" placeholder="••••••••" required><button type="button" class="eye-btn" onclick="toggle('pA',this)"><i class="ti ti-eye"></i></button></div>
          </div>
          <div class="pfield">
            <label class="pfield-label">Nueva contraseña</label>
            <div class="pinput"><i class="ti ti-lock-open"></i><input type="password" name="PassNueva" id="pN" placeholder="Mín. 6 caracteres" required oninput="fuerza(this.value)"><button type="button" class="eye-btn" onclick="toggle('pN',this)"><i class="ti ti-eye"></i></button></div>
            <div class="str-bar"><div class="str-fill" id="strFill"></div></div>
            <span class="str-label" id="strLabel"></span>
          </div>
          <div class="pfield">
            <label class="pfield-label">Confirmar nueva contraseña</label>
            <div class="pinput"><i class="ti ti-lock-check"></i><input type="password" name="PassConfirmar" id="pC" placeholder="Repite la contraseña" required><button type="button" class="eye-btn" onclick="toggle('pC',this)"><i class="ti ti-eye"></i></button></div>
          </div>
          <p class="hint">Mínimo 6 caracteres. Usa letras, números y símbolos.</p>
          <button type="submit" class="btn-guardar"><i class="ti ti-key"></i> Actualizar contraseña</button>
        </form>
      </div>
    </div>

  </div>
</div></main>
</div>
<script>
function toggle(id,btn){const i=document.getElementById(id);const t=i.type==='text';i.type=t?'password':'text';btn.innerHTML=t?'<i class="ti ti-eye"></i>':'<i class="ti ti-eye-off"></i>';}
function fuerza(v){const f=document.getElementById('strFill'),l=document.getElementById('strLabel');let s=0;
if(v.length>=6)s++;if(v.length>=10)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;
const n=[{p:'0%',c:'#e5e8ed',t:''},{p:'25%',c:'#ef4444',t:'Muy débil'},{p:'50%',c:'#f97316',t:'Débil'},{p:'70%',c:'#eab308',t:'Regular'},{p:'85%',c:'#22c55e',t:'Fuerte'},{p:'100%',c:'#10b981',t:'Muy fuerte'}];
const x=n[Math.min(s,5)];f.style.width=x.p;f.style.background=x.c;l.textContent=x.t;l.style.color=x.c;}
const sb=document.getElementById('sidebar'),btn=document.getElementById('sidebarToggle');
btn.addEventListener('click',()=>{sb.classList.toggle('collapsed');btn.classList.toggle('ti-chevrons-left');btn.classList.toggle('ti-chevrons-right');});
</script>
</body></html>
