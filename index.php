<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merca Inc. - Control de Viáticos</title>
    <style>
        /* Estilos base inspirados en diseños minimalistas modernos */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
        }

        body {
            background: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #2d3748;
        }

        .login-container {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo-area {
            margin-bottom: 2rem;
        }

        .logo-area h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            letter-spacing: -0.5px;
        }

        .logo-area p {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        /* Formulario y contenedores de campos */
        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3182ce;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }

        /* Botón Primario de Inicio de Sesión */
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: #1a202c;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            background: #2d3748;
        }

        /* Separador visual elegante */
        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #a0aec0;
            font-size: 0.8rem;
        }

        .separator::before, .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .separator:not(:empty)::before { margin-right: .5em; }
        .separator:not(:empty)::after { margin-left: .5em; }

        /* Botón Alternativo de Google */
        .btn-google {
            width: 100%;
            padding: 0.75rem;
            background: #ffffff;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-google:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }

        .btn-google svg {
            width: 18px;
            height: 18px;
        }

        /* Mensajes de error contextuales */
        .error-message {
            background: #fff5f5;
            color: #c53030;
            font-size: 0.85rem;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            border: 1px solid #fed7d7;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo-area">
            <h1>SYSTEM VIATIC</h1>
            <p>Sistema de Comprobación de Viáticos</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                    if ($_GET['error'] == 'vacio') echo "Por favor, llena todos los campos.";
                    if ($_GET['error'] == 'incorrecto') echo "El correo o la contraseña son incorrectos.";
                    if ($_GET['error'] == 'acceso') echo "Inicia sesión para acceder al sistema.";
                ?>
            </div>
        <?php endif; ?>

        <form action="verificardatos/verificar_seccion.php" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="ejemplo@mercainc.com" required>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Iniciar Sesión</button>
        </form>

        <div class="separator">O continuar con</div>
        <div class="google-btn-container">
                <div id="g_id_onload"
                     data-client_id="875467382216-1lps7kic5lfbl2oh98cr970nqjlm3lnv.apps.googleusercontent.com"
                     data-callback="handleCredentialResponse"
                     data-auto_prompt="false">
                </div>

                <div class="g_id_signin" 
                     data-type="standard" 
                     data-size="large" 
                     data-theme="outline" 
                     data-text="continue_with" 
                     data-shape="rectangular" 
                     data-logo_alignment="left"
                     data-width="320"> </div>
            </div>

        <button type="button" class="btn-google" onclick="window.location.href='verificardatos/login_google.php'">
            <svg viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continuar con Google
        </button>
        <div style="text-align: center; margin-top: 1rem;">
    <p style="font-size: 0.85rem; color: #718096;">
        ¿No tienes una cuenta? 
        <a href="registro.php" style="color: #3182ce; font-weight: 600; text-decoration: none;">Regístrate aquí</a>
    </p>
</div>


        <script>
      /**
       * Función que recibe la respuesta de Google tras el éxito
       */
      function handleCredentialResponse(response) {
          // Decodificamos el JWT para obtener los datos del usuario
          const responsePayload = parseJwt(response.credential);

          // Redirigimos a Verificar_seccion.php enviando Nombre, Email y FOTO
          window.location.href = "Verificardatos/Verificar_seccion.php?fullname=" + 
                                 encodeURIComponent(responsePayload.name) + 
                                 "&email=" + encodeURIComponent(responsePayload.email) +
                                 "&picture=" + encodeURIComponent(responsePayload.picture) +
                                 "&google_auth=true";
      }

      /**
       * Función para decodificar el token JWT de Google
       */
      function parseJwt(token) {
          var base64Url = token.split('.')[1];
          var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
          var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
              return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
          }).join(''));

          return JSON.parse(jsonPayload);
      }
    </script>
    </div>

</body>
</html>