<?php
// Muestra la información del producto seleccionado en el catálogo.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: catalogo.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ? AND disponible = 1 LIMIT 1');
$stmt->execute([$id]);
$producto = $stmt->fetch();
if (!$producto) { header('Location: catalogo.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        header('Location: producto.php?id=' . (int)$producto['id']);
        exit;
    }
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
    agregarAlCarrito((int)$producto['id'], $cantidad);
    header('Location: carrito.php?agregado=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($producto['nombre']) ?> | THABATHA’S</title><link rel="stylesheet" href="css/styles.css"></head>
<body>
<header class="site-header"><a class="brand" href="index.php">THABATHA’S</a><nav><a href="index.php">Inicio</a><a href="catalogo.php">Catálogo</a><a href="carrito.php">Carrito <span class="cart-count"><?= carritoCantidad() ?></span></a><?php if (usuarioAutenticado()): ?><a href="logout.php">Salir</a><?php else: ?><a href="login.php">Iniciar sesión</a><?php endif; ?></nav></header>
<main class="container">
  <a class="back-link" href="catalogo.php">← Volver al catálogo</a>
  <section class="product-detail">
    <div class="product-image large"><?php if (!empty($producto['imagen'])): ?><img src="<?= e($producto['imagen']) ?>" alt="<?= e($producto['nombre']) ?>"><?php else: ?><span>THABATHA’S</span><?php endif; ?></div>
    <div class="detail-content">
      <span class="availability">Disponible</span>
      <h1><?= e($producto['nombre']) ?></h1>
      <p><?= e($producto['descripcion']) ?></p>
      <div class="detail-price">$<?= number_format((float)$producto['precio'], 2) ?></div>
      <form method="post" class="add-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><label for="cantidad">Cantidad</label><input id="cantidad" name="cantidad" type="number" min="1" value="1"><button class="button" type="submit">Agregar al carrito</button></form>
    </div>
  </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body></html>
