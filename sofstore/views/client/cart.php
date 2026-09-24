<?php
require_once __DIR__ . '/../../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$total_general = 0;
foreach ($cart as $item) {
    $total_general += ($item['precio'] * $item['cantidad']);
}
?>

<div style="background: #054770; color: white; padding: 12px 16px; font-weight: bold; font-size: 0.85rem;">
    MI CARRITO DE COMPRAS
</div>

<div style="padding: 16px; max-width: 650px; margin: 0 auto;">

    <?php if (empty($cart)): ?>
        <div style="background: white; padding: 30px; text-align: center; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-top: 20px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">🛒</div>
            <h3 style="color: #054770; margin: 0 0 8px 0;">Tu carrito está vacío</h3>
            <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 20px;">Añade algunos snacks o bebidas del catálogo para continuar.</p>
            <a href="<?= BASE_URL ?>index.php?view=catalog" class="btn btn-teal" style="padding: 10px 20px; font-weight: bold; text-decoration: none; display: inline-block;">
                Ver Catálogo del Día
            </a>
        </div>
    <?php else: ?>
        <div style="background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <?php foreach ($cart as $id => $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #F1F5F9; padding: 12px 0;">
                    <div>
                        <div style="font-weight: bold; color: #1E293B; font-size: 0.95rem;"><?= htmlspecialchars($item['nombre']) ?></div>
                        <div style="color: #054770; font-weight: bold; font-size: 0.85rem; margin-top: 2px;">
                            <?= formatCOP($item['precio']) ?>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <!-- Controles de cantidad -->
                        <div style="display: flex; align-items: center; border: 1px solid #CBD5E1; border-radius: 6px; overflow: hidden;">
                            <button onclick="updateQty(<?= $id ?>, <?= $item['cantidad'] - 1 ?>)" style="background: #F8FAFC; border: none; padding: 4px 10px; cursor: pointer; font-weight: bold;">-</button>
                            <span style="padding: 4px 10px; font-weight: bold; font-size: 0.9rem; background: white;"><?= $item['cantidad'] ?></span>
                            <button onclick="updateQty(<?= $id ?>, <?= $item['cantidad'] + 1 ?>)" style="background: #F8FAFC; border: none; padding: 4px 10px; cursor: pointer; font-weight: bold;">+</button>
                        </div>

                        <!-- Botón Eliminar -->
                        <button onclick="removeItem(<?= $id ?>)" style="background: #FEE2E2; color: #EF4444; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                            🗑️
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Total acumulado -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 10px; border-top: 2px solid #E2E8F0;">
                <span style="font-size: 1.1rem; font-weight: bold; color: #1E293B;">Total a Pagar:</span>
                <span style="font-size: 1.3rem; font-weight: 800; color: #054770;"><?= formatCOP($total_general) ?></span>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>index.php?view=checkout" class="btn btn-teal" style="flex: 1; text-align: center; padding: 12px; font-size: 1rem; font-weight: bold; text-decoration: none;">
                Proceder al Pago 💳
            </a>
        </div>
    <?php endif; ?>

</div>

<script>
function updateQty(id, qty) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('producto_id', id);
    formData.append('cantidad', qty);

    fetch('<?= BASE_URL ?>api/cart_action.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(() => location.reload());
}

function removeItem(id) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('producto_id', id);

    fetch('<?= BASE_URL ?>api/cart_action.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(() => location.reload());
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>