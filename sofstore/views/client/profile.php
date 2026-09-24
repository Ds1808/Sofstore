<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="day-products-banner">
    MI PERFIL
</div>

<div style="padding: 16px; max-width: 500px; margin: 0 auto;">
    <div style="background: var(--white); padding: 20px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); text-align: center;">
        <div style="width: 70px; height: 70px; background: #EBF3F5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
            <i class="fa-solid fa-user" style="font-size: 2rem; color: var(--teal);"></i>
        </div>
        <h3 style="margin-bottom: 4px; color: var(--dark-blue);">Cliente Sofstore</h3>
        <p style="color: var(--grey-text); font-size: 0.85rem; margin-bottom: 20px;">Plataforma de Pedidos Escolar</p>

        <div style="text-align: left; background: #F8FAFC; padding: 14px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 0.9rem;">
            <p style="margin-bottom: 8px;"><strong>Estado del servicio:</strong> <span style="color: var(--teal); font-weight: bold;">Activo</span></p>
            <p style="margin: 0;"><strong>Sede:</strong> Tienda Principal</p>
        </div>

        <a href="<?= BASE_URL ?>index.php?view=catalog" class="btn btn-teal btn-block">
            Volver al Catálogo
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>