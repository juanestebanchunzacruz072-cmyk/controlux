<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['usuario']['id_rol'] == '1') {
        header("Location: ../admin/dashboard_admin.php");
        exit;
    }
    header("Location: ../../public/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Iniciar Sesión</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style_loginyregister.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="left-panel">
        <a href="../../public/index.php" class="back-link">← Volver a inicio</a>
        <h1 class="brand-title">JC URBAN</h1>
        <p class="brand-subtitle">Exclusividad & Lujo Urbano</p>
    </div>

    <div class="right-panel">
        <div class="form-wrapper">
            <h2 class="form-title">Iniciar Sesión</h2>
            <p class="form-subtitle">Accede a nuestro catálogo de pura exclusividad.</p>

            <form action="../../controllers/Auth/authController.php" method="POST">
                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" placeholder="ejemplo@jcurban.com" required>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <a href="#" class="forgot-pass">¿Olvidaste tu contraseña?</a>
                    <div style="position: relative;">
                        <input type="password" id="passwordField" name="password" placeholder="••••••••" required style="padding-right: 40px; width: 100%;">
                        <i class="bi bi-eye-slash" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.2rem;"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Ingresar al Sistema</button>
                
            </form>

            <div class="divider"><span>o</span></div>

            <a href="../../controllers/Auth/googleAuth.php" class="social-btn" style="display: flex; align-items: center; justify-content: center; text-decoration: none; gap: 10px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width: 20px; height: 20px;">
                Continuar con Google
            </a>

            <p class="switch-form-text">
                ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
            </p>
        </div>
    </div>
    
    <?php
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<script>
            Swal.fire({
                icon: '{$alert['icon']}',
                title: '{$alert['title']}',
                text: '{$alert['text']}',
                confirmButtonColor: '#D4AF37',
                background: '#111',
                color: '#fff',
                heightAuto: false
            });
        </script>";
        unset($_SESSION['alert']);
    }
    ?>
<script>
// Mostrar/Ocultar Contraseña
document.getElementById('togglePassword').addEventListener('click', function () {
    const password = document.getElementById('passwordField');
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
</script>
</body>
</html>