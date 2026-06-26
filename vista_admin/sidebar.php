<?php
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="logo-icon"><i class="ti ti-shield-check"></i></div>
    <div class="sidebar-brand">ViáticosApp<span>Panel Administrador</span></div>
    <i class="ti ti-chevrons-left sidebar-toggle" id="sidebarToggle"></i>
  </div>
  <nav class="nav">
    <a href="dashboard.php"   class="nav-item <?php echo $paginaActual==='dashboard.php'   ?'active':''; ?>"><i class="ti ti-layout-dashboard"></i><span>Dashboard</span></a>
    <a href="empledos.php"   class="nav-item <?php echo $paginaActual==='empleados.php'||$paginaActual==='empledos.php'  ?'active':''; ?>"><i class="ti ti-users"></i><span>Empleados</span></a>
    <a href="viaticos.php"    class="nav-item <?php echo $paginaActual==='viaticos.php'||$paginaActual==='ver_viatico.php'||$paginaActual==='ver_viaticos.php' ?'active':''; ?>"><i class="ti ti-briefcase"></i><span>Viáticos</span></a>
    <a href="reporte.php"    class="nav-item <?php echo $paginaActual==='reportes.php'    ?'active':''; ?>"><i class="ti ti-chart-bar"></i><span>Reportes</span></a>
    <a href="perfil_admin.php" class="nav-item <?php echo $paginaActual==='perfil_admin.php' ?'active':''; ?>"><i class="ti ti-user-circle"></i><span>Mi Perfil</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nombre'],0,1)); ?></div>
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
