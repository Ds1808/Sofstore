<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

$estado_filtro = sanitize($_GET['estado'] ?? 'TODOS');

if ($estado_filtro !== 'TODOS') {
    $stmt = $db->prepare("SELECT * FROM pedidos WHERE estado = ? ORDER BY id DESC");
    $stmt->execute([$estado_filtro]);
} else {
    $stmt = $db->query("SELECT * FROM pedidos ORDER BY id DESC");
}
$pedidos = $stmt->fetchAll();

foreach ($pedidos as &$p) {
    $stmtDet = $db->prepare("SELECT d.*, prod.nombre FROM detalle_pedidos d JOIN productos prod ON d.producto_id = prod.id WHERE d.pedido_id = ?");
    $stmtDet->execute([$p['id']]);
    $p['detalles'] = $stmtDet->fetchAll();
}
?>

<div class="day-products-banner">
    PANEL DE ADMINISTRACIÓN - PEDIDOS
</div>

<div style="padding: 16px; max-width: 900px; margin: 0 auto;">

    <!-- Navegación Superior de Admin -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>index.php?view=admin_orders" class="btn btn-primary" style="flex: 1; text-align: center;">📦 Ver Pedidos</a>
        <a href="<?= BASE_URL ?>index.php?view=admin_inventory" class="btn btn-teal" style="flex: 1; text-align: center;">🏷️ Gestionar Inventario</a>
    </div>

    <!-- Filtros de Estado -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 15px;">
        <?php
        $filtros = ['TODOS', 'PENDIENTE', 'EN_PREPARACION', 'LISTO', 'ENTREGADO', 'CANCELADO'];
        foreach ($filtros as $f):
            $active = $estado_filtro === $f ? 'background: var(--dark-blue); color: white;' : 'background: #EBF3F5; color: var(--dark-blue);';
        ?>
            <a href="<?= BASE_URL ?>index.php?view=admin_orders&estado=<?= $f ?>" 
               style="padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-decoration: none; white-space: nowrap; <?= $active ?>">
               <?= str_replace('_', ' ', $f) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Mensaje de notificación flotante -->
    <div id="toast-msg" style="display: none; position: fixed; top: 20px; right: 20px; background: #10B981; color: white; padding: 12px 20px; border-radius: var(--radius-sm); font-weight: bold; box-shadow: var(--shadow-sm); z-index: 2000;">
        ¡Estado actualizado correctamente!
    </div>

    <!-- Lista de Pedidos -->
    <?php if (empty($pedidos)): ?>
        <div style="background: white; padding: 25px; text-align: center; border-radius: var(--radius-md); color: var(--grey-text);">
            No hay pedidos registrados <?= $estado_filtro !== 'TODOS' ? "en estado $estado_filtro" : '' ?>.
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($pedidos as $p): ?>
                <div style="background: var(--white); padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 5px solid <?= $p['estado'] === 'PENDIENTE' ? '#F59E0B' : ($p['estado'] === 'LISTO' ? '#10B981' : ($p['estado'] === 'CANCELADO' ? '#EF4444' : '#3B82F6')) ?>;">
                    
                    <!-- Encabezado del Pedido -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--dark-blue);">
                                <?= htmlspecialchars($p['codigo_pedido']) ?> — <?= htmlspecialchars($p['nombre_estudiante']) ?>
                            </h3>
                            <small style="color: var(--grey-text);">Fecha: <?= htmlspecialchars($p['creado_en'] ?? '') ?></small>
                        </div>

                        <!-- Selector de Estado -->
                        <div>
                            <select onchange="updateStatus(<?= $p['id'] ?>, this.value)" style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid #CCC; font-weight: bold; cursor: pointer; background: #F8FAFC;">
                                <option value="PENDIENTE" <?= $p['estado'] === 'PENDIENTE' ? 'selected' : '' ?>>🟡 PENDIENTE</option>
                                <option value="EN_PREPARACION" <?= $p['estado'] === 'EN_PREPARACION' ? 'selected' : '' ?>>🔵 EN PREPARACIÓN</option>
                                <option value="LISTO" <?= $p['estado'] === 'LISTO' ? 'selected' : '' ?>>🟢 LISTO PARA ENTREGAR</option>
                                <option value="ENTREGADO" <?= $p['estado'] === 'ENTREGADO' ? 'selected' : '' ?>>✅ ENTREGADO</option>
                                <option value="CANCELADO" <?= $p['estado'] === 'CANCELADO' ? 'selected' : '' ?>>🔴 CANCELADO</option>
                            </select>
                        </div>
                    </div>

                    <!-- Detalle de Productos -->
                    <div style="background: #F8FAFC; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 10px;">
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem;">
                            <?php foreach ($p['detalles'] as $item): ?>
                                <li style="display: flex; justify-content: space-between; padding: 4px 0;">
                                    <span><strong><?= $item['cantidad'] ?>x</strong> <?= htmlspecialchars($item['nombre']) ?></span>
                                    <span><?= formatCOP($item['precio_unitario'] * $item['cantidad']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Info Pago y Comprobante -->
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span><strong>Método:</strong> <?= htmlspecialchars($p['metodo_pago']) ?></span>
                            <?php if (!empty($p['comprobante_nequi'])): ?>
                                &nbsp;|&nbsp;
                                <a href="<?= BASE_URL ?>uploads/receipts/<?= htmlspecialchars($p['comprobante_nequi']) ?>" target="_blank" style="color: var(--medium-blue); font-weight: bold; text-decoration: underline;">
                                    📸 Ver Comprobante
                                </a>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: var(--dark-blue);">
                            Total: <?= formatCOP($p['monto_total']) ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
function updateStatus(id, estado) {
    const formData = new FormData();
    formData.append('pedido_id', id);
    formData.append('estado', estado);

    fetch('<?= BASE_URL ?>api/update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const toast = document.getElementById('toast-msg');
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 2000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Error al conectar con el servidor.'));
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>