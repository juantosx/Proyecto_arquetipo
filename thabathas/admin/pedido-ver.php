<?php
// Detalle de una solicitud de compra seleccionada desde el panel administrativo.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funciones.php';
exigirRol('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ?');
$stmt->execute([$id]);
$pedido = $stmt->fetch();
if (!$pedido) { header('Location: pedidos.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM detalle_pedido WHERE pedido_id = ? ORDER BY id ASC');
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pedido #<?= $id ?> | THABATHA’S</title><link rel="stylesheet" href="../css/styles.css"></head>
<body><header class="site-header"><a class="brand" href="index.php">THABATHA’S | Administración</a><nav><a href="index.php">Panel</a><a href="productos.php">Productos</a><a href="pedidos.php">Pedidos</a><a href="../logout.php">Salir</a></nav></header>
<main class="container narrow-container"><a class="back-link" href="pedidos.php">← Volver a pedidos</a><section class="page-heading"><p class="eyebrow">Pedido #<?= $id ?></p><h1><?= e($pedido['nombre_cliente']) ?></h1><p>Registrado el <?= e($pedido['creado_en']) ?>.</p></section>
<div class="detail-box"><div class="detail-columns"><div><strong>Teléfono</strong><p><?= e($pedido['telefono'] ?: 'No indicado') ?></p></div><div><strong>Modalidad</strong><p><?= $pedido['modalidad']==='delivery' ? 'Delivery' : 'Retiro en el establecimiento' ?></p></div><div><strong>Estado</strong><p><span class="status <?= e($pedido['estado']) ?>"><?= e(ucfirst($pedido['estado'])) ?></span></p></div><div><strong>Dirección</strong><p><?= nl2br(e($pedido['direccion'] ?: 'No aplica')) ?></p></div></div></div>
<div class="detail-box"><h2>Productos</h2><div class="order-detail-list"><?php foreach ($detalles as $detalle): ?><div class="summary-row"><span><?= e($detalle['nombre_producto']) ?> × <?= (int)$detalle['cantidad'] ?></span><strong>$<?= number_format((float)$detalle['subtotal'],2) ?></strong></div><?php endforeach; ?></div><div class="cart-summary"><span>Total</span><strong>$<?= number_format((float)$pedido['total'],2) ?></strong></div></div>
</main></body></html>
