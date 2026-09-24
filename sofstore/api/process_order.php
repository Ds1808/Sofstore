
    <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido.');
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    jsonResponse(false, 'El carrito está vacío.');
}

$cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
if (empty($cliente_nombre)) {
    $cliente_nombre = $_SESSION['nombre'] ?? 'Estudiante';
}
$_SESSION['nombre'] = $cliente_nombre;

$metodo_pago = trim($_POST['metodo_pago'] ?? 'EFECTIVO');

// Subir comprobante si aplica
$nombre_comprobante = null;
if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
    $dir_subida = __DIR__ . '/../uploads/comprobantes/';
    if (!is_dir($dir_subida)) {
        mkdir($dir_subida, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
    $nombre_comprobante = 'comp_' . time() . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['comprobante']['tmp_name'], $dir_subida . $nombre_comprobante);
}

// Calcular total
$total = 0;
foreach ($cart as $item) {
    $total += (floatval($item['precio']) * intval($item['cantidad']));
}

try {
    $db = (new Database())->getConnection();
    $db->beginTransaction();

    $stmtCols = $db->query("SHOW COLUMNS FROM pedidos");
    $existingCols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

    $fields = [];
    $values = [];
    $placeholders = [];

    // Generar código único de pedido
    foreach (['codigo_pedido', 'codigo', 'num_pedido', 'numero_pedido'] as $codeCol) {
        if (in_array($codeCol, $existingCols)) {
            $fields[] = $codeCol;
            $values[] = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
            $placeholders[] = '?';
            break;
        }
    }

    // Nombre del cliente (guardar en todas las columnas de nombre compatibles)
    $possible_name_cols = ['cliente_nombre', 'nombre_cliente', 'cliente', 'nombre', 'estudiante', 'nombre_estudiante', 'usuario', 'nombre_usuario', 'user_name'];
    foreach ($possible_name_cols as $cCol) {
        if (in_array($cCol, $existingCols)) {
            $fields[] = $cCol;
            $values[] = $cliente_nombre;
            $placeholders[] = '?';
        }
    }

    // Total
    $possible_total_cols = ['monto_total', 'total', 'precio_total', 'valor_total', 'monto', 'precio'];
    foreach ($possible_total_cols as $tCol) {
        if (in_array($tCol, $existingCols)) {
            $fields[] = $tCol;
            $values[] = $total;
            $placeholders[] = '?';
            break;
        }
    }

    // Método de pago
    $possible_pay_cols = ['metodo_pago', 'metodo', 'forma_pago', 'tipo_pago', 'pago'];
    foreach ($possible_pay_cols as $mCol) {
        if (in_array($mCol, $existingCols)) {
            $fields[] = $mCol;
            $values[] = $metodo_pago;
            $placeholders[] = '?';
            break;
        }
    }

    // Comprobante Nequi
    if ($nombre_comprobante) {
        foreach (['comprobante', 'comprobante_pago', 'imagen_comprobante', 'voucher'] as $compCol) {
            if (in_array($compCol, $existingCols)) {
                $fields[] = $compCol;
                $values[] = $nombre_comprobante;
                $placeholders[] = '?';
                break;
            }
        }
    }

    // Estado inicial
    foreach (['estado', 'status', 'estado_pedido'] as $sCol) {
        if (in_array($sCol, $existingCols)) {
            $fields[] = $sCol;
            $values[] = 'PENDIENTE';
            $placeholders[] = '?';
            break;
        }
    }

    $sql = "INSERT INTO pedidos (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $db->prepare($sql);
    $stmt->execute($values);
    $pedido_id = $db->lastInsertId();

    // Insertar detalle y restar stock
    $prodCols = $db->query("SHOW COLUMNS FROM productos")->fetchAll(PDO::FETCH_COLUMN);
    $stock_col = in_array('stock_actual', $prodCols) ? 'stock_actual' : (in_array('stock', $prodCols) ? 'stock' : null);

    foreach ($cart as $item) {
        try {
            $stmtDet = $db->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmtDet->execute([$pedido_id, $item['id'], $item['cantidad'], $item['precio']]);
        } catch (Exception $eDet) {}

        if ($stock_col) {
            $stmtStock = $db->prepare("UPDATE productos SET $stock_col = GREATEST(0, $stock_col - ?) WHERE id = ?");
            $stmtStock->execute([$item['cantidad'], $item['id']]);
        }
    }

    $db->commit();

    $_SESSION['cart'] = [];
    $_SESSION['cart_count'] = 0;

    jsonResponse(true, 'Pedido creado correctamente.', ['pedido_id' => $pedido_id]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    jsonResponse(false, 'Error al procesar pedido: ' . $e->getMessage());
}