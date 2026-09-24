<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

try {
    $pedidos = $db->query("SELECT * FROM pedidos ORDER BY id DESC LIMIT 20")->fetchAll();
} catch (Exception $e) {
    $pedidos = [];
}
?>

<div style="background: #054770; color: white; padding: 12px 16px; font-weight: bold; font-size: 0.85rem;">
    📋 MIS PEDIDOS REALIZADOS
</div>

<div style="padding: 16px; max-width: 650px; margin: 0 auto;">

    <?php if (empty($pedidos)): ?>
        <div style="background: white; padding: 30px; text-align: center; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-top: 20px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">📋</div>
            <h3 style="color: #054770; margin: 0 0 8px 0;">No tienes pedidos registrados</h3>
            <p style="color: #64748B; font-size: 0.9rem;">Cuando realices compras en la tienda escolar aparecerán aquí.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($pedidos as $p): 
                // 1. Extraer nombre del cliente buscando en todas las columnas
                $cliente_nombre = '';
                $possible_names = ['cliente_nombre', 'nombre_cliente', 'cliente', 'nombre', 'estudiante', 'nombre_estudiante', 'usuario', 'nombre_usuario', 'user_name'];
                foreach ($possible_names as $cCol) {
                    if (!empty($p[$cCol]) && trim($p[$cCol]) !== '') {
                        $cliente_nombre = $p[$cCol];
                        break;
                    }
                }
                if (empty($cliente_nombre)) {
                    $cliente_nombre = 'Estudiante';
                }

                // 2. Método de pago
                $metodo_pago = '';
                $possible_methods = ['metodo_pago', 'metodo', 'forma_pago', 'tipo_pago', 'pago'];
                foreach ($possible_methods as $mCol) {
                    if (!empty($p[$mCol]) && trim($p[$mCol]) !== '') {
                        $metodo_pago = $p[$mCol];
                        break;
                    }
                }
                if (empty($metodo_pago)) {
                    $metodo_pago = 'Presencial / Efectivo';
                }

                // 3. Monto total
                $monto_total = 0;
                $possible_totals = ['monto_total', 'total', 'precio_total', 'valor_total', 'monto', 'precio'];
                foreach ($possible_totals as $tCol) {
                    if (isset($p[$tCol]) && floatval($p[$tCol]) > 0) {
                        $monto_total = floatval($p[$tCol]);
                        break;
                    }
                }

                // 4. Estado
                $estado = strtoupper($p['estado'] ?? $p['status'] ?? 'PENDIENTE');

                $color_estado = '#F59E0B';
                if (strpos($estado, 'LISTO') !== false || strpos($estado, 'ENTREGADO') !== false) {
                    $color_estado = '#10B981';
                } elseif (strpos($estado, 'CANCELADO') !== false) {
                    $color_estado = '#EF4444';
                }

                // 5. Detalles de lo comprado
                $detalles = [];
                try {
                    $stmtDet = $db->prepare("
                        SELECT d.*, p.nombre AS prod_nombre 
                        FROM detalle_pedidos d 
                        LEFT JOIN productos p ON d.producto_id = p.id 
                        WHERE d.pedido_id = ?
                    ");
                    $stmtDet->execute([$p['id']]);
                    $detalles = $stmtDet->fetchAll();
                } catch (Exception $eDet) {
                    $detalles = [];
                }
            ?>
                <div style="background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 5px solid <?= $color_estado ?>;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #F1F5F9; padding-bottom: 8px;">
                        <div>
                            <span style="font-weight: 800; color: #054770; font-size: 1.05rem;">Pedido #<?= $p['id'] ?></span>
                            <?php if (!empty($p['codigo_pedido'])): ?>
                                <span style="font-size: 0.75rem; color: #64748B; margin-left: 6px;">(<?= htmlspecialchars($p['codigo_pedido']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <span style="background: <?= $color_estado ?>; color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                            <?= htmlspecialchars($estado) ?>
                        </span>
                    </div>

                    <div style="font-size: 0.88rem; color: #475569; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                        <div><strong>👤 Estudiante:</strong> <?= htmlspecialchars($cliente_nombre) ?></div>
                        <div><strong>💳 Pago:</strong> <?= htmlspecialchars($metodo_pago) ?></div>
                    </div>

                    <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 8px; margin-bottom: 12px;">
                        <div style="font-size: 0.8rem; font-weight: bold; color: #64748B; margin-bottom: 6px; text-transform: uppercase;">📦 Productos comprados:</div>
                        <?php if (!empty($detalles)): ?>
                            <ul style="margin: 0; padding-left: 18px; font-size: 0.88rem; color: #1E293B;">
                                <?php foreach ($detalles as $item): 
                                    $pNombre = $item['prod_nombre'] ?? $item['nombre_producto'] ?? 'Producto';
                                    $pCant = $item['cantidad'] ?? 1;
                                    $pPrecio = $item['precio_unitario'] ?? $item['precio'] ?? 0;
                                ?>
                                    <li style="margin-bottom: 3px;">
                                        <strong><?= $pCant ?>x</strong> <?= htmlspecialchars($pNombre) ?> 
                                        <?php if ($pPrecio > 0): ?>
                                            <span style="color: #64748B; font-size: 0.8rem;">(<?= formatCOP($pPrecio) ?> c/u)</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span style="font-size: 0.85rem; color: #94A3B8; font-style: italic;">Sin detalle registrado.</span>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px;">
                        <span style="font-size: 0.9rem; font-weight: bold; color: #475569;">Monto Total:</span>
                        <span style="font-weight: 800; color: #054770; font-size: 1.15rem;"><?= formatCOP($monto_total) ?></span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>