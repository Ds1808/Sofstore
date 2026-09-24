<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido.');
}

$producto_id = intval($_POST['producto_id'] ?? 0);
$precio = floatval($_POST['precio'] ?? 0);
$stock_actual = intval($_POST['stock_actual'] ?? 0);
$publicado_hoy = isset($_POST['publicado_hoy']) ? intval($_POST['publicado_hoy']) : 0;

if (!$producto_id) {
    jsonResponse(false, 'ID de producto no válido.');
}

// Procesar imagen si se subió una nueva
$nombre_imagen = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $dir_subida = __DIR__ . '/../uploads/products/';
    if (!is_dir($dir_subida)) {
        mkdir($dir_subida, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (in_array($ext, $extensiones_permitidas)) {
        $nombre_imagen = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $dir_subida . $nombre_imagen);
    }
}

try {
    $db = (new Database())->getConnection();

    $stmtCols = $db->query("SHOW COLUMNS FROM productos");
    $existingCols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

    $updates = [];
    $params = [];

    // Detectar columna de precio
    foreach (['precio_base', 'precio', 'precio_venta', 'precio_unitario', 'valor'] as $col) {
        if (in_array($col, $existingCols)) {
            $updates[] = "$col = ?";
            $params[] = $precio;
            break;
        }
    }

    // Detectar columna de stock
    foreach (['stock_actual', 'stock', 'cantidad'] as $col) {
        if (in_array($col, $existingCols)) {
            $updates[] = "$col = ?";
            $params[] = $stock_actual;
            break;
        }
    }

    if (in_array('publicado_hoy', $existingCols)) {
        $updates[] = "publicado_hoy = ?";
        $params[] = $publicado_hoy;
    }

    if ($nombre_imagen && in_array('imagen', $existingCols)) {
        $updates[] = "imagen = ?";
        $params[] = $nombre_imagen;
    }

    if (!empty($updates)) {
        $params[] = $producto_id;
        $sql = "UPDATE productos SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    jsonResponse(true, 'Inventario actualizado correctamente.');
} catch (Exception $e) {
    jsonResponse(false, 'Error al actualizar inventario: ' . $e->getMessage());
}