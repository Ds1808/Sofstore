<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido.');
}

$producto_id = intval($_POST['producto_id'] ?? 0);

if (!$producto_id) {
    jsonResponse(false, 'ID de producto no válido.');
}

try {
    $db = (new Database())->getConnection();

    // Consultar imagen para eliminarla físicamente si existe
    $stmt = $db->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $prod = $stmt->fetch();

    if ($prod && !empty($prod['imagen'])) {
        $img_name = basename($prod['imagen']);
        $img_path = __DIR__ . '/../uploads/products/' . $img_name;
        if (file_exists($img_path)) {
            @unlink($img_path);
        }
    }

    // Eliminar el producto de la base de datos
    $stmtDel = $db->prepare("DELETE FROM productos WHERE id = ?");
    $stmtDel->execute([$producto_id]);

    jsonResponse(true, 'Producto eliminado correctamente.');
} catch (Exception $e) {
    jsonResponse(false, 'Error al eliminar el producto: ' . $e->getMessage());
}