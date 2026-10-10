<?php
// Permite revisar y actualizar los productos añadidos antes de enviar una solicitud.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        header('Location: carrito.php'); exit;
    }
    if (isset($_POST['actualizar'])) {
        actualizarCarrito($_POST['cantidades'] ?? []);
    } elseif (isset($_POST['eliminar'])) {
        eliminarDelCarrito((int)$_POST['eliminar']);
    }
    header('Location: carrito.php'); exit;
}

$carrito = $_SESSION['carrito'] ?? [];
$items = [];
$total = 0;
if ($carrito) {
    $ids = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id IN ($placeholders) AND disponible = 1");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $cantidad = (int)$carrito[$p['id']];
        $subtotal = (float)$p['precio'] * $cantidad;
        $items[] = ['producto'=>$p, 'cantidad'=>$cantidad, 'subtotal'=>$subtotal];
        $total += $subtotal;
    }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Carrito | THABATHA’S</title><link rel="stylesheet" href="css/styles.css"></head><body>
<header class="site-header"><a class="brand" href="index.php">THABATHA’S</a><nav><a href="index.php">Inicio</a><a href="catalogo.php">Catálogo</a><a class="active" href="carrito.php">Carrito <span class="cart-count"><?= carritoCantidad() ?></span></a><?php if (usuarioAutenticado()): ?><a href="logout.php">Salir</a><?php else: ?><a href="login.php">Iniciar sesión</a><?php endif; ?></nav></header>
<main class="container"><section class="page-heading"><p class="eyebrow">Tu selección</p><h1>Carrito de compras</h1><p>Revisa las cantidades antes de continuar con tu solicitud.</p></section>
<?php if (!$items): ?><div class="empty-state"><h2>Tu carrito está vacío</h2><p>Agrega productos desde nuestro catálogo.</p><a class="button" href="catalogo.php">Ir al catálogo</a></div>
<?php else: ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="cart-list"><?php foreach ($items as $item): $p=$item['producto']; ?><div class="cart-item"><div><h2><?= e($p['nombre']) ?></h2><p>$<?= number_format((float)$p['precio'],2) ?> por unidad</p></div><div class="cart-actions"><input type="number" min="1" name="cantidades[<?= (int)$p['id'] ?>]" value="<?= $item['cantidad'] ?>"><button type="submit" name="eliminar" value="<?= (int)$p['id'] ?>" class="link-button">Eliminar</button></div><strong>$<?= number_format($item['subtotal'],2) ?></strong></div><?php endforeach; ?></div><div class="cart-summary"><span>Total</span><strong>$<?= number_format($total,2) ?></strong></div><button class="button" type="submit" name="actualizar">Actualizar carrito</button> <a class="button secondary" href="pedido.php">Continuar pedido</a></form><?php endif; ?></main>
<?php require __DIR__ . '/includes/footer.php'; ?></body></html>
