<?php
// Página de inicio pública del prototipo THABATHA’S.
require_once __DIR__ . '/includes/auth.php';
?><!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>THABATHA’S</title><link rel="stylesheet" href="css/styles.css"></head>
<body>
<header class="site-header"><div><strong>THABATHA’S</strong></div><nav><a href="index.php">Inicio</a><a href="catalogo.php">Catálogo</a><a href="carrito.php">Carrito</a><?php if (usuarioAutenticado()): ?><a href="logout.php">Salir</a><?php else: ?><a href="login.php">Iniciar sesión</a><?php endif; ?></nav></header>
<main class="container hero"><p class="eyebrow">Carnes y productos seleccionados</p><h1>Bienvenido a THABATHA’S</h1><p>Consulta nuestro catálogo, arma tu pedido y comunícate con nosotros para coordinar la compra.</p><a class="button" href="catalogo.php">Ver catálogo</a></main>
</body></html>
