<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merca Inc. - Registro de Personal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; color: #2d3748; }
        .register-container { background: #ffffff; padding: 2.5rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); width: 100%; max-width: 400px; text-align: center; }
        .logo-area { margin-bottom: 2rem; }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: #1a202c; }
        .logo-area p { font-size: 0.9rem; color: #718096; margin-top: 0.25rem; }
        .form-group { margin-bottom: 1.25rem; text-align: left; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: #4a5568; }
        .form-group input { width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; background-color: #f8fafc; }
        .form-group input:focus { outline: none; border-color: #3182ce; background-color: #ffffff; }
        .btn-submit { width: 100%; padding: 0.75rem; background: #1a202c; color: #ffffff; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: 0.5rem; }
        .btn-submit:hover { background: #2d3748; }
        .error-message { background: #fff5f5; color: #c53030; font-size: 0.85rem; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.25rem; border: 1px solid #fed7d7; text-align: left; }
    </style>
</head>
<body>

    <div class="register-container">
        <div class="logo-area">
            <h1>Registro</h1>
            <p>Sistema de Comprobación de Viáticos</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                    if ($_GET['error'] == 'vacio') echo "Por favor, llena todos los campos.";
                    if ($_GET['error'] == 'existe') echo "Este correo ya se encuentra registrado.";
                    if ($_GET['error'] == 'db') echo "Error al registrar. Inténtalo de nuevo.";
                ?>
            </div>
        <?php endif; ?>

        <form action="verificardatos/insertar_usuario.php" method="POST">
            <div class="form-group">
                <label for="Nombre">Nombre Completo</label>
                <input type="text" id="Nombre" name="Nombre" placeholder="Juan " required>
            </div>

            <div class="form-group">
                <label for="Email">Correo Corporativo</label>
                <input type="Email" id="Email" name="Email" placeholder="ejemplo@mercainc.com" required>
            </div>

            <div class="form-group">
                <label for="Password">Contraseña</label>
                <input type="Password" id="Password" name="Password" placeholder="Crea una contraseña segura" required>
            </div>

            <button type="submit" class="btn-submit">Crear Cuenta</button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="index.php" style="font-size: 0.85rem; color: #718096; text-decoration: none;">Volver al Inicio de Sesión</a>
        </div>
    </div>

</body>
</html>