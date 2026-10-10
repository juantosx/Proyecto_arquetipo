<?php
// Lista de solicitudes de compra registradas para revisión del administrador.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funciones.php';
exigirRol('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        header('Location: pedidos.php'); exit;
    }
    $pedidoId = (int)($_POST['pedido_id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    $permitidos = ['pendiente', 'contactado', 'completado', 'cancelado'];
    if ($pedidoId > 0 && in_array($estado, $permitidos, true)) {
        $stmt = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
        $stmt->execute([$estado, $pedidoId]);
    }
    header('Location: pedidos.php'); exit;
}

$pedidos = $pdo->query('SELECT id, nombre_cliente, telefono, modalidad, direccion, total, estado, creado_en FROM pedidos ORDER BY creado_en DESC, id DESC')->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pedidos | THABATHA’S</title><link rel="stylesheet" href="../css/styles.css"></head>
<body><header class="site-header"><a class="brand" href="index.php">THABATHA’S | Administración</a><nav><a href="index.php">Panel</a><a href="productos.php">Productos</a><a class="active" href="pedidos.php">Pedidos</a><a href="../logout.php">Salir</a></nav></header>
<main class="container"><section class="admin-heading"><div><p class="eyebrow">Administración</p><h1>Pedidos recibidos</h1><p class="muted">Consulta las solicitudes y actualiza su estado.</p></div></section>
<?php if (!$pedidos): ?><div class="empty-state"><h2>No hay pedidos registrados</h2><p>Cuando un cliente confirme una solicitud desde el carrito, aparecerá aquí.</p></div><?php else: ?>
<div class="admin-table-wrap"><table class="admin-table orders-table"><thead><tr><th>#</th><th>Cliente</th><th>Modalidad</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($pedidos as $pedido): ?><tr><td>#<?= (int)$pedido['id'] ?></td><td><strong><?= e($pedido['nombre_cliente']) ?></strong><small><?= e($pedido['telefono']) ?></small></td><td><?= $pedido['modalidad'] === 'delivery' ? 'Delivery' : 'Retiro' ?></td><td>$<?= number_format((float)$pedido['total'],2) ?></td><td><span class="status <?= e($pedido['estado']) ?>"><?= e(ucfirst($pedido['estado'])) ?></span></td><td><?= e($pedido['creado_en']) ?></td><td class="action-group"><a class="button small" href="pedido-ver.php?id=<?= (int)$pedido['id'] ?>">Ver detalle</a><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>"><select name="estado" onchange="this.form.submit()" aria-label="Cambiar estado del pedido"><option value="pendiente" <?= $pedido['estado']==='pendiente'?'selected':'' ?>>Pendiente</option><option value="contactado" <?= $pedido['estado']==='contactado'?'selected':'' ?>>Contactado</option><option value="completado" <?= $pedido['estado']==='completado'?'selected':'' ?>>Completado</option><option value="cancelado" <?= $pedido['estado']==='cancelado'?'selected':'' ?>>Cancelado</option></select><input type="hidden" name="cambiar_estado" value="1"></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></main></body></html>
