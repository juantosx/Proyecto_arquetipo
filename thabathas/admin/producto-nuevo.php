<?php
// Formulario para registrar un producto nuevo en el catálogo.
require_once __DIR__ . '/../includes/auth.php';
exigirRol('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        $errores[] = 'La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.';
    } else {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = str_replace(',', '.', trim($_POST['precio'] ?? ''));
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if ($nombre === '') $errores[] = 'El nombre del producto es obligatorio.';
    if ($precio === '' || !is_numeric($precio) || (float)$precio < 0) $errores[] = 'El precio debe ser un número válido mayor o igual a 0.';

    $imagen = null;
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
                $imagen = 'uploads/productos/' . $nombreArchivo;
            } else {
                $errores[] = 'No fue posible guardar la imagen.';
            }
        }
    }

    if (!$errores) {
        $stmt = $pdo->prepare('INSERT INTO productos (nombre, descripcion, precio, imagen, disponible) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nombre, $descripcion, (float)$precio, $imagen, $disponible]);
        header('Location: productos.php');
        exit;
    }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo producto | THABATHA’S</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header class="site-header"><strong>THABATHA’S | Administración</strong><nav><a href="productos.php">Volver a productos</a><a href="../logout.php">Salir</a></nav></header>
<main class="container narrow-container">
    <div class="page-heading"><p class="eyebrow">CATÁLOGO</p><h1>Nuevo producto</h1><p>Registra un nuevo producto para mostrarlo en el catálogo.</p></div>
    <?php if ($errores): ?><div class="alert error"><ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="admin-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <label>Nombre del producto<input type="text" name="nombre" maxlength="120" required value="<?= e($_POST['nombre'] ?? '') ?>"></label>
        <label>Descripción<textarea name="descripcion" rows="5"><?= e($_POST['descripcion'] ?? '') ?></textarea></label>
        <label>Precio<input type="text" name="precio" inputmode="decimal" placeholder="0.00" required value="<?= e($_POST['precio'] ?? '') ?>"></label>
        <label>Imagen del producto<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><small>Formatos: JPG, PNG o WEBP. Máximo 4 MB.</small></label>
        <label class="check-row"><input type="checkbox" name="disponible" value="1" <?= isset($_POST['disponible']) || $_SERVER['REQUEST_METHOD'] === 'GET' ? 'checked' : '' ?>> Producto disponible</label>
        <div class="form-actions"><a class="button secondary" href="productos.php">Cancelar</a><button class="button" type="submit">Guardar producto</button></div>
    </form>
</main>
</body>
</html>
