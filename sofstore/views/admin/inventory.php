    <?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();
$productos = $db->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll();
?>

<div class="day-products-banner">
    PANEL DE ADMINISTRACIÓN - INVENTARIO
</div>

<div style="padding: 16px; max-width: 1000px; margin: 0 auto;">

    <!-- Navegación Superior -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?view=admin_orders" class="btn btn-primary" style="flex: 1; text-align: center;">📦 Ver Pedidos</a>
        <a href="<?= BASE_URL ?>index.php?view=admin_inventory" class="btn btn-teal" style="flex: 1; text-align: center;">🏷️ Gestionar Inventario</a>
    </div>

    <!-- Botón desplegar formulario de nuevo producto -->
    <div style="margin-bottom: 16px; text-align: right;">
        <button onclick="toggleForm()" class="btn btn-teal" style="font-weight: bold;">
            ➕ Registrar Nuevo Producto
        </button>
    </div>

    <!-- Formulario para Nuevo Producto -->
    <div id="newProductFormCard" style="display: none; background: white; padding: 20px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 20px; border: 2px solid var(--teal);">
        <h3 style="margin-top: 0; color: var(--dark-blue);">Nuevo Producto</h3>
        <form id="createProductForm" onsubmit="submitNewProduct(event)">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                <div>
                    <label style="font-weight: bold; font-size: 0.85rem; display: block; margin-bottom: 4px;">Nombre del Producto:</label>
                    <input type="text" id="new_nombre" required placeholder="Ej. Empanada de Carne" style="width: 100%; padding: 8px; border: 1px solid #CCC; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="font-weight: bold; font-size: 0.85rem; display: block; margin-bottom: 4px;">Precio ($ COP):</label>
                    <input type="number" id="new_precio" required step="50" min="0" placeholder="Ej. 2500" style="width: 100%; padding: 8px; border: 1px solid #CCC; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="font-weight: bold; font-size: 0.85rem; display: block; margin-bottom: 4px;">Stock Inicial:</label>
                    <input type="number" id="new_stock" value="10" min="0" style="width: 100%; padding: 8px; border: 1px solid #CCC; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="font-weight: bold; font-size: 0.85rem; display: block; margin-bottom: 4px;">Imagen del Producto:</label>
                    <input type="file" id="new_imagen" accept="image/*" style="width: 100%; font-size: 0.85rem;">
                </div>
            </div>

            <div style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="new_publicado" checked style="width: 18px; height: 18px;">
                <label for="new_publicado" style="font-weight: bold; font-size: 0.9rem; cursor: pointer;">Publicar en el catálogo de hoy</label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-teal">Guardar Producto</button>
                <button type="button" onclick="toggleForm()" class="btn" style="background: #64748B; color: white;">Cancelar</button>
            </div>
        </form>
    </div>

    <!-- Buscador Rápido -->
    <input type="text" id="searchProduct" onkeyup="filterProducts()" placeholder="🔍 Buscar producto por nombre..." style="width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #CCC; border-radius: var(--radius-sm); box-sizing: border-box;">

    <!-- Mensaje Toast -->
    <div id="toast-inv" style="display: none; position: fixed; top: 20px; right: 20px; background: #10B981; color: white; padding: 12px 20px; border-radius: var(--radius-sm); font-weight: bold; box-shadow: var(--shadow-sm); z-index: 2000;">
        ¡Acción completada con éxito!
    </div>

    <div style="background: var(--white); padding: 16px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow-x: auto;">
        <table id="inventoryTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="border-bottom: 2px solid #E2E8F0; color: var(--dark-blue);">
                    <th style="padding: 10px; text-align: center;">Imagen</th>
                    <th style="padding: 10px;">Producto</th>
                    <th style="padding: 10px; text-align: center;">Precio ($)</th>
                    <th style="padding: 10px; text-align: center;">Stock Actual</th>
                    <th style="padding: 10px; text-align: center;">Publicado Hoy</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod): 
                    // Obtener precio buscando en posibles nombres de columna
                    $val_precio = 0;
                    foreach (['precio_base', 'precio', 'precio_venta', 'precio_unitario', 'valor'] as $pCol) {
                        if (isset($prod[$pCol]) && $prod[$pCol] !== null) {
                            $val_precio = $prod[$pCol];
                            break;
                        }
                    }

                    // Obtener stock
                    $val_stock = 0;
                    foreach (['stock_actual', 'stock', 'cantidad'] as $sCol) {
                        if (isset($prod[$sCol]) && $prod[$sCol] !== null) {
                            $val_stock = $prod[$sCol];
                            break;
                        }
                    }

                    // Verificar imagen física
                    $img_name = !empty($prod['imagen']) ? basename($prod['imagen']) : '';
                    $img_url = null;

                    if (!empty($img_name)) {
                        $possible_paths = [
                            __DIR__ . '/../../uploads/products/' . $img_name => BASE_URL . 'uploads/products/' . $img_name,
                            __DIR__ . '/../../assets/images/' . $img_name => BASE_URL . 'assets/images/' . $img_name,
                            __DIR__ . '/../../uploads/' . $img_name => BASE_URL . 'uploads/' . $img_name,
                        ];
                        
                        foreach ($possible_paths as $fs_path => $web_path) {
                            if (file_exists($fs_path)) {
                                $img_url = $web_path;
                                break;
                            }
                        }
                    }
                ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px; text-align: center; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                <?php if ($img_url): ?>
                                    <img src="<?= $img_url ?>" alt="Producto" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #DDD;">
                                <?php else: ?>
                                    <div style="width: 45px; height: 45px; background: #E2E8F0; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🖼️</div>
                                <?php endif; ?>
                                
                                <input type="file" id="img_<?= $prod['id'] ?>" accept="image/*" style="font-size: 0.7rem; width: 110px;">
                            </div>
                        </td>
                        <td style="padding: 10px; font-weight: bold; color: var(--dark-blue);">
                            <?= htmlspecialchars($prod['nombre']) ?>
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <input type="number" id="precio_<?= $prod['id'] ?>" value="<?= $val_precio ?>" step="50" min="0" style="width: 85px; padding: 6px; border: 1px solid #CCC; border-radius: 4px; text-align: center; font-weight: bold; color: var(--medium-blue);">
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <input type="number" id="stock_<?= $prod['id'] ?>" value="<?= $val_stock ?>" min="0" style="width: 65px; padding: 6px; border: 1px solid #CCC; border-radius: 4px; text-align: center; font-weight: bold;">
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <input type="checkbox" id="pub_<?= $prod['id'] ?>" <?= !empty($prod['publicado_hoy']) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button onclick="saveInventory(<?= $prod['id'] ?>)" class="btn btn-teal" style="padding: 6px 10px; font-size: 0.8rem;">
                                    Guardar
                                </button>
                                <button onclick="deleteProduct(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nombre'])) ?>')" class="btn" style="padding: 6px 10px; font-size: 0.8rem; background: #EF4444; color: white;">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function toggleForm() {
    const card = document.getElementById('newProductFormCard');
    card.style.display = card.style.display === 'none' ? 'block' : 'none';
}

