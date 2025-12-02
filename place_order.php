<?php
require 'config.php';

header('Content-Type: application/json');

// Il faut être connecté pour passer une commande
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour passer une commande.'
    ]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['items']) || !is_array($data['items'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Données de commande invalides.'
    ]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$items = $data['items'];

try {
    $pdo->beginTransaction();

    $total = 0;
    $orderItems = [];

    $stmtProduct = $pdo->prepare("SELECT id, price FROM products WHERE id = ?");

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $qty       = (int)($item['quantity'] ?? 0);

        if ($productId <= 0 || $qty <= 0) {
            continue;
        }

        $stmtProduct->execute([$productId]);
        $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            continue;
        }

        $unitPrice = (float)$product['price'];
        $lineTotal = $unitPrice * $qty;
        $total    += $lineTotal;

        $orderItems[] = [
            'product_id' => $productId,
            'quantity'   => $qty,
            'unit_price' => $unitPrice
        ];
    }

    if ($total <= 0 || empty($orderItems)) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Aucun article valide dans le panier.'
        ]);
        exit;
    }

    // Insertion dans orders
    $stmtOrder = $pdo->prepare(
        "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'en_attente')"
    );
    $stmtOrder->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    // Insertion dans order_items
    $stmtItem = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($orderItems as $oi) {
        $stmtItem->execute([
            $orderId,
            $oi['product_id'],
            $oi['quantity'],
            $oi['unit_price']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success'  => true,
        'order_id' => $orderId
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors de la commande.'
    ]);
}
