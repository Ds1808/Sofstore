<?php
require_once __DIR__ . '/../../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: ' . BASE_URL . 'index.php?view=catalog');
    exit;
}

$total_general = 0;
foreach ($cart as $item) {
    $total_general += (floatval($item['precio']) * intval($item['cantidad']));
}

$nombre_guardado = $_SESSION['nombre'] ?? $_SESSION['user_name'] ?? '';
if (in_array(strtolower($nombre_guardado), ['estudiante', 'estudiante sofstore', ''])) {
    $nombre_guardado = '';
}
?>

<div style="background: #054770; color: white; padding: 12px 16px; font-weight: bold; font-size: 0.85rem;">
    FINALIZAR PEDIDO
</div>

<div style="padding: 16px; max-width: 600px; margin: 0 auto;">

    <form id="orderForm" onsubmit="processOrder(event)" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        
        <div style="margin-bottom: 16px;">
            <label style="font-weight: bold; font-size: 0.9rem; display: block; margin-bottom: 6px; color: #1E293B;">Nombre y Apellido del Estudiante:</label>
            <input type="text" id="cliente_nombre" required value="<?= htmlspecialchars($nombre_guardado) ?>" placeholder="Ej. Ana Sofía Rengifo" style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem;">
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-weight: bold; font-size: 0.9rem; display: block; margin-bottom: 6px; color: #1E293B;">Método de Pago:</label>
            <select id="metodo_pago" onchange="toggleNequiInfo()" style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-weight: bold;">
                <option value="EFECTIVO">💵 Pago Presencial (Efectivo en Entrega)</option>
                <option value="NEQUI">📱 Transferencia Nequi</option>
            </select>
        </div>

        <!-- Panel Informativo Nequi -->
        <div id="nequiBox" style="display: none; background: #F3E8FF; border: 2px dashed #8E7CC3; padding: 14px; border-radius: 8px; margin-bottom: 16px;">
            <div style="font-weight: bold; color: #5B21B6; font-size: 0.9rem; margin-bottom: 4px;">📱 Número Nequi de la Tienda:</div>
            <div style="font-size: 1.2rem; font-weight: 800; color: #054770; margin-bottom: 8px;">300 123 4567</div>
            <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 4px;">Adjuntar Comprobante de Pago:</label>
            <input type="file" id="comprobante" accept="image/*" style="font-size: 0.85rem; width: 100%;">
        </div>

        <!-- Resumen de Total -->
        <div style="background: #F8FAFC; padding: 14px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: bold; color: #1E293B;">Total a Pagar:</span>
            <span style="font-size: 1.3rem; font-weight: 800; color: #054770;"><?= formatCOP($total_general) ?></span>
        </div>

        <button type="submit" class="btn btn-teal" style="width: 100%; padding: 12px; font-weight: bold; font-size: 1rem; border-radius: 6px; cursor: pointer;">
             Confirmar y Enviar Pedido
        </button>
    </form>

</div>

<script>
function toggleNequiInfo() {
    const metodo = document.getElementById('metodo_pago').value;
    document.getElementById('nequiBox').style.display = (metodo === 'NEQUI') ? 'block' : 'none';
}

function processOrder(e) {
    e.preventDefault();
    const nombreInput = document.getElementById('cliente_nombre').value.trim();
    if (!nombreInput) {
        alert('Por favor ingresa tu nombre completo.');
        return;
    }

    const formData = new FormData();
    formData.append('cliente_nombre', nombreInput);
    formData.append('metodo_pago', document.getElementById('metodo_pago').value);

    const compFile = document.getElementById('comprobante').files[0];
    if (compFile) {
        formData.append('comprobante', compFile);
    }

    fetch('<?= BASE_URL ?>api/process_order.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('¡Pedido realizado con éxito!');
            window.location.href = '<?= BASE_URL ?>index.php?view=orders';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Error al procesar el pedido.'));
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>