function submitNewProduct(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('nombre', document.getElementById('new_nombre').value);
    formData.append('precio', document.getElementById('new_precio').value);
    formData.append('stock_actual', document.getElementById('new_stock').value);
    
    if (document.getElementById('new_publicado').checked) {
        formData.append('publicado_hoy', '1');
    }

    const imgFile = document.getElementById('new_imagen').files[0];
    if (imgFile) {
        formData.append('imagen', imgFile);
    }

    fetch('<?= BASE_URL ?>api/create_product.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('¡Producto registrado con éxito!');
            setTimeout(() => { location.reload(); }, 1000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Error al conectar con el servidor.'));
}

function filterProducts() {
    const input = document.getElementById('searchProduct').value.toLowerCase();
    const rows = document.querySelectorAll('#inventoryTable tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        row.style.display = name.includes(input) ? '' : 'none';
    });
}

function saveInventory(id) {
    const precio = document.getElementById('precio_' + id).value;
    const stock = document.getElementById('stock_' + id).value;
    const pub = document.getElementById('pub_' + id).checked ? 1 : 0;
    const imgInput = document.getElementById('img_' + id);

    const formData = new FormData();
    formData.append('producto_id', id);
    formData.append('precio', precio);
    formData.append('stock_actual', stock);
    formData.append('publicado_hoy', pub);

    if (imgInput && imgInput.files[0]) {
        formData.append('imagen', imgInput.files[0]);
    }

    fetch('<?= BASE_URL ?>api/update_inventory.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('¡Producto actualizado correctamente!');
            setTimeout(() => { location.reload(); }, 1000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Error al conectar con el servidor.'));
}

function deleteProduct(id, nombre) {
    if (confirm('¿Estás seguro de que deseas eliminar el producto "' + nombre + '"? Esta acción no se puede deshacer.')) {
        const formData = new FormData();
        formData.append('producto_id', id);

        fetch('<?= BASE_URL ?>api/delete_product.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Producto eliminado.');
                setTimeout(() => { location.reload(); }, 1000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(() => alert('Error al conectar con el servidor.'));
    }
}

function showToast(msg) {
    const toast = document.getElementById('toast-inv');
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2000);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>