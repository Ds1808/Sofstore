<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

$admin_nombre = $_SESSION['admin_nombre'] ?? 'Administrador General';
$admin_email = $_SESSION['admin_email'] ?? 'admin@sofstore.com';
?>

<div class="day-products-banner">
    PERFIL DEL ADMINISTRADOR
</div>

<div style="padding: 20px; max-width: 600px; margin: 0 auto;">

    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); text-align: center;">
        
        <div style="width: 80px; height: 80px; background: #054770; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 15px auto;">
            👤
        </div>

        <h2 style="margin: 0; color: var(--dark-blue); font-size: 1.3rem;"><?= htmlspecialchars($admin_nombre) ?></h2>
        <p style="color: var(--grey-text); font-size: 0.9rem; margin-top: 4px;"><?= htmlspecialchars($admin_email) ?></p>

        <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 20px 0;">

        <div style="text-align: left; font-size: 0.95rem; display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--grey-text); font-weight: bold;">Rol de Usuario:</span>
                <span style="color: #054770; font-weight: bold;">Administrador Tienda</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--grey-text); font-weight: bold;">Establecimiento:</span>
                <span>Tienda Escolar Principal</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--grey-text); font-weight: bold;">Estado del servicio:</span>
                <span style="background: #10B981; color: white; padding: 2px 10px; border-radius: 10px; font-size: 0.8rem; font-weight: bold;">En Línea</span>
            </div>
        </div>

        <div style="margin-top: 25px; display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>index.php?view=catalog" class="btn btn-teal" style="flex: 1; text-align: center; font-size: 0.9rem;">
                🏪 Vista Tienda Estudiante
            </a>
            <a href="<?= BASE_URL ?>index.php?view=admin_orders" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.9rem;">
                📦 Ver Pedidos
            </a>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>