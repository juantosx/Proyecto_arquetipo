<?php
// Formulario para actualizar los datos de un producto existente.
require_once __DIR__ . '/../includes/auth.php';
exigirRol('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: productos.php');
    exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        $errores[] = 'La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.';
    } else {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = str_replace(',', '.', trim($_POST['precio'] ?? ''));
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $imagen = $producto['imagen'];

    if ($nombre === '') $errores[] = 'El nombre del producto es obligatorio.';
    if ($precio === '' || !is_numeric($precio) || (float)$precio < 0) $errores[] = 'El precio debe ser un número válido mayor o igual a 0.';

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $archivo = $_FILES['imagen'];
        $permitidas = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tipo = mime_content_type($archivo['tmp_name']);
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'No fue posible cargar la imagen.';
        } elseif (!isset($permitidas[$tipo])) {
            $errores[] = 'La imagen debe ser JPG, PNG o WEBP.';
        } elseif ($archivo['size'] > 4 * 1024 * 1024) {
            $errores[] = 'La imagen no puede superar los 4 MB.';
        } else {
            $directorio = __DIR__ . '/../uploads/productos';
            if (!is_dir($directorio)) mkdir($directorio, 0775, true);
            $nombreArchivo = uniqid('producto_', true) . '.' . $permitidas[$tipo];
            if (move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombreArchivo)) {
                $imagenNueva = 'uploads/productos/' . $nombreArchivo;
                if (!empty($imagen)) {
                    $rutaAnterior = __DIR__ . '/../' . ltrim($imagen, '/');
                    if (is_file($rutaAnterior)) @unlink($rutaAnterior);
                }
                $imagen = $imagenNueva;
            } else {
                $errores[] = 'No fue posible guardar la nueva imagen.';
            }
        }
    }

    if (!$errores) {
        $stmt = $pdo->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, disponible = ? WHERE id = ?');
        $stmt->execute([$nombre, $descripcion, (float)$precio, $imagen, $disponible, $id]);
        header('Location: productos.php');
        exit;
    }

    $producto['nombre'] = $nombre;
    $producto['descripcion'] = $descripcion;
    $producto['precio'] = $precio;
    $producto['disponible'] = $disponible;
    $producto['imagen'] = $imagen;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto | THABATHA’S</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header class="site-header"><strong>THABATHA’S | Administración</strong><nav><a href="productos.php">Volver a productos</a><a href="../logout.php">Salir</a></nav></header>
<main class="container narrow-container">
    <div class="page-heading"><p class="eyebrow">CATÁLOGO</p><h1>Editar producto</h1><p>Actualiza los datos que se mostrarán en el catálogo.</p></div>
    <?php if ($errores): ?><div class="alert error"><ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="admin-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">
        <label>Nombre del producto<input type="text" name="nombre" maxlength="120" required value="<?= e($producto['nombre']) ?>"></label>
        <label>Descripción<textarea name="descripcion" rows="5"><?= e($producto['descripcion'] ?? '') ?></textarea></label>
        <label>Precio<input type="text" name="precio" inputmode="decimal" required value="<?= e($producto['precio']) ?>"></label>
        <label>Imagen del producto<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><small>Si no seleccionas una nueva imagen, se conservará la actual.</small></label>
        <?php if (!empty($producto['imagen'])): ?><div class="current-image"><img src="../<?= e($producto['imagen']) ?>" alt="<?= e($producto['nombre']) ?>"><span>Imagen actual</span></div><?php endif; ?>
        <label class="check-row"><input type="checkbox" name="disponible" value="1" <?= $producto['disponible'] ? 'checked' : '' ?>> Producto disponible</label>
        <div class="form-actions"><a class="button secondary" href="productos.php">Cancelar</a><button class="button" type="submit">Guardar cambios</button></div>
    </form>
</main>
</body>
</html>
