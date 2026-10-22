<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$storeId = (int)$user['store_id'];

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$readCursor = static function (string $key): ?int {
    if (!array_key_exists($key, $_GET)) {
        return null;
    }

    $value = filter_var($_GET[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($value === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid notification cursor.']);
        exit;
    }

    return $value;
};

$ordersAfter = $readCursor('orders_after');
$salesAfter = $readCursor('sales_after');
$initialized = ($_GET['initialized'] ?? '') === '1';

try {
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(id), 0) FROM orders WHERE store_id = ?');
    $stmt->execute([$storeId]);
    $latestOrderId = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COALESCE(MAX(id), 0) FROM sales WHERE store_id = ?');
    $stmt->execute([$storeId]);
    $latestSaleId = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COALESCE(MAX(id), 0) FROM products WHERE store_id = ?');
    $stmt->execute([$storeId]);
    $latestProductId = (int)$stmt->fetchColumn();

    $orders = [];
    $sales = [];
    $hasMoreOrders = false;
    $hasMoreSales = false;
    $orderCursor = $ordersAfter ?? $latestOrderId;
    $saleCursor = $salesAfter ?? $latestSaleId;
    if ($initialized && $ordersAfter !== null) {
        $stmt = $pdo->prepare(
            'SELECT id, customer_name, total, created_at
             FROM orders
             WHERE store_id = ? AND id > ?
             ORDER BY id ASC
             LIMIT 21'
        );
        $stmt->execute([$storeId, $ordersAfter]);
        $rows = $stmt->fetchAll();
        $hasMoreOrders = count($rows) > 20;
        if ($hasMoreOrders) {
            array_pop($rows);
        }
        foreach ($rows as $row) {
            $orders[] = [
                'id' => (int)$row['id'],
                'customer' => (string)$row['customer_name'],
                'total' => (float)$row['total'],
                'created_at' => date(DATE_ATOM, strtotime((string)$row['created_at'])),
            ];
        }
        if ($hasMoreOrders && $rows) {
            $orderCursor = (int)$rows[count($rows) - 1]['id'];
        } else {
            $orderCursor = $latestOrderId;
        }
    }

    if ($initialized && $salesAfter !== null) {
        $stmt = $pdo->prepare(
            'SELECT id, total, created_at
             FROM sales
             WHERE store_id = ? AND id > ?
             ORDER BY id ASC
             LIMIT 21'
        );
        $stmt->execute([$storeId, $salesAfter]);
        $rows = $stmt->fetchAll();
        $hasMoreSales = count($rows) > 20;
        if ($hasMoreSales) {
            array_pop($rows);
        }
        foreach ($rows as $row) {
            $sales[] = [
                'id' => (int)$row['id'],
                'total' => (float)$row['total'],
                'created_at' => date(DATE_ATOM, strtotime((string)$row['created_at'])),
            ];
        }
        if ($hasMoreSales && $rows) {
            $saleCursor = (int)$rows[count($rows) - 1]['id'];
        } else {
            $saleCursor = $latestSaleId;
        }
    }

    $lowStock = [];
    if (($user['role'] ?? '') === 'owner') {
        $stmt = $pdo->prepare(
            'SELECT id, name, stock_qty, low_stock_at, updated_at
             FROM products
             WHERE store_id = ? AND is_active = 1 AND stock_qty <= low_stock_at
             ORDER BY stock_qty ASC, name ASC
             LIMIT 300'
        );
        $stmt->execute([$storeId]);
        foreach ($stmt->fetchAll() as $row) {
            $lowStock[] = [
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
                'stock' => (int)$row['stock_qty'],
                'threshold' => (int)$row['low_stock_at'],
                'updated_at' => (string)$row['updated_at'],
            ];
        }
    }

    echo json_encode([
        'initialized' => true,
        'orders_cursor' => $orderCursor,
        'sales_cursor' => $saleCursor,
        'orders' => $orders,
        'sales' => $sales,
        'low_stock' => $lowStock,
        'has_more_orders' => $hasMoreOrders,
        'has_more_sales' => $hasMoreSales,
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('TindaHub notification feed failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Notifications could not be refreshed. Please try again.']);
}
