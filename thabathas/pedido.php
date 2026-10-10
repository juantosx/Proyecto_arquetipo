<?php
// Procesa la solicitud de compra y guarda sus datos para la consulta administrativa.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/negocio.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/auth.php';

$carrito = $_SESSION['carrito'] ?? [];
$items = [];
$total = 0;
$errores = [];
$pedidoCreado = null;
$mensajeWhatsApp = '';

if ($carrito) {
    $ids = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id IN ($placeholders) AND disponible = 1");
    $stmt->execute($ids);
    $productos = $stmt->fetchAll();

    foreach ($productos as $p) {
        $cantidad = (int)($carrito[$p['id']] ?? 0);
        if ($cantidad < 1) continue;
        $subtotal = (float)$p['precio'] * $cantidad;
        $items[] = ['producto' => $p, 'cantidad' => $cantidad, 'subtotal' => $subtotal];
        $total += $subtotal;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pedido'])) {
    if (!verificarCsrf($_POST['csrf_token'] ?? null)) {
        $errores[] = 'La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.';
    } else {
    if (!$items) {
        $errores[] = 'El carrito está vacío o los productos seleccionados ya no están disponibles.';
    }

    $nombre = trim($_POST['nombre_cliente'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $modalidad = $_POST['modalidad'] ?? '';
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombre === '') $errores[] = 'Indica el nombre del cliente.';
    if ($telefono === '') $errores[] = 'Indica un número de teléfono para poder contactar al cliente.';
    if (!in_array($modalidad, ['retiro', 'delivery'], true)) $errores[] = 'Selecciona una modalidad de entrega.';
    if ($modalidad === 'delivery' && $direccion === '') $errores[] = 'Indica la dirección para el delivery.';

    if (!$errores) {
        $usuarioId = $_SESSION['usuario']['id'] ?? null;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, nombre_cliente, telefono, modalidad, direccion, total) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$usuarioId, $nombre, $telefono, $modalidad, $modalidad === 'delivery' ? $direccion : null, $total]);
            $pedidoId = (int)$pdo->lastInsertId();

            $detalleStmt = $pdo->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, nombre_producto, precio, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $p = $item['producto'];
                $detalleStmt->execute([$pedidoId, $p['id'], $p['nombre'], $p['precio'], $item['cantidad'], $item['subtotal']]);
            }

            $pdo->commit();

            $lineas = [
                "Hola, soy {$nombre}. Quiero realizar el pedido #{$pedidoId} en " . NEGOCIO_NOMBRE . ".",
                '',
                'Productos:'
            ];
            foreach ($items as $item) {
                $lineas[] = '- ' . $item['producto']['nombre'] . ' x ' . $item['cantidad'] . ' = $' . number_format($item['subtotal'], 2);
            }
            $lineas[] = '';
            $lineas[] = 'Total: $' . number_format($total, 2);
            $lineas[] = 'Modalidad: ' . ($modalidad === 'delivery' ? 'Delivery' : 'Retiro en el establecimiento');
            if ($modalidad === 'delivery') $lineas[] = 'Dirección: ' . $direccion;
            $lineas[] = 'Teléfono: ' . $telefono;
            $lineas[] = '';
            $lineas[] = 'Quedo atento(a) para coordinar el pago y la entrega/retiro.';
            $mensajeWhatsApp = implode("\n", $lineas);

            $_SESSION['carrito'] = [];
            $pedidoCreado = $pedidoId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errores[] = 'No fue posible registrar el pedido. Verifica la conexión con la base de datos.';
        }
    }
    }
}

