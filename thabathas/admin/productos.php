<?php
// Listado y acciones de administración sobre los productos del catálogo.
require_once __DIR__ . '/../includes/auth.php';
exigirRol('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['admin_mensaje'] = 'La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.';
        header('Location: productos.php'); exit;
    }
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($accion === 'eliminar' && $id > 0) {
        $stmt = $pdo->prepare('SELECT imagen FROM productos WHERE id = ?');
        $stmt->execute([$id]);
        $producto = $stmt->fetch();

        if ($producto) {
            $pdo->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
            if (!empty($producto['imagen'])) {
                $ruta = __DIR__ . '/../' . ltrim($producto['imagen'], '/');
                if (is_file($ruta)) @unlink($ruta);
            }
            $_SESSION['admin_mensaje'] = 'Producto eliminado correctamente.';
        }
        header('Location: productos.php');
        exit;
    }

    if ($accion === 'cambiar_disponibilidad' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE productos SET disponible = NOT disponible WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['admin_mensaje'] = 'Disponibilidad actualizada.';
        header('Location: productos.php');
        exit;
    }
}

$productos = $pdo->query('SELECT id, nombre, descripcion, precio, imagen, disponible FROM productos ORDER BY nombre')->fetchAll();
$mensaje = $_SESSION['admin_mensaje'] ?? '';
unset($_SESSION['admin_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | THABATHA’S</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header class="site-header">
    <strong>THABATHA’S | Administración</strong>
    <nav>
        <a href="index.php">Panel</a>
        <a href="productos.php" class="active">Productos</a>
        <a href="pedidos.php">Pedidos</a>
        <a href="../index.php">Ver sitio</a>
        <a href="../logout.php">Salir</a>
    </nav>
</header>

<main class="container">
    <div class="admin-heading">
        <div>
            <p class="eyebrow">GESTIÓN DEL CATÁLOGO</p>
            <h1>Productos</h1>
            <p class="muted">Administra nombres, precios, imágenes y disponibilidad.</p>
        </div>
        <a class="button" href="producto-nuevo.php">+ Nuevo producto</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert success"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Disponibilidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td>
                        <div class="admin-thumb">
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="../<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>">
                            <?php else: ?>
                                <span>Sin foto</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <strong><?= e($p['nombre']) ?></strong>
                        <small><?= e($p['descripcion'] ?? '') ?></small>
                    </td>
                    <td>$<?= number_format((float)$p['precio'], 2, ',', '.') ?></td>
                    <td>
                        <span class="status <?= $p['disponible'] ? 'available' : 'unavailable' ?>">
                            <?= $p['disponible'] ? 'Disponible' : 'No disponible' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a class="button small" href="producto-editar.php?id=<?= (int)$p['id'] ?>">Editar</a>
                            <form method="post" onsubmit="return confirm('¿Cambiar la disponibilidad de este producto?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="accion" value="cambiar_disponibilidad">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="button small secondary" type="submit">Cambiar estado</button>
                            </form>
                            <form method="post" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="button small danger" type="submit">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$productos): ?>
                <tr><td colspan="5">No hay productos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
