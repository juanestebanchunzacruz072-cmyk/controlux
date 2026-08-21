<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Crear Cuenta</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style_loginyregister.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="left-panel">
        <a href="login.php" class="back-link">← Volver</a>
        <h1 class="brand-title">JC URBAN</h1>
        <p class="brand-subtitle">Exclusividad & Lujo Urbano</p>
    </div>

    <div class="right-panel">
        <div class="form-wrapper">
            <h2 class="form-title">Crear Cuenta</h2>
            <p class="form-subtitle">Regístrate para acceder a nuestro catálogo.</p>

            <form id="registerForm" action="../../controllers/Auth/registerController.php" method="POST">
                
                <div class="input-row">
                    <div class="input-group">
                        <label>Nombres</label>
                        <input type="text" name="nombre" placeholder="Tu nombre" required>
                    </div>
                    <div class="input-group">
                        <label>Apellidos</label>
                        <input type="text" name="apellido" placeholder="Tu apellido" required>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" placeholder="ejemplo@jcurban.com" required>
                    </div>
                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" placeholder="Tu celular" required>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label>Cédula de Ciudadanía</label>
                        <input type="text" name="cedula" placeholder="Número de documento" required>
                    </div>
                    <div class="input-group">
                        <label>Dirección de Envío</label>
                        <input type="text" name="direccion" placeholder="Ej. Calle 123 #45-67" required>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label>Barrio</label>
                        <input type="text" name="barrio" placeholder="Tu barrio" required>
                    </div>
                    <div class="input-group">
                        <label>Ciudad</label>
                        <input type="text" name="ciudad" placeholder="Tu ciudad" required>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label>Contraseña</label>
                        <div style="position: relative;">
                            <input type="password" id="passwordField1" name="password" placeholder="••••••••" required style="padding-right: 40px; width: 100%;">
                            <i class="bi bi-eye-slash toggle-password" data-target="passwordField1" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Confirmar Contraseña</label>
                        <div style="position: relative;">
                            <input type="password" id="passwordField2" name="confirm_password" placeholder="••••••••" required style="padding-right: 40px; width: 100%;">
                            <i class="bi bi-eye-slash toggle-password" data-target="passwordField2" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Crear Cuenta</button>
            </form>

            <div class="divider"><span>o</span></div>

            <a href="../../controllers/Auth/googleAuth.php" class="social-btn" style="display: flex; align-items: center; justify-content: center; text-decoration: none; gap: 10px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width: 20px; height: 20px;">
                Continuar con Google
            </a>

            <p class="switch-form-text">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </div>
    </div>

    <!-- Script y Estilos de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Evitar que el formulario redirija la página
            
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'Procesando...';
            btnSubmit.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;

                Swal.fire({
                    icon: data.status,
                    title: data.title,
                    text: data.message,
                    background: 'transparent', 
                    color: '#fff',
                    backdrop: `rgba(0, 0, 0, 0.6)`,
                    heightAuto: false, // <-- Esto evita que rompa el layout
                    customClass: {
                        popup: 'glass-alert',
                        title: 'alert-title',
                        confirmButton: 'btn-premium-primary'
                    },
                    buttonsStyling: false
                }).then(() => {
                    if (data.status === 'success' && data.redirect) {
                        window.location.href = data.redirect;
                    }
                });
            })
            .catch(error => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo conectar con el servidor.',
                    background: 'transparent',
                    color: '#fff',
                    backdrop: `rgba(0, 0, 0, 0.6)`,
                    customClass: {
                        popup: 'glass-alert',
                        title: 'alert-title',
                        confirmButton: 'btn-premium-primary'
                    },
                    buttonsStyling: false
                });
            });
        });
        
        // Mostrar/Ocultar Contraseñas
        document.querySelectorAll('.toggle-password').forEach(function(icon) {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const password = document.getElementById(targetId);
                
                if (password.type === 'password') {
                    password.type = 'text';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                } else {
                    password.type = 'password';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                }
            });
        });
    </script>
</body>
</html>
