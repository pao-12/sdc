<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?error=acceso");
    exit();
}
include("conexion.php");

$idEmpleado = (int) $_SESSION['id_usuario'];

// Obtener datos actuales del empleado
$res = mysqli_query($conect, "SELECT * FROM empleado WHERE IDEmpleado = '$idEmpleado'");
$emp = mysqli_fetch_assoc($res);

$msg     = '';
$msgTipo = '';

// ── Procesar formulario ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ── Actualizar nombre / email ──────────────────────────────────────
    if ($accion === 'datos') {
        $nombre = mysqli_real_escape_string($conect, trim($_POST['Nombre']));
        $email  = mysqli_real_escape_string($conect, trim($_POST['Email']));

        if (empty($nombre) || empty($email)) {
            $msg = 'El nombre y el correo no pueden estar vacíos.';
            $msgTipo = 'error';
        } else {
            // Verificar que el email no lo use otro empleado
            $checkEmail = mysqli_query($conect, "SELECT IDEmpleado FROM empleado WHERE Email='$email' AND IDEmpleado != '$idEmpleado'");
            if (mysqli_num_rows($checkEmail) > 0) {
                $msg = 'Ese correo ya está registrado por otro usuario.';
                $msgTipo = 'error';
            } else {
                mysqli_query($conect, "UPDATE empleado SET Nombre='$nombre', Email='$email' WHERE IDEmpleado='$idEmpleado'");
                $_SESSION['nombre'] = $nombre;
                $_SESSION['email']  = $email;
                $emp['Nombre'] = $nombre;
                $emp['Email']  = $email;
                $msg = 'Datos actualizados correctamente.';
                $msgTipo = 'ok';
            }
        }
    }

    // ── Cambiar contraseña ─────────────────────────────────────────────
    if ($accion === 'password') {
        $actual    = $_POST['PassActual']    ?? '';
        $nueva     = $_POST['PassNueva']     ?? '';
        $confirmar = $_POST['PassConfirmar'] ?? '';

        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            $msg = 'Completa todos los campos de contraseña.';
            $msgTipo = 'error';
        } elseif (strlen($nueva) < 6) {
            $msg = 'La nueva contraseña debe tener al menos 6 caracteres.';
            $msgTipo = 'error';
        } elseif ($nueva !== $confirmar) {
            $msg = 'Las contraseñas nuevas no coinciden.';
            $msgTipo = 'error';
        } elseif (!password_verify($actual, $emp['Password'])) {
            $msg = 'La contraseña actual es incorrecta.';
            $msgTipo = 'error';
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $hashEsc = mysqli_real_escape_string($conect, $hash);
            mysqli_query($conect, "UPDATE empleado SET Password='$hashEsc' WHERE IDEmpleado='$idEmpleado'");
            $msg = 'Contraseña actualizada correctamente.';
            $msgTipo = 'ok';
        }
    }
}

