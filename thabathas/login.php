<?php
// Valida las credenciales e inicia una sesión con el rol asignado al usuario.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';

if (usuarioAutenticado()) {
    header('Location: ' . ($_SESSION['usuario']['rol'] === 'admin' ? '/thabathas/admin/index.php' : '/thabathas/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.';
    } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        unset($usuario['password']);
        $_SESSION['usuario'] = $usuario;
        header('Location: ' . ($usuario['rol'] === 'admin' ? '/thabathas/admin/index.php' : '/thabathas/index.php'));
        exit;
    }
    $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Iniciar sesión | THABATHA’S</title><link rel="stylesheet" href="css/styles.css"></head>
<body>
<main class="auth-page">
    <section class="auth-card">
        <h1>THABATHA’S</h1>
        <p>Acceso al sistema</p>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" required>
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required>
            <button type="submit">Iniciar sesión</button>
        </form>
        <a href="index.php">Volver al inicio</a>
    </section>
</main>
</body>
</html>
