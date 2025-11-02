<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

// Redirigir según autenticación y rol
if (estaAutenticado()) {
    if (esAdmin()) {
        header("Location: admin/index.php");
    } else {
        header("Location: jugador/index.php");
    }
} else {
    header("Location: login.php");
}
exit;