// Stats rápidas para mostrar en perfil
$resStats = mysqli_query($conect, "SELECT 
    COUNT(*) AS Total,
    SUM(CASE WHEN Estado='aprobado'  THEN 1 ELSE 0 END) AS Aprobados,
    SUM(CASE WHEN Estado='pendiente' THEN 1 ELSE 0 END) AS Pendientes
    FROM viatico WHERE IDEmpleado='$idEmpleado'");
$stats = mysqli_fetch_assoc($resStats);

$inicial = strtoupper(substr($emp['Nombre'], 0, 1));
$fechaAlta = date('d/m/Y', strtotime($emp['FechaAlta']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Perfil — ViáticosApp</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; display: flex; height: 100vh; width: 100vw; overflow: hidden; }
.app { display: flex; width: 100%; height: 100%; }

/* ── SIDEBAR ── */
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
.user-avatar-sm { width: 36px; height: 36px; border-radius: 50%; background: #1db87a22; border: 2px solid #1db87a55; display: flex; align-items: center; justify-content: center; color: #1db87a; font-size: 16px; font-weight: 700; flex-shrink: 0; }
.user-name-s  { font-size: 13px; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-email-s { font-size: 11px; color: #8a95a8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.logout-btn { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8a95a8; cursor: pointer; padding: 6px 0; background: none; border: none; width: 100%; }
.logout-btn:hover { color: #e53e3e; }

/* ── MAIN ── */
.main { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
.main-inner { width: 100%; max-width: 820px; margin: 0 auto; padding: 28px 32px; overflow-y: auto; height: 100%; }
.main-inner::-webkit-scrollbar { width: 5px; }
.main-inner::-webkit-scrollbar-thumb { background: #dde1e7; border-radius: 3px; }

.page-header { margin-bottom: 22px; }
.main-title { font-size: 24px; font-weight: 700; color: #1a2232; }
.main-sub   { font-size: 13px; color: #8a95a8; margin-top: 3px; }

/* Flash */
.flash { display: flex; align-items: center; gap: 8px; padding: 11px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; font-weight: 500; }
.flash.ok    { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.flash.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* ── HERO CARD ── */
.hero-card { background: #1a2232; border-radius: 14px; padding: 28px; display: flex; align-items: center; gap: 22px; margin-bottom: 20px; position: relative; overflow: hidden; }
.hero-card::before { content: ''; position: absolute; right: -40px; top: -40px; width: 200px; height: 200px; background: #1db87a12; border-radius: 50%; }
.hero-card::after  { content: ''; position: absolute; right: 60px; bottom: -60px; width: 150px; height: 150px; background: #1db87a08; border-radius: 50%; }
.hero-avatar { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #1db87a, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 30px; font-weight: 700; flex-shrink: 0; z-index: 1; box-shadow: 0 4px 16px rgba(29,184,122,0.4); }
.hero-info { z-index: 1; flex: 1; }
.hero-name  { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.hero-email { font-size: 13px; color: #8a95a8; margin-bottom: 10px; }
.hero-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-empleado { background: #1db87a22; color: #1db87a; border: 1px solid #1db87a44; }
.badge-admin    { background: #fef3c744; color: #d97706; border: 1px solid #d9770644; }
.hero-meta { z-index: 1; text-align: right; }
.hero-meta-label { font-size: 11px; color: #8a95a8; margin-bottom: 4px; }
.hero-meta-val   { font-size: 13px; color: #c8d0e0; font-weight: 500; }

/* ── STATS MINI ── */
.stats-mini { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.stat-mini { background: #fff; border: 1px solid #e5e8ed; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; }
.stat-mini-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.icon-blue   { background: #eff6ff; color: #2563eb; }
.icon-green  { background: #d1fae5; color: #059669; }
.icon-amber  { background: #fef3c7; color: #d97706; }
.stat-mini-val   { font-size: 20px; font-weight: 700; color: #1a2232; line-height: 1; }
.stat-mini-label { font-size: 12px; color: #8a95a8; margin-top: 3px; }

/* ── CARDS ── */
.cards-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.card { background: #fff; border: 1px solid #e5e8ed; border-radius: 12px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 16px 20px; border-bottom: 1px solid #f0f2f5; }
.card-header i { font-size: 20px; color: #1db87a; }
.card-title { font-size: 14px; font-weight: 600; color: #1a2232; }
.card-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }

/* Fields */
.field { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 12px; font-weight: 500; color: #8a95a8; text-transform: uppercase; letter-spacing: 0.04em; }
.input-wrap { display: flex; align-items: center; border: 1px solid #dde1e7; border-radius: 8px; padding: 9px 12px; gap: 8px; transition: border-color 0.15s; }
.input-wrap:focus-within { border-color: #1db87a; }
.input-wrap i { font-size: 16px; color: #b0b8c8; }
.input-wrap input { border: none; outline: none; font-size: 13px; width: 100%; color: #1a2232; background: transparent; }

/* Password eye toggle */
.input-wrap .eye-btn { background: none; border: none; cursor: pointer; color: #b0b8c8; font-size: 16px; padding: 0; display: flex; align-items: center; }
.input-wrap .eye-btn:hover { color: #6b7280; }

.btn-save { display: flex; align-items: center; justify-content: center; gap: 7px; width: 100%; padding: 10px; border-radius: 8px; border: none; background: #1db87a; color: #fff; font-weight: 600; font-size: 13px; cursor: pointer; margin-top: 4px; transition: background 0.15s; }
.btn-save:hover { background: #17a06a; }

.hint { font-size: 11px; color: #b0b8c8; margin-top: -8px; }

/* Strength bar */
.strength-wrap { margin-top: -10px; }
.strength-bar { height: 4px; border-radius: 2px; background: #e5e8ed; margin-bottom: 4px; overflow: hidden; }
.strength-fill { height: 100%; width: 0%; border-radius: 2px; transition: width 0.3s, background 0.3s; }
.strength-label { font-size: 11px; color: #8a95a8; }
</style>
</head>
<body>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-icon"><i class="ti ti-circle-dollar-sign"></i></div>
      <div class="sidebar-brand">ViáticosApp<span>Sistema de gastos</span></div>
    </div>
    <nav class="nav">
      <a href="../principal.php"  class="nav-item"><i class="ti ti-briefcase"></i><span>Mis Viáticos</span></a>
      <a href="formulario.php" class="nav-item"><i class="ti ti-circle-plus"></i><span>Nuevo Viático</span></a>
      <a href="vista_usuarios/perfil.php"   class="nav-item active"><i class="ti ti-user"></i><span>Mi Perfil</span></a>
    </nav>
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar-sm"><?php echo $inicial; ?></div>
        <div style="flex:1;min-width:0;">
          <div class="user-name-s"><?php echo htmlspecialchars($emp['Nombre']); ?></div>
          <div class="user-email-s"><?php echo htmlspecialchars($emp['Email']); ?></div>
        </div>
      </div>
      <button class="logout-btn" onclick="window.location.href='../verificardatos/terminarseccion.php'">
        <i class="ti ti-logout"></i><span>Cerrar Sesión</span>
      </button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <div class="main-inner">

      <div class="page-header">
        <h1 class="main-title">Mi Perfil</h1>
        <p class="main-sub">Consulta y actualiza tu información personal</p>
      </div>

      <?php if ($msg): ?>
      <div class="flash <?php echo $msgTipo; ?>">
        <i class="ti ti-<?php echo $msgTipo === 'ok' ? 'circle-check' : 'alert-circle'; ?>"></i>
        <?php echo htmlspecialchars($msg); ?>
      </div>
      <?php endif; ?>

      <!-- HERO -->
      <div class="hero-card">
        <div class="hero-avatar"><?php echo $inicial; ?></div>
        <div class="hero-info">
          <div class="hero-name"><?php echo htmlspecialchars($emp['Nombre']); ?></div>
          <div class="hero-email"><?php echo htmlspecialchars($emp['Email']); ?></div>
          <span class="hero-badge <?php echo $emp['Rol'] === 'admin' ? 'badge-admin' : 'badge-empleado'; ?>">
            <i class="ti ti-<?php echo $emp['Rol'] === 'admin' ? 'shield' : 'user'; ?>"></i>
            <?php echo ucfirst($emp['Rol']); ?>
          </span>
        </div>
        <div class="hero-meta">
          <div class="hero-meta-label">Miembro desde</div>
          <div class="hero-meta-val"><?php echo $fechaAlta; ?></div>
        </div>
      </div>

      <!-- STATS MINI -->
      <div class="stats-mini">
        <div class="stat-mini">
          <div class="stat-mini-icon icon-blue"><i class="ti ti-briefcase"></i></div>
          <div>
            <div class="stat-mini-val"><?php echo (int)$stats['Total']; ?></div>
            <div class="stat-mini-label">Viáticos totales</div>
          </div>
        </div>
        <div class="stat-mini">
          <div class="stat-mini-icon icon-green"><i class="ti ti-checks"></i></div>
          <div>
            <div class="stat-mini-val"><?php echo (int)$stats['Aprobados']; ?></div>
            <div class="stat-mini-label">Aprobados</div>
          </div>
        </div>
        <div class="stat-mini">
          <div class="stat-mini-icon icon-amber"><i class="ti ti-clock"></i></div>
          <div>
            <div class="stat-mini-val"><?php echo (int)$stats['Pendientes']; ?></div>
            <div class="stat-mini-label">Pendientes</div>
          </div>
        </div>
      </div>

      <!-- FORMULARIOS -->
      <div class="cards-row">

        <!-- Datos personales -->
        <div class="card">
          <div class="card-header">
            <i class="ti ti-user-edit"></i>
            <span class="card-title">Datos personales</span>
          </div>
          <div class="card-body">
            <form method="POST">
              <input type="hidden" name="accion" value="datos">
              <div style="display:flex;flex-direction:column;gap:14px;">
                <div class="field">
                  <label class="field-label">Nombre completo</label>
                  <div class="input-wrap">
                    <i class="ti ti-user"></i>
                    <input type="text" name="Nombre" value="<?php echo htmlspecialchars($emp['Nombre']); ?>" required>
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Correo electrónico</label>
                  <div class="input-wrap">
                    <i class="ti ti-mail"></i>
                    <input type="email" name="Email" value="<?php echo htmlspecialchars($emp['Email']); ?>" required>
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Rol</label>
                  <div class="input-wrap" style="background:#f8fafb;">
                    <i class="ti ti-shield"></i>
                    <input type="text" value="<?php echo ucfirst($emp['Rol']); ?>" disabled style="color:#8a95a8;">
                  </div>
                </div>
                <button type="submit" class="btn-save"><i class="ti ti-device-floppy"></i> Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Cambiar contraseña -->
        <div class="card">
          <div class="card-header">
            <i class="ti ti-lock"></i>
            <span class="card-title">Cambiar contraseña</span>
          </div>
          <div class="card-body">
            <form method="POST">
              <input type="hidden" name="accion" value="password">
              <div style="display:flex;flex-direction:column;gap:14px;">
                <div class="field">
                  <label class="field-label">Contraseña actual</label>
                  <div class="input-wrap">
                    <i class="ti ti-lock"></i>
                    <input type="password" name="PassActual" id="passActual" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="togglePass('passActual', this)"><i class="ti ti-eye"></i></button>
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Nueva contraseña</label>
                  <div class="input-wrap">
                    <i class="ti ti-lock-open"></i>
                    <input type="password" name="PassNueva" id="passNueva" placeholder="Mín. 6 caracteres" required oninput="medirFuerza(this.value)">
                    <button type="button" class="eye-btn" onclick="togglePass('passNueva', this)"><i class="ti ti-eye"></i></button>
                  </div>
                  <div class="strength-wrap">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <span class="strength-label" id="strengthLabel"></span>
                  </div>
                </div>
                <div class="field">
                  <label class="field-label">Confirmar nueva contraseña</label>
                  <div class="input-wrap">
                    <i class="ti ti-lock-check"></i>
                    <input type="password" name="PassConfirmar" id="passConfirmar" placeholder="Repite la contraseña" required>
                    <button type="button" class="eye-btn" onclick="togglePass('passConfirmar', this)"><i class="ti ti-eye"></i></button>
                  </div>
                </div>
                <p class="hint">Mínimo 6 caracteres. Usa letras, números y símbolos para mayor seguridad.</p>
                <button type="submit" class="btn-save"><i class="ti ti-key"></i> Actualizar contraseña</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<script>
function togglePass(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.innerHTML = isText ? '<i class="ti ti-eye"></i>' : '<i class="ti ti-eye-off"></i>';
}

function medirFuerza(val) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const niveles = [
    { pct: '0%',   color: '#e5e8ed', txt: '' },
    { pct: '25%',  color: '#ef4444', txt: 'Muy débil' },
    { pct: '50%',  color: '#f97316', txt: 'Débil' },
    { pct: '70%',  color: '#eab308', txt: 'Regular' },
    { pct: '85%',  color: '#22c55e', txt: 'Fuerte' },
    { pct: '100%', color: '#10b981', txt: 'Muy fuerte' },
  ];
  const n = niveles[Math.min(score, 5)];
  fill.style.width = n.pct;
  fill.style.background = n.color;
  label.textContent = n.txt;
  label.style.color = n.color;
}
</script>
</body>
</html>