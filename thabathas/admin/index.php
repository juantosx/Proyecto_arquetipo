<?php
// Inicio del área administrativa; requiere un usuario con rol de administrador.
require_once __DIR__ . '/../includes/auth.php';
exigirRol('admin');
?><!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Panel administrativo | THABATHA’S</title><link rel="stylesheet" href="../css/styles.css"></head>
<body><header class="site-header"><strong>THABATHA’S | Administración</strong><nav><a href="../index.php">Ver sitio</a><a href="productos.php">Productos</a><a href="pedidos.php">Pedidos</a><a href="../logout.php">Salir</a></nav></header><main class="container"><h1>Panel administrativo</h1><p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>.</p><div class="cards"><a class="card" href="productos.php"><h2>Productos</h2><p>Gestionar catálogo, precios, imágenes y disponibilidad.</p></a><a class="card" href="pedidos.php"><h2>Pedidos</h2><p>Consultar solicitudes recibidas.</p></a></div></main></body></html>
