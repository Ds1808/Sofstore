<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

// Fecha a consultar (Por defecto el día de hoy)
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');

// 1. Resumen general de pedidos
$stmtPedidos = $db->prepare("SELECT * FROM pedidos WHERE DATE(creado_en) = ? OR DATE(fecha) = ?");
try {
    $stmtPedidos->execute([$fecha_filtro, $fecha_filtro]);
} catch (Exception $e) {
    $stmtPedidos = $db->prepare("SELECT * FROM pedidos");
    $stmtPedidos->execute();
}
$pedidos_dia = $stmtPedidos->fetchAll();

$total_ventas = 0;
$ventas_efectivo = 0;
$ventas_nequi = 0;
$pedidos_entregados = 0;
$pedidos_pendientes = 0;

foreach ($pedidos_dia as $p) {
    if ($p['estado'] !== 'CANCELADO') {
        $total_ventas += floatval($p['monto_total']);
        
        $metodo = strtoupper($p['metodo_pago'] ?? '');
        if (strpos($metodo, 'NEQUI') !== false || strpos($metodo, 'VIRTUAL') !== false) {
            $ventas_nequi += floatval($p['monto_total']);
        } else {
            $ventas_efectivo += floatval($p['monto_total']);
        }
    }

    if ($p['estado'] === 'ENTREGADO') {
        $pedidos_entregados++;
    } elseif ($p['estado'] === 'PENDIENTE' || $p['estado'] === 'EN_PREPARACION') {
        $pedidos_pendientes++;
    }
}

// 2. Top Productos Más Vendidos
$queryTop = "SELECT prod.nombre, SUM(d.cantidad) as unidades_vendidas, SUM(d.cantidad * d.precio_unitario) as recaudo_total 
             FROM detalle_pedidos d 
             JOIN pedidos p ON d.pedido_id = p.id 
             JOIN productos prod ON d.producto_id = prod.id 
             WHERE p.estado != 'CANCELADO' 
             GROUP BY d.producto_id 
             ORDER BY unidades_vendidas DESC 
             LIMIT 5";
$top_productos = [];
try {
    $top_productos = $db->query($queryTop)->fetchAll();
} catch (Exception $e) {
    $top_productos = [];
}

// 3. Alertas de Reabastecimiento (Stock Crítico)
$queryStock = "SELECT * FROM productos WHERE stock_actual <= 5 ORDER BY stock_actual ASC";
$stock_critico = [];
try {
    $stock_critico = $db->query($queryStock)->fetchAll();
} catch (Exception $e) {
    try {
        $stock_critico = $db->query("SELECT * FROM productos WHERE stock <= 5 ORDER BY stock ASC")->fetchAll();
    } catch (Exception $ex) {
        $stock_critico = [];
    }
}
?>

<div class="day-products-banner">
    PANEL DE ADMINISTRACIÓN - ESTADÍSTICAS Y CIERRE DE JORNADA
</div>

