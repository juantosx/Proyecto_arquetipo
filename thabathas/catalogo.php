<?php
// Consulta y presentación de los productos disponibles para el cliente.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/auth.php';

$productos = $pdo->query('SELECT * FROM productos WHERE disponible = 1 ORDER BY nombre')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo | THABATHA’S</title><link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="index.php">THABATHA’S</a>
  <nav><a href="index.php">Inicio</a><a class="active" href="catalogo.php">Catálogo</a><a href="carrito.php">Carrito <span class="cart-count"><?= carritoCantidad() ?></span></a><?php if (usuarioAutenticado()): ?><a href="logout.php">Salir</a><?php else: ?><a href="login.php">Iniciar sesión</a><?php endif; ?></nav>
</header>
<main class="container">
  <section class="page-heading">
    <p class="eyebrow">Productos THABATHA’S</p>
    <h1>Nuestro catálogo</h1>
    <p>Consulta nuestros cortes y productos disponibles. Selecciona lo que deseas y arma tu pedido.</p>
  </section>
  <div class="cards">
    <?php foreach ($productos as $p): ?>
      <article class="product-card">
        <div class="product-image">
          <?php if (!empty($p['imagen'])): ?><img src="<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>"><?php else: ?><span>THABATHA’S</span><?php endif; ?>
        </div>
        <div class="product-body">
          <span class="availability">Disponible</span>
          <h2><?= e($p['nombre']) ?></h2>
          <p><?= e($p['descripcion']) ?></p>
          <div class="product-footer">
            <strong class="price">$<?= number_format((float)$p['precio'], 2) ?></strong>
            <a class="button small" href="producto.php?id=<?= (int)$p['id'] ?>">Ver producto</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body></html>
