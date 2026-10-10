<?php
// Funciones reutilizables para escapar texto y gestionar el carrito de compra en la sesión.
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function carritoCantidad(): int {
    return array_sum($_SESSION['carrito'] ?? []);
}

function agregarAlCarrito(int $productoId, int $cantidad = 1): void {
    if ($cantidad < 1) $cantidad = 1;
    $_SESSION['carrito'][$productoId] = ($_SESSION['carrito'][$productoId] ?? 0) + $cantidad;
}

function actualizarCarrito(array $cantidades): void {
    foreach ($cantidades as $id => $cantidad) {
        $id = (int)$id;
        $cantidad = (int)$cantidad;
        if ($cantidad <= 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }
    }
}

function eliminarDelCarrito(int $productoId): void {
    unset($_SESSION['carrito'][$productoId]);
}
