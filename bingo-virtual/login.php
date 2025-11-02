<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/funciones.php';

$db = new Database();
$error = '';
$mensaje = '';

// Si ya está autenticado, redirigir según rol
if (estaAutenticado()) {
    if (esAdmin()) {
        redirigir('admin/index.php');
    } else {
        redirigir('jugador/index.php');
    }
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido';
    } else {
        $email = limpiar($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error = 'Por favor complete todos los campos';
        } else {
            $sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo' LIMIT 1";
            $usuario = $db->fetchOne($sql, ['email' => $email]);

            if ($usuario && verificarPassword($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['whatsapp'] = $usuario['whatsapp'];

                if ($usuario['rol'] === 'admin') {
                    redirigir('admin/index.php');
                } else {
                    redirigir('jugador/index.php');
                }
            } else {
                $error = 'Credenciales incorrectas';
            }
        }
    }
}

// Procesar registro rápido de jugador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido';
    } else {
        $nombre = limpiar($_POST['nombre']);
        $whatsapp = limpiar($_POST['whatsapp']);
        $email = limpiar($_POST['email_reg']);
        $password = $_POST['password_reg'];

        if (empty($nombre) || empty($whatsapp) || empty($email) || empty($password)) {
            $error = 'Por favor complete todos los campos';
        } else {
            // Verificar si el email ya existe
            $sqlCheck = "SELECT id FROM usuarios WHERE email = :email LIMIT 1";
            $existe = $db->fetchOne($sqlCheck, ['email' => $email]);

            if ($existe) {
                $error = 'Este correo ya está registrado';
            } else {
                $passwordHash = hashPassword($password);
                $sql = "INSERT INTO usuarios (nombre, whatsapp, email, password, rol, estado)
                        VALUES (:nombre, :whatsapp, :email, :password, 'jugador', 'activo')";

                if ($db->query($sql, [
                    'nombre' => $nombre,
                    'whatsapp' => $whatsapp,
                    'email' => $email,
                    'password' => $passwordHash
                ])) {
                    $mensaje = 'Registro exitoso. Ahora puede iniciar sesión.';
                } else {
                    $error = 'Error al registrar usuario';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>🎰 BINGO VIRTUAL</h1>
            <p>Juega y gana en línea</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-button active" onclick="mostrarTab('login')">Iniciar Sesión</button>
            <button class="tab-button" onclick="mostrarTab('registro')">Registrarse</button>
        </div>

        <!-- Formulario de Login -->
        <div id="login-form" class="tab-content active">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-block">
                    Ingresar
                </button>

                <div class="login-info">
                    <p><strong>Usuario Admin:</strong> admin@bingo.com</p>
                    <p><strong>Contraseña:</strong> password</p>
                </div>
            </form>
        </div>

        <!-- Formulario de Registro -->
        <div id="registro-form" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="whatsapp">WhatsApp</label>
                    <input type="tel" id="whatsapp" name="whatsapp" placeholder="3001234567" required>
                </div>

                <div class="form-group">
                    <label for="email_reg">Correo Electrónico</label>
                    <input type="email" id="email_reg" name="email_reg" required>
                </div>

                <div class="form-group">
                    <label for="password_reg">Contraseña</label>
                    <input type="password" id="password_reg" name="password_reg" required minlength="6">
                </div>

                <button type="submit" name="registro" class="btn btn-primary btn-block">
                    Crear Cuenta
                </button>
            </form>
        </div>
    </div>

    <script>
        function mostrarTab(tab) {
            // Ocultar todos los contenidos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Mostrar el tab seleccionado
            if (tab === 'login') {
                document.getElementById('login-form').classList.add('active');
                document.querySelectorAll('.tab-button')[0].classList.add('active');
            } else {
                document.getElementById('registro-form').classList.add('active');
                document.querySelectorAll('.tab-button')[1].classList.add('active');
            }
        }
    </script>
</body>
</html>
