<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

$view = $_GET['view'] ?? 'catalog';
$es_admin = (strpos($view, 'admin') === 0) || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sofstore - Tienda Escolar</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
</head>
<body>

<?php if ($es_admin): ?>
    <!-- CABECERA EXCLUSIVA DEL ADMINISTRADOR -->
    <header style="background: #054770; color: white; padding: 12px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
        <div style="max-width: 1100px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                ⚙️ Sofstore <span style="font-size: 0.75rem; background: #8E7CC3; padding: 3px 8px; border-radius: 12px; font-weight: normal;">ADMIN</span>
            </div>
            <nav style="display: flex; gap: 14px; font-size: 0.9rem; font-weight: bold; flex-wrap: wrap;">
                <a href="<?= BASE_URL ?>index.php?view=admin_orders" style="color: <?= $view === 'admin_orders' ? '#38BDF8' : 'white' ?>; text-decoration: none;">📦 Pedidos Clientes</a>
                <a href="<?= BASE_URL ?>index.php?view=admin_inventory" style="color: <?= $view === 'admin_inventory' ? '#38BDF8' : 'white' ?>; text-decoration: none;">🏷️ Stock e Inventario</a>
                <a href="<?= BASE_URL ?>index.php?view=admin_stats" style="color: <?= $view === 'admin_stats' ? '#38BDF8' : 'white' ?>; text-decoration: none;">📊 Estadísticas</a>
                <a href="<?= BASE_URL ?>index.php?view=admin_profile" style="color: <?= $view === 'admin_profile' ? '#38BDF8' : 'white' ?>; text-decoration: none;">👤 Perfil Admin</a>
            </nav>
        </div>
    </header>
<?php else: ?>
    <!-- CABECERA ORIGINAL DEL ESTUDIANTE / CLIENTE -->
    <header style="background: #054770; color: white; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 1.2rem; font-weight: 800; color: white;">
            <a href="<?= BASE_URL ?>index.php?view=catalog" style="color: white; text-decoration: none;">$ Sofstore</a>
        </div>
        <div style="font-size: 0.9rem; font-weight: 600;">
            Hola
        </div>
    </header>
<?php endif; ?>

<main class="container" style="padding-bottom: 80px;">