<div style="padding: 16px; max-width: 1000px; margin: 0 auto;">

    <!-- Navegación Superior de Admin -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>index.php?view=admin_orders" class="btn btn-primary" style="flex: 1; text-align: center;">📦 Ver Pedidos</a>
        <a href="<?= BASE_URL ?>index.php?view=admin_inventory" class="btn btn-teal" style="flex: 1; text-align: center;">🏷️ Gestionar Inventario</a>
        <a href="<?= BASE_URL ?>index.php?view=admin_stats" class="btn" style="flex: 1; text-align: center; background: #054770; color: white; font-weight: bold;">📊 Estadísticas</a>
    </div>

    <!-- Filtro de Fecha -->
    <div style="background: white; padding: 15px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h3 style="margin: 0; color: var(--dark-blue); font-size: 1.1rem;">📅 Informe del Día</h3>
        <form method="GET" style="display: flex; gap: 8px; align-items: center;">
            <input type="hidden" name="view" value="admin_stats">
            <input type="date" name="fecha" value="<?= htmlspecialchars($fecha_filtro) ?>" style="padding: 6px 10px; border: 1px solid #CCC; border-radius: 4px; font-weight: bold;">
            <button type="submit" class="btn btn-teal" style="padding: 6px 12px;">Consultar</button>
        </form>
    </div>

    <!-- Tarjetas de Métricas Rápidas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Vendido -->
        <div style="background: #054770; color: white; padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.85rem; opacity: 0.9;">💵 Total Ventas</div>
            <div style="font-size: 1.8rem; font-weight: 800; margin-top: 4px;"><?= formatCOP($total_ventas) ?></div>
            <small style="opacity: 0.8;"><?= count($pedidos_dia) ?> pedidos registrados</small>
        </div>

        <!-- Cobros en Efectivo -->
        <div style="background: white; border-left: 5px solid #10B981; padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.85rem; color: var(--grey-text);">💵 Ingresos en Efectivo</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #10B981; margin-top: 4px;"><?= formatCOP($ventas_efectivo) ?></div>
            <small style="color: var(--grey-text);">Pago en entrega presencial</small>
        </div>

        <!-- Cobros por Nequi -->
        <div style="background: white; border-left: 5px solid #8E7CC3; padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.85rem; color: var(--grey-text);">📱 Ingresos en Nequi</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #8E7CC3; margin-top: 4px;"><?= formatCOP($ventas_nequi) ?></div>
            <small style="color: var(--grey-text);">Pago virtual transferido</small>
        </div>

        <!-- Estado de Operaciones -->
        <div style="background: white; border-left: 5px solid #F59E0B; padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.85rem; color: var(--grey-text);">📦 Pedidos Entregados</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--dark-blue); margin-top: 4px;"><?= $pedidos_entregados ?> / <?= count($pedidos_dia) ?></div>
            <small style="color: #F59E0B; font-weight: bold;"><?= $pedidos_pendientes ?> pendientes por entregar</small>
        </div>

    </div>

    <!-- Sección de Analítica Avanzada -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        
        <!-- Top Productos Más Vendidos -->
        <div style="background: white; padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <h3 style="margin-top: 0; color: var(--dark-blue); font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                🔥 Top Productos Más Vendidos
            </h3>
            <?php if (empty($top_productos)): ?>
                <p style="color: var(--grey-text); font-size: 0.9rem;">No hay suficientes datos de ventas aun.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 10px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #E2E8F0; text-align: left; color: var(--grey-text);">
                            <th style="padding: 6px 0;">Producto</th>
                            <th style="padding: 6px; text-align: center;">Vendidos</th>
                            <th style="padding: 6px; text-align: right;">Total ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_productos as $index => $tp): ?>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <td style="padding: 8px 0; font-weight: bold; color: var(--dark-blue);">
                                    <?= ($index + 1) ?>. <?= htmlspecialchars($tp['nombre']) ?>
                                </td>
                                <td style="padding: 8px; text-align: center; font-weight: bold; color: #10B981;">
                                    <?= $tp['unidades_vendidas'] ?> u.
                                </td>
                                <td style="padding: 8px; text-align: right; font-weight: bold;">
                                    <?= formatCOP($tp['recaudo_total']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Alertas de Reabastecimiento -->
        <div style="background: white; padding: 18px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <h3 style="margin-top: 0; color: var(--dark-blue); font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                ⚠️ Sugerencias de Reabastecimiento
            </h3>
            <p style="color: var(--grey-text); font-size: 0.82rem; margin-top: -5px;">Productos con 5 o menos unidades en stock.</p>
            
            <?php if (empty($stock_critico)): ?>
                <div style="background: #ECFDF5; color: #065F46; padding: 12px; border-radius: var(--radius-sm); font-size: 0.88rem; font-weight: bold; text-align: center;">
                    ✅ Todo el inventario tiene buen stock.
                </div>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 10px 0 0 0; font-size: 0.9rem;">
                    <?php foreach ($stock_critico as $sc): 
                        $s_cant = $sc['stock_actual'] ?? $sc['stock'] ?? 0;
                    ?>
                        <li style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
                            <span style="font-weight: bold; color: var(--dark-blue);"><?= htmlspecialchars($sc['nombre']) ?></span>
                            <span style="background: #FEE2E2; color: #991B1B; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.8rem;">
                                <?= $s_cant ?> restantes
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>