<?php
require __DIR__ . '/db.php';
ensureDatabase();
$db = connectDatabase();
$count = (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo 'DB_OK:' . (file_exists(getDatabasePath()) ? 'yes' : 'no') . PHP_EOL;
echo 'COUNT:' . $count . PHP_EOL;
foreach ($db->query('SELECT id, name, price FROM products ORDER BY id LIMIT 3') as $row) {
}
echo 'ORDERS_COUNT:' . count(getAllOrders()) . PHP_EOL;
foreach (getAllOrders() as $row) {
    echo $row['id'] . ' ' . $row['order_code'] . ' ' . $row['customer_name'] . ' ' . $row['status'] . PHP_EOL;
}