$nombreInicial = $_SESSION['usuario']['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedido | THABATHA’S</title><link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="site-header"><a class="brand" href="index.php">THABATHA’S</a><nav><a href="index.php">Inicio</a><a href="catalogo.php">Catálogo</a><a href="carrito.php">Carrito <span class="cart-count"><?= carritoCantidad() ?></span></a><?php if (usuarioAutenticado()): ?><a href="logout.php">Salir</a><?php else: ?><a href="login.php">Iniciar sesión</a><?php endif; ?></nav></header>
<main class="container narrow-container">
<section class="page-heading"><p class="eyebrow">Finalizar solicitud</p><h1>Datos del pedido</h1><p>Completa tus datos. El pago se coordinará directamente con THABATHA’S después de enviar la solicitud.</p></section>

<?php if ($errores): ?><div class="alert error"><ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<?php if ($pedidoCreado): ?>
<div class="empty-state"><p class="eyebrow">Solicitud registrada</p><h2>Pedido #<?= (int)$pedidoCreado ?> creado correctamente</h2><p>Tu pedido quedó registrado. Ahora puedes enviarlo por WhatsApp para coordinar el pago y la modalidad de entrega.</p>
<?php if (WHATSAPP_NUMERO !== ''): ?><a class="button" target="_blank" rel="noopener" href="https://wa.me/<?= e(WHATSAPP_NUMERO) ?>?text=<?= rawurlencode($mensajeWhatsApp) ?>">Enviar pedido por WhatsApp</a><?php else: ?><div class="alert error"><strong>WhatsApp aún no está configurado.</strong><br>El pedido ya fue guardado. Configura el número real de THABATHA’S en <code>config/negocio.php</code> para activar el botón de WhatsApp.</div><textarea class="copy-message" readonly><?= e($mensajeWhatsApp) ?></textarea><?php endif; ?>
<p><a class="button secondary" href="catalogo.php">Volver al catálogo</a></p></div>
<?php elseif (!$items): ?>
<div class="empty-state"><h2>No hay productos en el carrito</h2><p>Agrega al menos un producto antes de realizar un pedido.</p><a class="button" href="catalogo.php">Ir al catálogo</a></div>
<?php else: ?>
<div class="order-grid">
<section class="admin-form">
<form method="post" class="order-form">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
<label>Nombre completo<input type="text" name="nombre_cliente" value="<?= e($_POST['nombre_cliente'] ?? $nombreInicial) ?>" required></label>
<label>Teléfono / WhatsApp<input type="tel" name="telefono" value="<?= e($_POST['telefono'] ?? '') ?>" required></label>
<label>Modalidad<select name="modalidad" id="modalidad" required><option value="">Selecciona una opción</option><option value="retiro" <?= (($_POST['modalidad'] ?? '') === 'retiro') ? 'selected' : '' ?>>Retiro en el establecimiento</option><option value="delivery" <?= (($_POST['modalidad'] ?? '') === 'delivery') ? 'selected' : '' ?>>Delivery</option></select></label>
<label id="direccion-wrap">Dirección para delivery<textarea name="direccion" rows="4"><?= e($_POST['direccion'] ?? '') ?></textarea><small>Solo es necesaria si seleccionas delivery.</small></label>
<div class="form-actions"><a class="button secondary" href="carrito.php">Volver al carrito</a><button class="button" type="submit" name="confirmar_pedido">Confirmar pedido</button></div>
</form>
</section>
<aside class="order-summary"><h2>Resumen</h2><?php foreach ($items as $item): ?><div class="summary-row"><span><?= e($item['producto']['nombre']) ?> × <?= $item['cantidad'] ?></span><strong>$<?= number_format($item['subtotal'], 2) ?></strong></div><?php endforeach; ?><div class="cart-summary"><span>Total</span><strong>$<?= number_format($total, 2) ?></strong></div><p class="muted">El pedido no realiza cobro en línea.</p></aside>
</div>
<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
<script>const modalidad=document.getElementById('modalidad');const wrap=document.getElementById('direccion-wrap');function actualizarDireccion(){if(!modalidad||!wrap)return;wrap.style.display=modalidad.value==='delivery'?'grid':'none';}actualizarDireccion();modalidad?.addEventListener('change',actualizarDireccion);</script>
</body></html>
