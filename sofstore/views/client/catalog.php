<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

// Traer productos activos
try {
    $productos = $db->query("SELECT * FROM productos WHERE publicado_hoy = 1 ORDER BY nombre ASC")->fetchAll();
    if (empty($productos)) {
        $productos = $db->query("SELECT * FROM productos ORDER BY nombre ASC")->fetchAll();
    }
} catch (Exception $e) {
    $productos = $db->query("SELECT * FROM productos ORDER BY nombre ASC")->fetchAll();
}
?>

<div style="background: #054770; color: white; padding: 12px 16px; font-weight: bold; font-size: 0.85rem; letter-spacing: 0.5px;">
    PRODUCTOS DEL DÍA
</div>

<div style="padding: 16px; max-width: 900px; margin: 0 auto;">

    <!-- Buscador Rápido de Productos -->
    <input type="text" id="searchClientProduct" onkeyup="filterClientProducts()" placeholder="🔍 Buscar snacks, bebidas..." style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #CBD5E1; border-radius: var(--radius-sm); box-sizing: border-box; font-size: 0.95rem;">

    <!-- Mensaje Toast al agregar -->
    <div id="toast-client" style="display: none; position: fixed; top: 20px; right: 20px; background: #10B981; color: white; padding: 12px 20px; border-radius: var(--radius-sm); font-weight: bold; box-shadow: var(--shadow-sm); z-index: 2000;">
        ¡Añadido al carrito!
    </div>

    <!-- Grid de Productos -->
    <div id="productsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px;">
        <?php foreach ($productos as $prod): 
            // Extraer el valor del precio buscando en todas las columnas posibles
            $val_precio = 0;
            foreach (['precio_base', 'precio', 'precio_venta', 'precio_unitario', 'valor'] as $pCol) {
                if (isset($prod[$pCol]) && floatval($prod[$pCol]) > 0) {
                    $val_precio = floatval($prod[$pCol]);
                    break;
                }
            }

            // Extraer stock
            $val_stock = 0;
            foreach (['stock_actual', 'stock', 'cantidad'] as $sCol) {
                if (isset($prod[$sCol]) && $prod[$sCol] !== null) {
                    $val_stock = intval($prod[$sCol]);
                    break;
                }
            }

            // Verificar imagen
            $img_name = !empty($prod['imagen']) ? basename($prod['imagen']) : '';
            $img_url = null;
            if (!empty($img_name)) {
                $possible_paths = [
                    __DIR__ . '/../../uploads/products/' . $img_name => BASE_URL . 'uploads/products/' . $img_name,
                    __DIR__ . '/../../assets/images/' . $img_name => BASE_URL . 'assets/images/' . $img_name,
                ];
                foreach ($possible_paths as $fs_path => $web_path) {
                    if (file_exists($fs_path)) {
                        $img_url = $web_path;
                        break;
                    }
                }
            }
        ?>
            <div class="product-card" data-name="<?= strtolower(htmlspecialchars($prod['nombre'])) ?>" style="background: white; border-radius: 12px; padding: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
                <div>
                    <!-- Imagen o Icono -->
                    <div style="height: 110px; background: #F8FAFC; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 10px;">
                        <?php if ($img_url): ?>
                            <img src="<?= $img_url ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 2.8rem;">🥪</span>
                        <?php endif; ?>
                    </div>

                    <!-- Disponibilidad -->
                    <div style="font-size: 0.75rem; color: <?= $val_stock > 0 ? '#10B981' : '#EF4444' ?>; font-weight: bold; text-align: left; margin-bottom: 4px;">
                        <?= $val_stock > 0 ? 'Disponible (' . $val_stock . ')' : 'Agotado' ?>
                    </div>

                    <!-- Nombre del Producto -->
                    <div style="font-weight: 700; color: #1E293B; font-size: 0.95rem; text-align: left; line-height: 1.2; margin-bottom: 6px;">
                        <?= htmlspecialchars($prod['nombre']) ?>
                    </div>

                    <!-- Precio -->
                    <div style="font-weight: 800; color: #054770; font-size: 0.95rem; text-align: left; margin-bottom: 12px;">
                        <?= formatCOP($val_precio) ?>
                    </div>
                </div>

                <!-- Botón Añadir -->
                <?php if ($val_stock > 0): ?>
                    <button onclick="addToCart(<?= $prod['id'] ?>)" class="btn btn-teal" style="width: 100%; padding: 8px 10px; font-weight: bold; font-size: 0.85rem; border-radius: 6px; cursor: pointer;">
                        Añadir al Carrito
                    </button>
                <?php else: ?>
                    <button disabled class="btn" style="width: 100%; padding: 8px 10px; font-weight: bold; font-size: 0.85rem; border-radius: 6px; background: #CBD5E1; color: #64748B; cursor: not-allowed;">
                        Agotado
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
function filterClientProducts() {
    const query = document.getElementById('searchClientProduct').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(query) ? 'flex' : 'none';
    });
}

function addToCart(id) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('producto_id', id);
    formData.append('cantidad', 1);

    fetch('<?= BASE_URL ?>api/cart_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const toast = document.getElementById('toast-client');
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 1800);
        } else {
            alert(data.message);
        }
    })
    .catch(() => alert('Error al agregar al carrito.'));
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>