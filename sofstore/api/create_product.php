<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido.');
}

$nombre = trim($_POST['nombre'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$stock = intval($_POST['stock_actual'] ?? 0);
$publicado = isset($_POST['publicado_hoy']) ? 1 : 0;

if (empty($nombre) || $precio <= 0) {
    jsonResponse(false, 'Por favor ingresa un nombre y un precio válido mayor a 0.');
}

// Procesar carga de la imagen
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
        $ruta_destino = $dir_subida . $nombre_imagen;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino);
    } else {
        jsonResponse(false, 'Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.');
    }
}

try {
    $db = (new Database())->getConnection();

    $stmtCols = $db->query("SHOW COLUMNS FROM productos");
    $existingCols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

    $fields = ['nombre'];
    $values = [$nombre];
    $placeholders = ['?'];

    // Detectar columna de precio
    foreach (['precio_base', 'precio', 'precio_venta', 'precio_unitario', 'valor'] as $col) {
        if (in_array($col, $existingCols)) {
            $fields[] = $col;
            $values[] = $precio;
            $placeholders[] = '?';
            break;
        }
    }

    // Detectar columna de stock
    foreach (['stock_actual', 'stock', 'cantidad'] as $col) {
        if (in_array($col, $existingCols)) {
            $fields[] = $col;
            $values[] = $stock;
            $placeholders[] = '?';
            break;
        }
    }

    // Columna publicado_hoy
    if (in_array('publicado_hoy', $existingCols)) {
        $fields[] = 'publicado_hoy';
        $values[] = $publicado;
        $placeholders[] = '?';
    }

    // Columna imagen
    if ($nombre_imagen && in_array('imagen', $existingCols)) {
        $fields[] = 'imagen';
        $values[] = $nombre_imagen;
        $placeholders[] = '?';
    }

    $sql = "INSERT INTO productos (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $db->prepare($sql);
    $stmt->execute($values);

    jsonResponse(true, 'Producto creado exitosamente.');
} catch (Exception $e) {
    jsonResponse(false, 'Error al guardar el producto: ' . $e->getMessage());
}