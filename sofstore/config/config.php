<?php
// Configuración General de Sofstore
define('BASE_URL', 'http://localhost/sofstore/');
define('APP_NAME', 'Sofstore');

// Datos para Pagos Virtuales (Nequi)
define('NEQUI_NUMBER', '3169600433'); // Número oficial de la tienda escolar
define('NEQUI_NAME', 'Tienda Escolar Sofstore');

// Zonas Horarias y Moneda
date_default_timezone_set('America/Bogota');
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'COP');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>