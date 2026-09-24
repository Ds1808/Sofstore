</main> <!-- Cierre del contenedor principal -->

<?php
$view = $_GET['view'] ?? 'catalog';
$es_admin = (strpos($view, 'admin') === 0) || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin');
?>

<?php if (!$es_admin): ?>
    <!-- BARRA INFERIOR DE NAVEGACIÓN DEL ESTUDIANTE (ORIGINAL) -->
    <nav style="position: fixed; bottom: 0; left: 0; right: 0; background: #054770; display: flex; justify-content: space-around; padding: 8px 0; border-top: 1px solid rgba(255,255,255,0.1); z-index: 1000;">
        <a href="<?= BASE_URL ?>index.php?view=catalog" style="color: <?= $view === 'catalog' ? '#38BDF8' : 'white' ?>; text-decoration: none; text-align: center; font-size: 0.75rem; flex: 1;">
            <div style="font-size: 1.2rem; margin-bottom: 2px;">🏪</div>
            Catálogo
        </a>
        <a href="<?= BASE_URL ?>index.php?view=cart" style="color: <?= $view === 'cart' ? '#38BDF8' : 'white' ?>; text-decoration: none; text-align: center; font-size: 0.75rem; flex: 1;">
            <div style="font-size: 1.2rem; margin-bottom: 2px;">🛒</div>
            Carrito
        </a>
        <a href="<?= BASE_URL ?>index.php?view=orders" style="color: <?= ($view === 'orders' || $view === 'my_orders') ? '#38BDF8' : 'white' ?>; text-decoration: none; text-align: center; font-size: 0.75rem; flex: 1;">
            <div style="font-size: 1.2rem; margin-bottom: 2px;">📋</div>
            Mis Pedidos
        </a>
        <a href="<?= BASE_URL ?>index.php?view=profile" style="color: <?= $view === 'profile' ? '#38BDF8' : 'white' ?>; text-decoration: none; text-align: center; font-size: 0.75rem; flex: 1;">
            <div style="font-size: 1.2rem; margin-bottom: 2px;">👤</div>
            Perfil
        </a>
    </nav>
<?php endif; ?>

</body>
</html>