<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    ensureDatabase();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $action = $input['action'] ?? ($_GET['action'] ?? '');

        if ($action === 'register') {
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $address = trim($input['address'] ?? '');

            if ($name === '' || $email === '' || $password === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required.']);
                exit;
            }

            $userId = registerUser($name, $email, $password, $phone, $address);
            $_SESSION['piku_user'] = $name;
            $_SESSION['piku_user_data'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Account created successfully!',
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                ]
            ]);
            exit;
        }

        if ($action === 'login') {
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');

            // 1. Check if credentials match an Admin account
            $admin = findAdminUser($email, $password);
            if ($admin) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_hub'] = $admin['hub_name'];
                $_SESSION['admin_hub_short'] = $admin['hub_short'];
                $_SESSION['admin_hub_code'] = $admin['hub_code'];
                $_SESSION['admin_avatar'] = $admin['avatar'];
                $_SESSION['admin_data'] = $admin;
                $_SESSION['piku_user'] = $admin['name'];
                $_SESSION['piku_user_data'] = [
                    'id' => $admin['id'] ?? 1,
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'role' => $admin['role'],
                    'is_admin' => true
                ];

                logAdminActivity($admin['id'] ?? null, $admin['username'], 'LOGIN', "Logged into Hub: {$admin['hub_short']} via API/Storefront");

                $token = hash('sha256', $admin['username'] . '_ranchimart_secret_2026');
                @setcookie('rm_admin_user', $admin['username'], time() + 86400 * 30, '/');
                @setcookie('rm_admin_token', $token, time() + 86400 * 30, '/');

                echo json_encode([
                    'status' => 'success',
                    'is_admin' => true,
                    'message' => "Welcome back, {$admin['name']}! Signed into {$admin['hub_short']}.",
                    'redirect' => '/admin.php',
                    'user' => [
                        'name' => $admin['name'],
                        'email' => $admin['email'],
                        'role' => $admin['role'],
                        'is_admin' => true
                    ]
                ]);
                exit;
            }

            // 2. Regular customer authentication
            $user = authenticateUser($email, $password);
            if (!$user) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid username/email or password.']);
                exit;
            }

            $_SESSION['piku_user'] = $user['name'];
            $_SESSION['piku_user_data'] = $user;

            echo json_encode([
                'status' => 'success',
                'is_admin' => false,
                'message' => 'Login successful!',
                'user' => $user
            ]);
            exit;
        }

        if ($action === 'create_order') {
            $orderCode = trim($input['order_code'] ?? '');
            if (!$orderCode) {
                $orderCode = 'RM-' . rand(100000, 999999);
            }
            $userEmail = trim($input['user_email'] ?? ($_SESSION['piku_user_data']['email'] ?? 'guest@ranchimart.com'));
            $customerName = trim($input['customer_name'] ?? '');
            $customerPhone = trim($input['customer_phone'] ?? '');
            $deliveryAddress = trim($input['delivery_address'] ?? '');
            $itemsJson = is_array($input['items'] ?? null) ? json_encode($input['items']) : trim($input['items_json'] ?? '[]');
            $totalAmount = (float)($input['total_amount'] ?? 0);
            $paymentMethod = trim($input['payment_method'] ?? 'cod');

            $status = trim($input['status'] ?? 'Confirmed');
            $originHub = trim($input['origin_hub_code'] ?? 'HUB-MAIN');
            $payStatus = trim($input['payment_status'] ?? ($paymentMethod === 'cod' ? 'Pending' : 'Paid'));
            $notes = trim($input['tracking_notes'] ?? 'Order placed and confirmed. Awaiting local hub dispatch.');

            $orderId = createOrder($orderCode, $userEmail, $customerName, $customerPhone, $deliveryAddress, $itemsJson, $totalAmount, $paymentMethod, $status, $originHub, $payStatus, $notes);

            echo json_encode([
                'status' => 'success',
                'message' => 'Order created successfully',
                'order' => [
                    'id' => $orderId,
                    'order_code' => $orderCode,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'delivery_address' => $deliveryAddress,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $payStatus,
                    'status' => $status,
                    'origin_hub_code' => $originHub,
                    'tracking_notes' => $notes
                ]
            ]);
            exit;
        }

        if ($action === 'track_order') {
            $orderCode = trim($input['order_code'] ?? '');
            $order = getOrderByCode($orderCode);
            if (!$order) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => "Order '$orderCode' not found. Please verify your Order ID."]);
                exit;
            }

            $order['items'] = json_decode($order['items_json'] ?? '[]', true) ?: [];
            foreach (getAllHubs() as $hub) {
                if (($hub['code'] ?? '') === ($order['origin_hub_code'] ?? '')) {
                    $order['hub_name'] = $hub['name'];
                    $order['hub_address'] = $hub['address'];
                    $order['hub_manager'] = $hub['manager_name'] . ' (' . $hub['manager_phone'] . ')';
                    break;
                }
            }
            echo json_encode([
                'status' => 'success',
                'order' => $order
            ]);
            exit;
        }

        if ($action === 'update_order_status') {
            if (empty($_SESSION['admin_logged_in'])) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Admin login is required to update order tracking.']);
                exit;
            }

            $orderId = (int)($input['order_id'] ?? 0);
            $newStatus = trim($input['status'] ?? '');
            $notes = isset($input['tracking_notes']) ? trim($input['tracking_notes']) : null;
            $hubCode = isset($input['origin_hub_code']) ? trim($input['origin_hub_code']) : null;

            if ($orderId <= 0 || $newStatus === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Valid order ID and status are required.']);
                exit;
            }

            updateOrderStatus($orderId, $newStatus, $notes, $hubCode);
            echo json_encode([
                'status' => 'success',
                'message' => "Order #$orderId status updated to '$newStatus'.",
                'order_id' => $orderId,
                'new_status' => $newStatus,
            ]);
            exit;
        }

        if ($action === 'get_orders') {
            $orders = getAllOrders();
            foreach ($orders as &$ord) {
                $ord['items'] = json_decode($ord['items_json'], true) ?: [];
            }
            unset($ord);
            echo json_encode([
                'status' => 'success',
                'orders' => $orders,
                'count' => count($orders),
            ]);
            exit;
        }

        if ($action === 'get_hubs') {
            $hubs = getAllHubs();
            echo json_encode([
                'status' => 'success',
                'hubs' => $hubs,
                'count' => count($hubs),
            ]);
            exit;
        }

        if ($action === 'get_order_stats') {
            $stats = getOrderStats();
            echo json_encode([
                'status' => 'success',
                'stats' => $stats
            ]);
            exit;
        }

        if ($action === 'get_admin_logs') {
            $logs = getRecentAdminLogs(30);
            echo json_encode([
                'status' => 'success',
                'logs' => $logs
            ]);
            exit;
        }

        if ($action === 'submit_review') {
            $productId = (int)($input['product_id'] ?? 0);
            $userName = trim($input['user_name'] ?? 'Verified Buyer');
            $rating = (int)($input['rating'] ?? 5);
            $title = trim($input['review_title'] ?? 'Great Product');
            $text = trim($input['review_text'] ?? '');

            if ($productId <= 0 || $text === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Product ID and review text are required.']);
                exit;
            }

            $reviewId = addProductReview($productId, $userName, $rating, $title, $text);
            echo json_encode([
                'status' => 'success',
                'message' => 'Review submitted successfully!',
                'review_id' => $reviewId
            ]);
            exit;
        }

        if ($action === 'get_reviews') {
            $productId = (int)($input['product_id'] ?? 0);
            $reviews = getProductReviews($productId);
            echo json_encode([
                'status' => 'success',
                'reviews' => $reviews,
                'count' => count($reviews)
            ]);
            exit;
        }

        if ($action === 'contact_inquiry') {
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $subject = trim($input['subject'] ?? 'General Inquiry');
            $message = trim($input['message'] ?? '');

            if ($name === '' || $email === '' || $message === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Name, email, and message are required.']);
                exit;
            }

            $inquiryId = submitContactInquiry($name, $email, $phone, $subject, $message);
            echo json_encode([
                'status' => 'success',
                'message' => 'Your message has been received! Our Ranchi Mart team will contact you shortly.',
                'inquiry_id' => $inquiryId
            ]);
            exit;
        }

        if ($action === 'add_product') {
            $name = trim($input['name'] ?? '');
            $categoryId = (int)($input['category_id'] ?? 1);
            $price = (float)($input['price'] ?? 0);
            $image = trim($input['image'] ?? '/frontend/photos/box1__image.png');
            $description = trim($input['description'] ?? '');
            $stock = (int)($input['stock'] ?? 20);

            if ($name === '' || $price <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Product name and valid price are required.']);
                exit;
            }

            $newId = addProduct($name, $categoryId, $price, $image, $description, $stock);
            $activeUser = $_SESSION['admin_data']['username'] ?? $_SESSION['admin_user'] ?? 'admin';
            $activeId = $_SESSION['admin_data']['id'] ?? null;
            logAdminActivity($activeId, $activeUser, 'ADD_PRODUCT', "Added product '{$name}' (ID: #{$newId})", 'product', $newId);

            echo json_encode([
                'status' => 'success',
                'message' => "Product '{$name}' added to catalog successfully!",
                'product_id' => $newId,
                'product' => [
                    'id' => $newId,
                    'name' => $name,
                    'category_id' => $categoryId,
                    'price' => $price,
                    'image' => $image,
                    'description' => $description,
                    'stock' => $stock
                ]
            ]);
            exit;
        }

        if ($action === 'delete_product') {
            $productId = (int)($input['product_id'] ?? 0);
            if ($productId <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Valid product ID is required.']);
                exit;
            }

            deleteProduct($productId);
            $activeUser = $_SESSION['admin_data']['username'] ?? $_SESSION['admin_user'] ?? 'admin';
            $activeId = $_SESSION['admin_data']['id'] ?? null;
            logAdminActivity($activeId, $activeUser, 'DELETE_PRODUCT', "Deleted product #{$productId}", 'product', $productId);

            echo json_encode([
                'status' => 'success',
                'message' => "Product #$productId has been deleted successfully."
            ]);
            exit;
        }

        if ($action === 'get_products') {
            $products = getFeaturedProducts();
            echo json_encode([
                'status' => 'success',
                'products' => $products,
                'count' => count($products),
            ]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
    }

    $getAction = $_GET['action'] ?? 'get_products';

    if ($getAction === 'get_hubs') {
        $hubs = getAllHubs();
        echo json_encode([
            'status' => 'success',
            'hubs' => $hubs,
            'count' => count($hubs),
        ]);
        exit;
    }

    if ($getAction === 'get_categories') {
        $cats = getAllCategories();
        echo json_encode([
            'status' => 'success',
            'categories' => $cats,
            'count' => count($cats),
        ]);
        exit;
    }

    if ($getAction === 'get_order_stats') {
        $stats = getOrderStats();
        echo json_encode([
            'status' => 'success',
            'stats' => $stats
        ]);
        exit;
    }

    if ($getAction === 'get_admin_logs') {
        $limit = (int)($_GET['limit'] ?? 25);
        $logs = getRecentAdminLogs($limit);
        echo json_encode([
            'status' => 'success',
            'logs' => $logs,
            'count' => count($logs)
        ]);
        exit;
    }

    if ($getAction === 'get_reviews') {
        $productId = (int)($_GET['product_id'] ?? 0);
        $reviews = getProductReviews($productId);
        echo json_encode([
            'status' => 'success',
            'reviews' => $reviews,
            'count' => count($reviews)
        ]);
        exit;
    }

    if ($getAction === 'track_order') {
        $orderCode = trim($_GET['order_code'] ?? ($_GET['query'] ?? ''));
        $order = getOrderByCode($orderCode);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => "Order '$orderCode' not found. Please verify your Order ID."]);
            exit;
        }
        $order['items'] = json_decode($order['items_json'] ?? '[]', true) ?: [];
        foreach (getAllHubs() as $hub) {
            if (($hub['code'] ?? '') === ($order['origin_hub_code'] ?? '')) {
                $order['hub_name'] = $hub['name'];
                $order['hub_address'] = $hub['address'];
                $order['hub_manager'] = $hub['manager_name'] . ' (' . $hub['manager_phone'] . ')';
                break;
            }
        }
        echo json_encode(['status' => 'success', 'order' => $order]);
        exit;
    }

    $products = getFeaturedProducts();
    echo json_encode([
        'status' => 'success',
        'products' => $products,
        'count' => count($products),
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
