<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
require_once dirname(__DIR__) . '/backend/db.php';
ensureDatabase();

$adminPassword = 'admin123';
$adminUser = 'admin';

// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_user']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['admin_hub']);
    unset($_SESSION['admin_hub_short']);
    unset($_SESSION['admin_hub_code']);
    unset($_SESSION['admin_avatar']);
    unset($_SESSION['admin_data']);
    if (isset($_COOKIE['rm_admin_user'])) {
        @setcookie('rm_admin_user', '', time() - 86400, '/');
    }
    if (isset($_COOKIE['rm_admin_token'])) {
        @setcookie('rm_admin_token', '', time() - 86400, '/');
    }
    $message = 'Logged out successfully.';
    $messageType = 'success';
}

// Restore session from persistent cookie if available
if (empty($_SESSION['admin_logged_in']) && !isset($_GET['logout'])) {
    $cookieUser = $_COOKIE['rm_admin_user'] ?? '';
    $cookieToken = $_COOKIE['rm_admin_token'] ?? '';
    if ($cookieUser !== '' && $cookieToken !== '') {
        $expectedHash = hash('sha256', $cookieUser . '_ranchimart_secret_2026');
        if (hash_equals($expectedHash, $cookieToken)) {
            $restoredAdmin = findAdminUserByUsername($cookieUser);
            if ($restoredAdmin) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = $restoredAdmin['name'];
                $_SESSION['admin_role'] = $restoredAdmin['role'];
                $_SESSION['admin_hub'] = $restoredAdmin['hub_name'];
                $_SESSION['admin_hub_short'] = $restoredAdmin['hub_short'];
                $_SESSION['admin_hub_code'] = $restoredAdmin['hub_code'];
                $_SESSION['admin_avatar'] = $restoredAdmin['avatar'];
                $_SESSION['admin_data'] = $restoredAdmin;
            }
        }
    }
}

// Quick login query parameter support
if (empty($_SESSION['admin_logged_in']) && !empty($_GET['quick_login']) && !isset($_GET['logout'])) {
    $qlUser = trim($_GET['quick_login']);
    $qlPass = trim($_GET['pass'] ?? 'admin123');
    $adminData = findAdminUser($qlUser, $qlPass);
    if ($adminData) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $adminData['name'] ?? 'Admin';
        $_SESSION['admin_role'] = $adminData['role'] ?? 'Hub Operations Lead';
        $_SESSION['admin_hub'] = $adminData['hub_name'] ?? 'Ranchi Mart Central Headquarters';
        $_SESSION['admin_hub_short'] = $adminData['hub_short'] ?? 'Main HQ';
        $_SESSION['admin_hub_code'] = $adminData['hub_code'] ?? 'HQ-RANCHI';
        $_SESSION['admin_avatar'] = $adminData['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80';
        $_SESSION['admin_data'] = $adminData;

        $token = hash('sha256', ($adminData['username'] ?? 'admin') . '_ranchimart_secret_2026');
        @setcookie('rm_admin_user', $adminData['username'] ?? 'admin', time() + 86400 * 30, '/');
        @setcookie('rm_admin_token', $token, time() + 86400 * 30, '/');

        $message = "Welcome, " . ($adminData['name'] ?? 'Admin') . "!";
        $messageType = 'success';
    }
}

$message = $message ?? '';
$messageType = $messageType ?? '';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'POST') {
    // 1. Admin Login
    if (isset($_POST['admin_login']) || (isset($_POST['username']) && isset($_POST['password']) && !isset($_POST['create_admin']))) {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $adminData = findAdminUser($username, $password);
        if ($adminData) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $adminData['name'] ?? 'Admin';
            $_SESSION['admin_role'] = $adminData['role'] ?? 'Hub Operations Lead';
            $_SESSION['admin_hub'] = $adminData['hub_name'] ?? 'Ranchi Mart Central Headquarters';
            $_SESSION['admin_hub_short'] = $adminData['hub_short'] ?? 'Main HQ';
            $_SESSION['admin_hub_code'] = $adminData['hub_code'] ?? 'HQ-RANCHI';
            $_SESSION['admin_avatar'] = $adminData['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80';
            $_SESSION['admin_data'] = $adminData;

            logAdminActivity($adminData['id'] ?? null, $adminData['username'] ?? 'admin', 'LOGIN', "Logged into Hub: " . ($adminData['hub_short'] ?? 'Hub'));

            $token = hash('sha256', ($adminData['username'] ?? 'admin') . '_ranchimart_secret_2026');
            @setcookie('rm_admin_user', $adminData['username'] ?? 'admin', time() + 86400 * 30, '/');
            @setcookie('rm_admin_token', $token, time() + 86400 * 30, '/');

            $message = "Welcome back, " . ($adminData['name'] ?? 'Admin') . "! Signed into " . ($adminData['hub_short'] ?? 'Hub') . ".";
            $messageType = 'success';
        } else {
            $message = 'Invalid credentials. Please select one of the Hub Managers below or check your username/password.';
            $messageType = 'error';
        }
    }

    // 1b. Create New Admin Account
    if (isset($_POST['create_admin'])) {
        $regName = trim($_POST['name'] ?? '');
        $regUsername = strtolower(trim($_POST['username'] ?? ''));
        $regEmail = strtolower(trim($_POST['email'] ?? ''));
        $regPassword = trim($_POST['password'] ?? '');
        $regRole = trim($_POST['role'] ?? 'Hub Operations Lead');
        $regHubName = trim($_POST['hub_name'] ?? '');
        $regHubShort = trim($_POST['hub_short'] ?? '');
        $regPhone = trim($_POST['phone'] ?? '+91 98350 00000');
        $regAddress = trim($_POST['hub_address'] ?? 'Main Road, Ranchi');
        $regPincodes = trim($_POST['pincodes'] ?? '834001');
        $regAvatar = trim($_POST['avatar'] ?? '');
        $regBadge = trim($_POST['badge'] ?? 'Verified Hub Manager');

        if ($regName === '' || $regUsername === '' || $regEmail === '' || $regPassword === '') {
            $message = 'Please provide Full Name, Username, Email, and Password.';
            $messageType = 'error';
        } elseif (!filter_var($regEmail, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            try {
                if ($regHubShort === '') {
                    $regHubShort = $regName . ' Hub';
                }
                if ($regHubName === '') {
                    $regHubName = 'Ranchi Mart ' . $regHubShort;
                }
                if ($regAvatar === '') {
                    $regAvatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=300&q=80';
                }

                $newAdminId = createAdminUser([
                    'name' => $regName,
                    'username' => $regUsername,
                    'email' => $regEmail,
                    'password' => $regPassword,
                    'role' => $regRole,
                    'hub_name' => $regHubName,
                    'hub_short' => $regHubShort,
                    'phone' => $regPhone,
                    'hub_address' => $regAddress,
                    'pincodes' => $regPincodes,
                    'avatar' => $regAvatar,
                    'badge' => $regBadge,
                    'badge_color' => '#0284c7'
                ]);

                $adminData = findAdminUser($regUsername, $regPassword);
                if ($adminData) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = $adminData['name'];
                    $_SESSION['admin_role'] = $adminData['role'];
                    $_SESSION['admin_hub'] = $adminData['hub_name'];
                    $_SESSION['admin_hub_short'] = $adminData['hub_short'];
                    $_SESSION['admin_hub_code'] = $adminData['hub_code'];
                    $_SESSION['admin_avatar'] = $adminData['avatar'];
                    $_SESSION['admin_data'] = $adminData;

                    logAdminActivity($adminData['id'] ?? null, $regUsername, 'REGISTER_ADMIN', "New Admin account created: {$regName} ({$regUsername}) for {$regHubShort}");

                    $token = hash('sha256', $adminData['username'] . '_ranchimart_secret_2026');
                    @setcookie('rm_admin_user', $adminData['username'], time() + 86400 * 30, '/');
                    @setcookie('rm_admin_token', $token, time() + 86400 * 30, '/');

                    $message = "🎉 Admin Account for '{$adminData['name']}' created successfully! Welcome to {$adminData['hub_short']}.";
                    $messageType = 'success';
                }
            } catch (Throwable $e) {
                $message = 'Error creating admin account: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }

    // 1c. Switch Active Hub Manager
    if (isset($_POST['switch_admin'])) {
        $targetUser = trim($_POST['target_user'] ?? '');
        $adminData = findAdminUserByUsername($targetUser);
        if ($adminData) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $adminData['name'];
            $_SESSION['admin_role'] = $adminData['role'];
            $_SESSION['admin_hub'] = $adminData['hub_name'];
            $_SESSION['admin_hub_short'] = $adminData['hub_short'];
            $_SESSION['admin_hub_code'] = $adminData['hub_code'];
            $_SESSION['admin_avatar'] = $adminData['avatar'];
            $_SESSION['admin_data'] = $adminData;

            logAdminActivity($adminData['id'] ?? null, $adminData['username'], 'SWITCH_ADMIN', "Switched active manager profile to {$adminData['name']} ({$adminData['hub_short']})");

            $token = hash('sha256', $adminData['username'] . '_ranchimart_secret_2026');
            @setcookie('rm_admin_user', $adminData['username'], time() + 86400 * 30, '/');
            @setcookie('rm_admin_token', $token, time() + 86400 * 30, '/');

            $message = "Switched to Hub Manager: {$adminData['name']} ({$adminData['hub_short']})";
            $messageType = 'success';
        }
    }


    // 2. Add New Product
    if (isset($_POST['add_product'])) {
        if (empty($_SESSION['admin_logged_in'])) {
            $message = 'Please login as admin first.';
            $messageType = 'error';
        } else {
            $name = trim($_POST['name'] ?? '');
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $price = (float) ($_POST['price'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $stock = (int) ($_POST['stock'] ?? 0);
            $imagePreset = trim($_POST['image_preset'] ?? '');
            $imageUrl = trim($_POST['image_url'] ?? '');
            $image = '';

            // Handle file upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['image']['tmp_name'];
                $originalName = basename($_FILES['image']['name']);
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
                $uploadDir = dirname(__DIR__) . '/frontend/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $targetPath = $uploadDir . $safeName;
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $image = '/frontend/uploads/' . $safeName;
                }
            }

            // Fallback to direct image URL if provided
            if ($image === '' && $imageUrl !== '') {
                $image = $imageUrl;
            }

            // Fallback to preset or default
            if ($image === '') {
                $image = $imagePreset !== '' ? $imagePreset : '/frontend/photos/box1__image.png';
            }

            if ($name === '' || $categoryId <= 0 || $price <= 0 || $stock < 0) {
                $message = 'Please fill in valid product details (Name, Category, and Price are required).';
                $messageType = 'error';
            } else {
                $newId = addProduct($name, $categoryId, $price, $image, $description, $stock);
                $activeUser = $_SESSION['admin_data']['username'] ?? $_SESSION['admin_user'] ?? 'admin';
                $activeId = $_SESSION['admin_data']['id'] ?? null;
                logAdminActivity($activeId, $activeUser, 'ADD_PRODUCT', "Added product '{$name}' (ID: #{$newId}, Price: ₹{$price}, Stock: {$stock})", 'product', $newId);
                $message = "Product \"$name\" (ID: #$newId) added to catalog successfully!";
                $messageType = 'success';
            }
        }
    }

    // 3. Delete Product
    if (isset($_POST['delete_product'])) {
        if (empty($_SESSION['admin_logged_in'])) {
            $message = 'Please login as admin first.';
            $messageType = 'error';
        } else {
            $productId = (int) ($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                deleteProduct($productId);
                $activeUser = $_SESSION['admin_data']['username'] ?? $_SESSION['admin_user'] ?? 'admin';
                $activeId = $_SESSION['admin_data']['id'] ?? null;
                logAdminActivity($activeId, $activeUser, 'DELETE_PRODUCT', "Deleted product #{$productId} from catalog", 'product', $productId);
                $message = "Product #$productId has been deleted successfully.";
                $messageType = 'success';
            } else {
                $message = 'Invalid product selected.';
                $messageType = 'error';
            }
        }
    }

    // 4. Update Order Status / Confirm Order
    if (isset($_POST['update_order_status'])) {
        if (empty($_SESSION['admin_logged_in'])) {
            $message = 'Please login as admin first.';
            $messageType = 'error';
        } else {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $orderCode = trim($_POST['order_code'] ?? '');
            $newStatus = trim($_POST['status'] ?? 'Confirmed');
            $hubCode = trim($_POST['hub_code'] ?? '');
            $notes = trim($_POST['tracking_notes'] ?? '');

            if ($orderId > 0 && $newStatus !== '') {
                updateOrderStatus($orderId, $newStatus, $notes !== '' ? $notes : null, $hubCode !== '' ? $hubCode : null);
                $codeDisplay = $orderCode ? "#$orderCode" : "#$orderId";
                $activeUser = $_SESSION['admin_data']['username'] ?? $_SESSION['admin_user'] ?? 'admin';
                $activeId = $_SESSION['admin_data']['id'] ?? null;
                logAdminActivity($activeId, $activeUser, 'UPDATE_ORDER_STATUS', "Order {$codeDisplay} updated to: \"{$newStatus}\"" . ($hubCode ? " (Assigned Hub: {$hubCode})" : ''), 'order', $orderId);
                $message = "Order $codeDisplay successfully updated to: \"$newStatus\"!";
                $messageType = 'success';
            } else {
                $message = 'Invalid order or status selected.';
                $messageType = 'error';
            }
        }
    }

    // 5. Delete Order
    if (isset($_POST['delete_order'])) {
        if (empty($_SESSION['admin_logged_in'])) {
            $message = 'Please login as admin first.';
            $messageType = 'error';
        } else {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            if ($orderId > 0) {
                deleteOrder($orderId);
                $activeUser = $_SESSION['admin_data']['username'] ?? $_SESSION['admin_user'] ?? 'admin';
                $activeId = $_SESSION['admin_data']['id'] ?? null;
                logAdminActivity($activeId, $activeUser, 'DELETE_ORDER', "Deleted order #{$orderId}", 'order', $orderId);
                $message = "Order #$orderId has been deleted.";
                $messageType = 'success';
            }
        }
    }
}

$isAdminLoggedIn = !empty($_SESSION['admin_logged_in']);
$categories = getAllCategories();
$products = getAllProducts();
$orders = getAllOrders();
$hubs = getAllHubs();
$dummyAdmins = getDummyAdmins();
$adminLogs = getRecentAdminLogs(50);
$allInquiries = getAllContactInquiries(20);
$orderStats = getOrderStats();

// Live database health and table metrics
try {
    $dbPdo = getDatabaseConnection();
    $sqliteVer = $dbPdo->query("SELECT sqlite_version()")->fetchColumn() ?: '3.x';
    $journalMode = $dbPdo->query("PRAGMA journal_mode")->fetchColumn() ?: 'wal';
    $dbFile = dirname(__DIR__) . '/backend/shop.db';
    $dbSizeBytes = file_exists($dbFile) ? filesize($dbFile) : 0;
    $dbSizeKb = round($dbSizeBytes / 1024, 1);
    $tableCounts = [
        'products' => (int)$dbPdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'orders' => (int)$dbPdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'categories' => (int)$dbPdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        'users' => (int)$dbPdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'admins' => (int)$dbPdo->query("SELECT COUNT(*) FROM admins")->fetchColumn(),
        'admin_logs' => (int)$dbPdo->query("SELECT COUNT(*) FROM admin_logs")->fetchColumn(),
        'product_reviews' => (int)$dbPdo->query("SELECT COUNT(*) FROM product_reviews")->fetchColumn(),
        'contact_inquiries' => (int)$dbPdo->query("SELECT COUNT(*) FROM contact_inquiries")->fetchColumn(),
    ];
    $tablesList = $dbPdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $indexesList = $dbPdo->query("SELECT name, tbl_name FROM sqlite_master WHERE type='index' AND name NOT LIKE 'sqlite_%' ORDER BY tbl_name, name")->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $sqliteVer = '3.x';
    $journalMode = 'wal';
    $dbSizeKb = 0;
    $tableCounts = [];
    $tablesList = ['products', 'categories', 'orders', 'users', 'admins', 'admin_logs', 'product_reviews', 'contact_inquiries'];
    $indexesList = [];
}

$activeAdmin = $_SESSION['admin_data'] ?? [
    'username' => 'rajesh',
    'name' => $_SESSION['admin_user'] ?? 'Rajesh Sharma',
    'role' => $_SESSION['admin_role'] ?? 'Senior Operations Head & Hub Director',
    'hub_name' => $_SESSION['admin_hub'] ?? 'Central Ranchi Super Hub & Superstore',
    'hub_short' => $_SESSION['admin_hub_short'] ?? 'Main Road Hub',
    'hub_code' => $_SESSION['admin_hub_code'] ?? 'HUB-MAIN',
    'avatar' => $_SESSION['admin_avatar'] ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
    'badge' => 'Verified Super Hub'
];

// Compute Quick Stats
$totalOrdersCount = count($orders);
$pendingOrdersCount = count(array_filter($orders, fn($o) => strtolower($o['status']) === 'pending'));
$confirmedOrdersCount = count(array_filter($orders, fn($o) => in_array(strtolower($o['status']), ['confirmed', 'packed', 'in transit', 'out for delivery'])));
$deliveredOrdersCount = count(array_filter($orders, fn($o) => strtolower($o['status']) === 'delivered'));
$totalRevenue = array_reduce($orders, fn($sum, $o) => $sum + (float)($o['total_amount'] ?? 0), 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Ranchi Mart</title>
  <link rel="stylesheet" href="/frontend/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..800;1,9..40,400..800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="admin-body">
  <?php if (!$isAdminLoggedIn): 
    $initialAuthTab = (isset($_POST['create_admin']) && $messageType === 'error') ? 'register' : 'login';
  ?>
    <!-- Admin Login Screen -->
    <div class="admin-page">
      <div class="admin-login-card">
        <div class="admin-login-brand">
          <span class="admin-login-icon">⚡</span>
          <h2>Ranchi Mart Admin Portal</h2>
          <p>Fulfillment Hub Management &amp; Catalog Control Center</p>
        </div>

        <!-- Auth Tabs Switcher -->
        <div class="admin-auth-tabs">
          <button type="button" class="auth-tab-btn <?= $initialAuthTab === 'login' ? 'is-active' : '' ?>" id="tabBtnLogin" onclick="switchAuthTab('login')">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In as Admin
          </button>
          <button type="button" class="auth-tab-btn <?= $initialAuthTab === 'register' ? 'is-active' : '' ?>" id="tabBtnRegister" onclick="switchAuthTab('register')">
            <i class="fa-solid fa-user-plus"></i> Create New Admin
          </button>
        </div>

        <!-- Flash Notice -->
        <?php if ($message !== ''): ?>
          <div class="admin-message <?= htmlspecialchars($messageType) ?>" style="margin-bottom: 18px;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <!-- PANEL 1: EXISTING ADMIN SIGN IN -->
        <div id="panelLogin" class="auth-panel" style="<?= $initialAuthTab === 'login' ? 'display: block;' : 'display: none;' ?>">
          <!-- 5 Verified Hub Managers Quick Selector -->
          <div class="admin-quick-managers">
            <p class="quick-mgr-title"><strong>⚡ 1-Click Instant Login (Click Any Manager Card to Enter):</strong></p>
            <div class="mgr-quick-grid">
              <a href="?quick_login=admin&pass=admin123" class="mgr-quick-btn mgr-hq-btn" style="text-decoration: none; cursor: pointer;">
                <span class="hq-icon">👑</span>
                <div>
                  <strong>Headquarters Admin</strong>
                  <span>Master HQ (admin / admin123)</span>
                </div>
              </a>
              <a href="?quick_login=rajesh&pass=admin123" class="mgr-quick-btn active-mgr" style="text-decoration: none; cursor: pointer;">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Rajesh Sharma" />
                <div>
                  <strong>Rajesh Sharma</strong>
                  <span>Main Road Super Hub</span>
                </div>
              </a>
              <a href="?quick_login=pooja&pass=admin123" class="mgr-quick-btn" style="text-decoration: none; cursor: pointer;">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=100&q=80" alt="Pooja Verma" />
                <div>
                  <strong>Pooja Verma</strong>
                  <span>Lalpur Express Hub</span>
                </div>
              </a>
              <a href="?quick_login=amitabh&pass=admin123" class="mgr-quick-btn" style="text-decoration: none; cursor: pointer;">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt="Amitabh Roy" />
                <div>
                  <strong>Amitabh Roy</strong>
                  <span>Doranda Regional Hub</span>
                </div>
              </a>
              <a href="?quick_login=sunita&pass=admin123" class="mgr-quick-btn" style="text-decoration: none; cursor: pointer;">
                <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=100&q=80" alt="Sunita Singh" />
                <div>
                  <strong>Sunita Singh</strong>
                  <span>Kanke Quick Store</span>
                </div>
              </a>
            </div>
          </div>

          <form class="admin-form" method="POST" id="adminLoginForm" action="">
            <input type="hidden" name="admin_login" value="1" />
            <label>
              Admin Username or Email
              <input type="text" name="username" id="adminUsernameInput" value="admin" required placeholder="admin or manager username" autofocus />
            </label>

            <label>
              Password
              <div class="password-wrapper">
                <input type="password" name="password" id="adminPasswordInput" value="admin123" placeholder="admin123" required />
                <button type="button" class="btn-toggle-eye" onclick="togglePassVisibility('adminPasswordInput', this)" title="Show/Hide Password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </label>

            <button type="submit" name="admin_login" class="admin-submit-btn">
              Sign In to Admin Dashboard <span>→</span>
            </button>
          </form>

          <div class="admin-login-hint">
            <p><strong>Available Credentials:</strong></p>
            <code>rajesh / pooja / amitabh / sunita / admin (Password: admin123)</code>
          </div>
        </div>

        <!-- PANEL 2: CREATE NEW ADMIN ACCOUNT -->
        <div id="panelRegister" class="auth-panel" style="<?= $initialAuthTab === 'register' ? 'display: block;' : 'display: none;' ?>">
          <div class="reg-panel-intro">
            <h3><i class="fa-solid fa-user-shield"></i> Register New Hub Admin</h3>
            <p>Create credentials for a new store manager or local fulfillment center.</p>
          </div>

          <form class="admin-form" method="POST" id="adminRegisterForm">
            <div class="admin-form-grid-2">
              <label>
                Full Name *
                <input type="text" name="name" required placeholder="e.g. Alok Verma" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
              </label>

              <label>
                Admin Username *
                <input type="text" name="username" required placeholder="e.g. alok" pattern="[a-zA-Z0-9_-]+" title="Letters, numbers, underscores or hyphens only" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
              </label>
            </div>

            <div class="admin-form-grid-2">
              <label>
                Official Email *
                <input type="email" name="email" required placeholder="e.g. alok@ranchimart.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
              </label>

              <label>
                Password *
                <div class="password-wrapper">
                  <input type="password" name="password" id="regPasswordInput" required placeholder="Enter password" value="<?= htmlspecialchars($_POST['password'] ?? 'admin123') ?>" />
                  <button type="button" class="btn-toggle-eye" onclick="togglePassVisibility('regPasswordInput', this)" title="Show/Hide Password">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
              </label>
            </div>

            <div class="admin-form-grid-2">
              <label>
                Role / Title
                <select name="role">
                  <option value="Senior Operations Head & Hub Director">Senior Operations Head & Hub Director</option>
                  <option value="Hub Operations Lead" selected>Hub Operations Lead</option>
                  <option value="Warehouse & Inventory Lead">Warehouse & Inventory Lead</option>
                  <option value="Quick Commerce Store Manager">Quick Commerce Store Manager</option>
                  <option value="Dispatch & Logistics Head">Dispatch & Logistics Head</option>
                </select>
              </label>

              <label>
                Contact Phone
                <input type="text" name="phone" placeholder="+91 98350 12345" value="<?= htmlspecialchars($_POST['phone'] ?? '+91 98350 ') ?>" />
              </label>
            </div>

            <div class="admin-form-grid-2">
              <label>
                Operating Hub Full Name
                <input type="text" name="hub_name" placeholder="e.g. Ranchi Mart Harmu Super Hub" value="<?= htmlspecialchars($_POST['hub_name'] ?? '') ?>" />
              </label>

              <label>
                Hub Short Title
                <input type="text" name="hub_short" placeholder="e.g. Harmu Hub" value="<?= htmlspecialchars($_POST['hub_short'] ?? '') ?>" />
              </label>
            </div>

            <div class="admin-form-grid-2">
              <label>
                Pincodes Covered
                <input type="text" name="pincodes" placeholder="e.g. 834002, 834012" value="<?= htmlspecialchars($_POST['pincodes'] ?? '834002') ?>" />
              </label>

              <label>
                Badge Tag
                <select name="badge">
                  <option value="Verified Hub Manager" selected>Verified Hub Manager</option>
                  <option value="Express Dispatch Center">Express Dispatch Center</option>
                  <option value="Quick Commerce Store">Quick Commerce Store</option>
                  <option value="Regional Super Hub">Regional Super Hub</option>
                </select>
              </label>
            </div>

            <label>
              Hub Physical Address
              <input type="text" name="hub_address" placeholder="e.g. Argora Bypass Road, Harmu, Ranchi - 834002" value="<?= htmlspecialchars($_POST['hub_address'] ?? '') ?>" />
            </label>

            <!-- Avatar Preset Selector -->
            <label>
              Choose Profile Avatar
              <div class="avatar-preset-selector">
                <input type="hidden" name="avatar" id="regAvatarInput" value="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80" />
                <button type="button" class="avatar-chip is-selected" onclick="selectRegAvatar('https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80', this)">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Avatar 1" />
                </button>
                <button type="button" class="avatar-chip" onclick="selectRegAvatar('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80', this)">
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Avatar 2" />
                </button>
                <button type="button" class="avatar-chip" onclick="selectRegAvatar('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80', this)">
                  <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=100&q=80" alt="Avatar 3" />
                </button>
                <button type="button" class="avatar-chip" onclick="selectRegAvatar('https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80', this)">
                  <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt="Avatar 4" />
                </button>
                <button type="button" class="avatar-chip" onclick="selectRegAvatar('https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=300&q=80', this)">
                  <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=100&q=80" alt="Avatar 5" />
                </button>
              </div>
            </label>

            <button type="submit" name="create_admin" class="admin-submit-btn reg-btn">
              <i class="fa-solid fa-user-plus"></i> Create Admin Account &amp; Sign In <span>→</span>
            </button>
          </form>
        </div>

        <script>
          function switchAuthTab(tab) {
            const btnLogin = document.getElementById('tabBtnLogin');
            const btnReg = document.getElementById('tabBtnRegister');
            const panelLogin = document.getElementById('panelLogin');
            const panelReg = document.getElementById('panelRegister');

            if (tab === 'register') {
              btnLogin.classList.remove('is-active');
              btnReg.classList.add('is-active');
              panelLogin.style.display = 'none';
              panelReg.style.display = 'block';
            } else {
              btnReg.classList.remove('is-active');
              btnLogin.classList.add('is-active');
              panelReg.style.display = 'none';
              panelLogin.style.display = 'block';
            }
          }

          function selectAdminQuick(user, pass, btn) {
            document.getElementById('adminUsernameInput').value = user;
            document.getElementById('adminPasswordInput').value = pass;
            document.querySelectorAll('.mgr-quick-btn').forEach(b => b.classList.remove('active-mgr'));
            if (btn) btn.classList.add('active-mgr');
          }

          function togglePassVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            btn.innerHTML = isPass ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
          }

          function selectRegAvatar(url, btn) {
            document.getElementById('regAvatarInput').value = url;
            document.querySelectorAll('.avatar-chip').forEach(b => b.classList.remove('is-selected'));
            if (btn) btn.classList.add('is-selected');
          }
        </script>

        <div class="admin-back-store">
          <a href="/">← Back to Ranchi Mart Storefront</a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Admin Dashboard Main Panel -->
    <div class="admin-page">
      <div class="admin-shell">
        
        <!-- Header -->
        <header class="admin-header">
          <div class="admin-brand-info">
            <span class="admin-badge-tag">ADMIN PORTAL • <?= htmlspecialchars($activeAdmin['badge'] ?? 'VERIFIED HUB') ?></span>
            <h1>Ranchi Mart Management</h1>
            <p class="admin-subtext">Operating Hub: <strong><?= htmlspecialchars($activeAdmin['hub_name'] ?? 'Main Store') ?></strong></p>
          </div>

          <div class="admin-active-profile">
            <img src="<?= htmlspecialchars($activeAdmin['avatar'] ?? '') ?>" alt="Avatar" class="admin-avatar-top" />
            <div class="admin-meta-top">
              <strong><?= htmlspecialchars($activeAdmin['name'] ?? 'Admin') ?></strong>
              <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($activeAdmin['hub_short'] ?? 'Ranchi') ?></span>
              <small class="admin-role-badge"><?= htmlspecialchars($activeAdmin['role'] ?? 'Operations') ?></small>
            </div>
          </div>

          <div class="admin-actions">
            <a href="/" class="admin-store-btn" target="_blank">
              <i class="fa-solid fa-store"></i> View Storefront ↗
            </a>
            <a href="?logout=1" class="admin-logout-btn">
              <i class="fa-solid fa-arrow-right-from-bracket"></i> Switch / Logout
            </a>
          </div>
        </header>

        <!-- Flash Notice -->
        <?php if ($message !== ''): ?>
          <div class="admin-message <?= htmlspecialchars($messageType) ?>" id="adminFlashMsg">
            <strong><?= $messageType === 'success' ? '✓ Success:' : '⚠ Notice:' ?></strong>
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <!-- KPI Stats Bar -->
        <div class="admin-stats-grid">
          <div class="admin-stat-card">
            <div class="admin-stat-icon icon-orders"><i class="fa-solid fa-boxes-packing"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Total Orders</span>
              <strong class="admin-stat-value"><?= $totalOrdersCount ?></strong>
              <small class="admin-stat-sub">Lifetime customer orders</small>
            </div>
          </div>

          <div class="admin-stat-card <?= $pendingOrdersCount > 0 ? 'stat-alert-pending' : '' ?>">
            <div class="admin-stat-icon icon-pending"><i class="fa-solid fa-clock"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Pending Orders</span>
              <strong class="admin-stat-value text-amber"><?= $pendingOrdersCount ?></strong>
              <small class="admin-stat-sub"><?= $pendingOrdersCount > 0 ? 'Requires confirmation!' : 'All orders processed' ?></small>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon icon-transit"><i class="fa-solid fa-truck-fast"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Active / Confirmed</span>
              <strong class="admin-stat-value"><?= $confirmedOrdersCount ?></strong>
              <small class="admin-stat-sub">Confirmed & in transit</small>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon icon-products"><i class="fa-solid fa-shirt"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Total Products</span>
              <strong class="admin-stat-value"><?= count($products) ?></strong>
              <small class="admin-stat-sub">Catalog items listed</small>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon icon-revenue"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Gross Revenue</span>
              <strong class="admin-stat-value">₹<?= number_format($totalRevenue, 2) ?></strong>
              <small class="admin-stat-sub">Across all orders</small>
            </div>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <nav class="admin-tab-nav" aria-label="Admin Navigation">
          <button type="button" class="admin-tab-btn is-active" onclick="switchAdminTab('tabOrders')" id="btnTabOrders">
            <i class="fa-solid fa-receipt"></i> Orders &amp; Confirmations
            <?php if ($pendingOrdersCount > 0): ?>
              <span class="tab-counter-badge"><?= $pendingOrdersCount ?></span>
            <?php endif; ?>
          </button>
          <button type="button" class="admin-tab-btn" onclick="switchAdminTab('tabHubs')" id="btnTabHubs">
            <i class="fa-solid fa-shop"></i> Store Hubs &amp; Admins (<?= count($dummyAdmins) ?>)
          </button>
          <button type="button" class="admin-tab-btn" onclick="switchAdminTab('tabAddProduct')" id="btnTabAddProduct">
            <i class="fa-solid fa-plus-circle"></i> Add New Product
          </button>
          <button type="button" class="admin-tab-btn" onclick="switchAdminTab('tabProducts')" id="btnTabProducts">
            <i class="fa-solid fa-layer-group"></i> Manage Products (<?= count($products) ?>)
          </button>
          <button type="button" class="admin-tab-btn" onclick="switchAdminTab('tabDatabase')" id="btnTabDatabase">
            <i class="fa-solid fa-database"></i> Database &amp; Logs (<?= count($adminLogs) ?>)
          </button>
        </nav>

        <!-- TAB 1: ORDERS & CONFIRMATIONS -->
        <section id="tabOrders" class="admin-tab-content active-content">
          <div class="admin-section-header">
            <div>
              <h2>Customer Orders &amp; Dispatch Radar</h2>
              <p class="section-desc">Confirm new incoming orders and update delivery tracking status for customers.</p>
            </div>
            <div class="admin-filter-pills">
              <button type="button" class="filter-pill is-active" onclick="filterOrderRows('all', this)">All (<?= $totalOrdersCount ?>)</button>
              <button type="button" class="filter-pill pill-pending" onclick="filterOrderRows('pending', this)">Pending (<?= $pendingOrdersCount ?>)</button>
              <button type="button" class="filter-pill" onclick="filterOrderRows('confirmed', this)">Confirmed</button>
              <button type="button" class="filter-pill" onclick="filterOrderRows('packed', this)">Packed</button>
              <button type="button" class="filter-pill" onclick="filterOrderRows('transit', this)">Out for Delivery</button>
              <button type="button" class="filter-pill" onclick="filterOrderRows('delivered', this)">Delivered</button>
            </div>
          </div>

          <?php if (empty($orders)): ?>
            <div class="admin-empty-state">
              <i class="fa-solid fa-box-open"></i>
              <h3>No orders yet</h3>
              <p>When a customer places an order from the store, it will immediately appear here for confirmation.</p>
            </div>
          <?php else: ?>
            <div class="admin-orders-list">
              <?php foreach ($orders as $order): 
                $items = json_decode($order['items_json'] ?? '[]', true) ?: [];
                $statusNorm = strtolower($order['status'] ?? 'pending');
                $codeClean = ltrim($order['order_code'] ?? '', '#');
                $isPending = ($statusNorm === 'pending');
              ?>
                <div class="admin-order-card" data-order-status="<?= htmlspecialchars($statusNorm) ?>">
                  <div class="order-card-top">
                    <div class="order-id-group">
                      <strong class="order-code-badge">#<?= htmlspecialchars($codeClean) ?></strong>
                      <span class="order-timestamp">
                        <i class="fa-regular fa-calendar"></i>
                        <?= htmlspecialchars($order['created_at'] ?? 'Recently') ?>
                      </span>
                    </div>

                    <div class="order-status-badge-wrap">
                      <span class="status-badge status-<?= htmlspecialchars($statusNorm) ?>">
                        ● <?= htmlspecialchars($order['status']) ?>
                      </span>
                      <span class="payment-method-tag">
                        <?= strtoupper($order['payment_method'] ?? 'COD') === 'COD' ? '💵 Cash on Delivery' : '💳 Online Card' ?>
                      </span>
                      <?php if (!empty($order['origin_hub_code'])): ?>
                        <span class="hub-pill-mini" title="Origin Dispatch Hub">
                          <i class="fa-solid fa-warehouse"></i> <?= htmlspecialchars($order['origin_hub_code']) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($order['payment_status'])): ?>
                        <span class="payment-status-pill <?= strtolower($order['payment_status']) === 'paid' ? 'paid' : 'unpaid' ?>">
                          <?= htmlspecialchars($order['payment_status']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="order-card-body-grid">
                    <!-- Customer Details -->
                    <div class="customer-info-box">
                      <div class="customer-info-header">
                        <i class="fa-solid fa-user"></i>
                        <strong><?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?></strong>
                      </div>
                      <div class="customer-phone-row">
                        <i class="fa-solid fa-phone"></i>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone'] ?? '') ?>"><?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></a>
                      </div>
                      <div class="customer-address-row">
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?= htmlspecialchars($order['delivery_address'] ?? 'Ranchi, Jharkhand') ?></span>
                      </div>
                    </div>

                    <!-- Ordered Items Preview -->
                    <div class="ordered-items-box">
                      <span class="items-heading">Items Ordered (<?= count($items) ?>):</span>
                      <div class="items-list">
                        <?php if (empty($items)): ?>
                          <div class="item-preview-row"><span>General Mart Item</span><strong>₹<?= number_format((float)$order['total_amount'], 2) ?></strong></div>
                        <?php else: ?>
                          <?php foreach ($items as $it): ?>
                            <div class="item-preview-row">
                              <span><?= htmlspecialchars($it['name'] ?? 'Item') ?> × <?= (int)($it['quantity'] ?? 1) ?></span>
                              <strong>₹<?= number_format(((float)($it['price'] ?? 0) * (int)($it['quantity'] ?? 1)), 2) ?></strong>
                            </div>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>
                      <div class="order-total-row">
                        <span>Total Payable:</span>
                        <strong class="total-price">₹<?= number_format((float)$order['total_amount'], 2) ?></strong>
                      </div>
                    </div>
                  </div>

                  <!-- Order Action Bar (Confirm Order / Change Status / Delete) -->
                  <div class="order-actions-bar">
                    <?php if ($isPending): ?>
                      <!-- Direct Quick Confirm Button for Pending Orders -->
                      <form method="POST" class="inline-confirm-form">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>" />
                        <input type="hidden" name="order_code" value="<?= htmlspecialchars($codeClean) ?>" />
                        <input type="hidden" name="status" value="Confirmed" />
                        <button type="submit" name="update_order_status" class="admin-confirm-order-btn">
                          <i class="fa-solid fa-circle-check"></i> Confirm Order
                        </button>
                      </form>
                    <?php endif; ?>

                    <!-- Status Change Dropdown -->
                    <form method="POST" class="admin-status-form">
                      <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>" />
                      <input type="hidden" name="order_code" value="<?= htmlspecialchars($codeClean) ?>" />
                      <label class="status-select-label">
                        <span>Status:</span>
                        <select name="status" class="admin-status-dropdown">
                          <option value="Pending" <?= $statusNorm === 'pending' ? 'selected' : '' ?>>Pending Confirmation</option>
                          <option value="Confirmed" <?= $statusNorm === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                          <option value="Packed &amp; Dispatched" <?= str_contains($statusNorm, 'pack') ? 'selected' : '' ?>>Packed &amp; Dispatched</option>
                          <option value="Out for Delivery" <?= str_contains($statusNorm, 'out') || str_contains($statusNorm, 'transit') ? 'selected' : '' ?>>Out for Delivery</option>
                          <option value="Delivered" <?= $statusNorm === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                          <option value="Cancelled" <?= $statusNorm === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                      </label>
                      <label class="status-select-label">
                        <span>Dispatch Hub:</span>
                        <select name="hub_code" class="admin-status-dropdown hub-select">
                          <option value="">Auto / Default Hub</option>
                          <?php foreach ($hubs as $hb): ?>
                            <option value="<?= htmlspecialchars($hb['code']) ?>" <?= (($order['origin_hub_code'] ?? '') === $hb['code']) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($hb['short_name']) ?> (<?= htmlspecialchars($hb['code']) ?>)
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </label>
                      <label class="status-select-label">
                        <span>Delivery Update / Live Note (Customer Track Order me dikhega):</span>
                        <input type="text" name="tracking_notes" maxlength="240" value="<?= htmlspecialchars($order['tracking_notes'] ?? '') ?>" placeholder="e.g. Reached Kanke sorting center, out for delivery" />
                      </label>
                      <button type="submit" name="update_order_status" class="admin-btn-secondary" title="Save status & delivery notes"><i class="fa-solid fa-arrows-rotate"></i> Update Status</button>
                      <a href="/frontend/index.html?track=<?= urlencode($codeClean) ?>" target="_blank" class="admin-track-preview-btn" title="View live customer tracking screen for this order">
                        <i class="fa-solid fa-satellite-dish"></i> Customer View ↗
                      </a>
                    </form>

                    <!-- Delete Order Form -->
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete order #<?= htmlspecialchars($codeClean) ?>?');">
                      <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>" />
                      <button type="submit" name="delete_order" class="admin-btn-danger" title="Delete order">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <!-- TAB 2: ADD NEW PRODUCT -->
        <section id="tabAddProduct" class="admin-tab-content">
          <div class="admin-section-header">
            <div>
              <h2>Add New Product to Store Catalog</h2>
              <p class="section-desc">Create a new product listing with title, category, price, stock, and photo.</p>
            </div>
          </div>

          <div class="admin-add-product-wrapper">
            <form class="admin-form-modern" method="POST" enctype="multipart/form-data">
              <div class="form-row-two">
                <label>
                  <span>Product Title / Name *</span>
                  <input type="text" name="name" placeholder="e.g. Wireless Noise-Cancelling Headphones" required />
                </label>

                <label>
                  <span>Product Category *</span>
                  <select name="category_id" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
              </div>

              <div class="form-row-two">
                <label>
                  <span>Price (₹ INR) *</span>
                  <input type="number" name="price" step="0.01" min="1" placeholder="e.g. 1499.00" required />
                </label>

                <label>
                  <span>Stock Quantity *</span>
                  <input type="number" name="stock" min="0" value="25" placeholder="e.g. 25" required />
                </label>
              </div>

              <div class="form-row-two">
                <label>
                  <span>Upload Product Photo</span>
                  <input type="file" name="image" accept="image/*" />
                  <small class="field-hint">Upload an image file (PNG, JPG, WebP)</small>
                </label>

                <label>
                  <span>Or Enter Online Image URL</span>
                  <input type="url" name="image_url" placeholder="https://images.unsplash.com/photo-..." />
                  <small class="field-hint">Paste direct online photo link (Unsplash, web URL, etc.)</small>
                </label>
              </div>

              <label>
                <span>Or Pick Standard Mart Image Preset</span>
                <select name="image_preset">
                  <option value="/frontend/photos/box1__image.png">Health &amp; Personal Care (Box 1)</option>
                  <option value="/frontend/photos/box2_image.png">Beauty Picks (Box 2)</option>
                  <option value="/frontend/photos/box3_image.png">Electronics (Box 3)</option>
                  <option value="/frontend/photos/box4_image.png">Clothes &amp; Apparel (Box 4)</option>
                  <option value="/frontend/photos/box5_image.png">Furniture &amp; Home (Box 5)</option>
                  <option value="/frontend/photos/box6_images.png">Hair Accessories (Box 6)</option>
                  <option value="/frontend/photos/box9_images.png">Home &amp; Kitchen (Box 9)</option>
                  <option value="/frontend/photos/box10_images.png">Books &amp; Stationary (Box 10)</option>
                </select>
              </label>

              <label>
                <span>Product Description</span>
                <textarea name="description" rows="3" placeholder="Provide product features, highlights, and materials..."></textarea>
              </label>

              <div class="form-actions">
                <button type="submit" name="add_product" class="admin-submit-btn-large">
                  <i class="fa-solid fa-plus"></i> Add Product to Ranchi Mart
                </button>
              </div>
            </form>
          </div>
        </section>

        <!-- TAB 3: MANAGE PRODUCTS (VIEW & DELETE) -->
        <section id="tabProducts" class="admin-tab-content">
          <div class="admin-section-header">
            <div>
              <h2>Current Store Products (<?= count($products) ?>)</h2>
              <p class="section-desc">View existing catalog items, review pricing, stock levels, and delete old products.</p>
            </div>
            <div class="admin-search-wrap">
              <input type="text" id="productSearchInput" onkeyup="filterProductsTable()" placeholder="Search products by name or category..." />
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table" id="productsTable">
              <thead>
                <tr>
                  <th style="width: 60px;">Image</th>
                  <th style="width: 60px;">ID</th>
                  <th>Product Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th style="width: 100px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($products)): ?>
                  <tr>
                    <td colspan="7" style="text-align: center; padding: 30px;">No products found in the catalog.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($products as $p): ?>
                    <tr class="product-data-row">
                      <td>
                        <img src="<?= htmlspecialchars($p['image'] ?: '/frontend/photos/box1__image.png') ?>" alt="Product" class="admin-product-thumb" onerror="this.src='/frontend/photos/box1__image.png'" />
                      </td>
                      <td><strong>#<?= (int)$p['id'] ?></strong></td>
                      <td>
                        <strong class="product-table-name"><?= htmlspecialchars($p['name']) ?></strong>
                        <?php if (!empty($p['description'])): ?>
                          <p class="product-table-desc"><?= htmlspecialchars(mb_strimwidth($p['description'], 0, 70, '...')) ?></p>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="category-pill"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                      </td>
                      <td>
                        <strong class="price-text">₹<?= number_format((float)$p['price'], 2) ?></strong>
                      </td>
                      <td>
                        <?php if ((int)$p['stock'] > 10): ?>
                          <span class="stock-badge stock-in"><?= (int)$p['stock'] ?> in stock</span>
                        <?php elseif ((int)$p['stock'] > 0): ?>
                          <span class="stock-badge stock-low">Low (<?= (int)$p['stock'] ?>)</span>
                        <?php else: ?>
                          <span class="stock-badge stock-out">Out of stock</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete \'<?= htmlspecialchars(addslashes($p['name'])) ?>\'? This action cannot be undone.');">
                          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>" />
                          <button type="submit" name="delete_product" class="admin-delete-btn" title="Delete product">
                            <i class="fa-solid fa-trash"></i> Delete
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- TAB: STORE HUBS & MANAGERS -->
        <section id="tabHubs" class="admin-tab-content">
          <div class="admin-section-header" style="flex-wrap: wrap; gap: 14px;">
            <div>
              <h2>Verified Physical Hubs &amp; Store Admins (<?= count($hubs) ?>)</h2>
              <p class="section-desc">Manage regional fulfillment hubs, switch operating location, or create additional admin managers.</p>
            </div>
            <button type="button" class="btn-create-admin-dash" onclick="toggleDashboardNewAdmin()">
              <i class="fa-solid fa-user-plus"></i> + Register New Admin &amp; Hub
            </button>
          </div>

          <!-- Collapsible Create Admin & Hub Inside Dashboard -->
          <div id="dashboardNewAdminBox" class="dashboard-reg-box" style="display: none;">
            <div class="dash-reg-inner">
              <div class="dash-reg-header">
                <div>
                  <h3><i class="fa-solid fa-store"></i> Register New Hub Admin &amp; Storefront</h3>
                  <p>Authorize a new manager account and link their physical Ranchi neighborhood fulfillment hub.</p>
                </div>
                <button type="button" class="btn-close-dash-reg" onclick="toggleDashboardNewAdmin()">✕</button>
              </div>

              <form method="POST" class="admin-form" style="margin-top: 14px;">
                <div class="admin-form-grid-2">
                  <label>
                    Manager Full Name *
                    <input type="text" name="name" required placeholder="e.g. Alok Verma" />
                  </label>

                  <label>
                    Admin Username *
                    <input type="text" name="username" required placeholder="e.g. alok" pattern="[a-zA-Z0-9_-]+" title="Letters, numbers, underscores only" />
                  </label>
                </div>

                <div class="admin-form-grid-2">
                  <label>
                    Official Email *
                    <input type="email" name="email" required placeholder="alok@ranchimart.com" />
                  </label>

                  <label>
                    Password *
                    <input type="password" name="password" required value="admin123" />
                  </label>
                </div>

                <div class="admin-form-grid-2">
                  <label>
                    Designation / Role
                    <select name="role">
                      <option value="Senior Operations Head & Hub Director">Senior Operations Head & Hub Director</option>
                      <option value="Hub Operations Lead" selected>Hub Operations Lead</option>
                      <option value="Warehouse & Inventory Lead">Warehouse & Inventory Lead</option>
                      <option value="Quick Commerce Store Manager">Quick Commerce Store Manager</option>
                      <option value="Dispatch & Logistics Head">Dispatch & Logistics Head</option>
                    </select>
                  </label>

                  <label>
                    Direct Contact Phone
                    <input type="text" name="phone" placeholder="+91 98350 12345" />
                  </label>
                </div>

                <div class="admin-form-grid-2">
                  <label>
                    Hub Full Name
                    <input type="text" name="hub_name" placeholder="Ranchi Mart Harmu Super Hub" />
                  </label>

                  <label>
                    Hub Short Title
                    <input type="text" name="hub_short" placeholder="Harmu Hub" />
                  </label>
                </div>

                <div class="admin-form-grid-2">
                  <label>
                    Service Pincodes
                    <input type="text" name="pincodes" placeholder="834002, 834012" />
                  </label>

                  <label>
                    Store Badge
                    <select name="badge">
                      <option value="Verified Hub Manager" selected>Verified Hub Manager</option>
                      <option value="Express Dispatch Center">Express Dispatch Center</option>
                      <option value="Quick Commerce Store">Quick Commerce Store</option>
                      <option value="Regional Super Hub">Regional Super Hub</option>
                    </select>
                  </label>
                </div>

                <label>
                  Physical Store Address
                  <input type="text" name="hub_address" placeholder="Argora Bypass Road, Harmu, Ranchi - 834002" />
                </label>

                <div style="margin-top: 14px; display: flex; gap: 12px; align-items: center;">
                  <button type="submit" name="create_admin" class="admin-submit-btn" style="width: auto; padding: 10px 24px;">
                    <i class="fa-solid fa-check"></i> Register Admin &amp; Activate Hub
                  </button>
                  <button type="button" class="btn-cancel-flat" onclick="toggleDashboardNewAdmin()">Cancel</button>
                </div>
              </form>
            </div>
          </div>

          <div class="hubs-admin-grid">
            <?php foreach ($hubs as $hub): 
              $isCurrent = ($activeAdmin['hub_code'] === $hub['code']);
            ?>
              <div class="hub-admin-card <?= $isCurrent ? 'hub-active-ring' : '' ?>">
                <div class="hub-admin-card-top">
                  <span class="hub-admin-badge" style="background: <?= htmlspecialchars($hub['badge_color']) ?>; color: #fff;">
                    <?= htmlspecialchars($hub['badge']) ?>
                  </span>
                  <span class="hub-admin-code"><?= htmlspecialchars($hub['code']) ?></span>
                </div>

                <div class="hub-admin-mgr">
                  <img src="<?= htmlspecialchars($hub['manager_avatar']) ?>" alt="<?= htmlspecialchars($hub['manager_name']) ?>" class="hub-admin-avatar" />
                  <div>
                    <h3 class="hub-mgr-name"><?= htmlspecialchars($hub['manager_name']) ?></h3>
                    <p class="hub-mgr-role"><?= htmlspecialchars($hub['manager_role']) ?></p>
                    <a href="tel:<?= htmlspecialchars($hub['manager_phone']) ?>" class="hub-mgr-phone"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($hub['manager_phone']) ?></a>
                  </div>
                </div>

                <div class="hub-admin-info-box">
                  <strong class="hub-title"><?= htmlspecialchars($hub['name']) ?></strong>
                  <p class="hub-loc"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($hub['address']) ?></p>
                  <p class="hub-landmark"><i class="fa-regular fa-building"></i> Landmark: <?= htmlspecialchars($hub['landmark'] ?? 'Ranchi Center') ?></p>
                  
                  <div class="hub-meta-pills">
                    <span><i class="fa-solid fa-map-pin"></i> Pin: <?= htmlspecialchars($hub['pincodes']) ?></span>
                    <span><i class="fa-solid fa-bolt"></i> <?= htmlspecialchars($hub['delivery_speed']) ?> Dispatch</span>
                    <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($hub['timing']) ?></span>
                    <span><i class="fa-solid fa-star text-amber"></i> <?= htmlspecialchars($hub['rating']) ?> (<?= htmlspecialchars($hub['orders_completed']) ?> orders)</span>
                  </div>
                </div>

                <div class="hub-admin-footer">
                  <?php if ($isCurrent): ?>
                    <span class="hub-current-status"><i class="fa-solid fa-circle-check"></i> Currently Active Profile</span>
                  <?php else: 
                    $matchUser = !empty($hub['manager_username']) ? $hub['manager_username'] : 'rajesh';
                  ?>
                    <form method="POST" style="margin: 0;">
                      <input type="hidden" name="target_user" value="<?= htmlspecialchars($matchUser) ?>" />
                      <button type="submit" name="switch_admin" class="btn-switch-hub">
                        <i class="fa-solid fa-right-left"></i> Operate from this Hub
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- TAB 5: DATABASE & AUDIT LOGS -->
        <section id="tabDatabase" class="admin-tab-content">
          <div class="admin-section-header">
            <div>
              <h2><i class="fa-solid fa-server" style="color: #0284c7; margin-right: 8px;"></i> Backend Database Engine &amp; Audit Trail</h2>
              <p class="section-desc">Production SQLite 3 relational storage, auto-migrated schema, performance indexes, and full administrative audit trail.</p>
            </div>
            <div class="db-status-pill">
              <span class="db-dot-live"></span> WAL Engine Active • SQLite <?= htmlspecialchars($sqliteVer) ?>
            </div>
          </div>

          <!-- Database Architecture Quick Stats -->
          <div class="db-metrics-grid">
            <div class="db-metric-card">
              <div class="db-metric-icon" style="background: #e0f2fe; color: #0284c7;"><i class="fa-solid fa-database"></i></div>
              <div class="db-metric-text">
                <span class="db-metric-label">Database File</span>
                <strong class="db-metric-val">shop.db (<?= $dbSizeKb ?> KB)</strong>
                <small class="db-metric-sub">SQLite Journal: <?= strtoupper($journalMode) ?></small>
              </div>
            </div>

            <div class="db-metric-card">
              <div class="db-metric-icon" style="background: #ecfdf5; color: #059669;"><i class="fa-solid fa-table-cells"></i></div>
              <div class="db-metric-text">
                <span class="db-metric-label">Relational Tables</span>
                <strong class="db-metric-val"><?= count($tablesList) ?> Production Tables</strong>
                <small class="db-metric-sub">Foreign Keys: ON (Cascading)</small>
              </div>
            </div>

            <div class="db-metric-card">
              <div class="db-metric-icon" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-bolt"></i></div>
              <div class="db-metric-text">
                <span class="db-metric-label">Speed Indexes</span>
                <strong class="db-metric-val"><?= count($indexesList) ?> B-Tree Indexes</strong>
                <small class="db-metric-sub">Auto-indexed for fast queries</small>
              </div>
            </div>

            <div class="db-metric-card">
              <div class="db-metric-icon" style="background: #f3e8ff; color: #7c3aed;"><i class="fa-solid fa-shield-halved"></i></div>
              <div class="db-metric-text">
                <span class="db-metric-label">Audit Logs</span>
                <strong class="db-metric-val"><?= $tableCounts['admin_logs'] ?? 0 ?> Tracked Events</strong>
                <small class="db-metric-sub">Tamper-evident activity trail</small>
              </div>
            </div>
          </div>

          <!-- Live Schema & Table Counts -->
          <div class="db-tables-overview">
            <h3 class="db-subheading"><i class="fa-solid fa-layer-group"></i> Active Database Tables &amp; Row Counts</h3>
            <div class="db-tables-grid">
              <?php 
                $tableMetadata = [
                  'products' => ['icon' => 'fa-box', 'color' => '#0284c7', 'desc' => 'Store catalog items, prices & stock'],
                  'categories' => ['icon' => 'fa-tags', 'color' => '#059669', 'desc' => 'Grocery, Electronics, Fashion'],
                  'orders' => ['icon' => 'fa-receipt', 'color' => '#d97706', 'desc' => 'Customer orders & dispatch tracking'],
                  'users' => ['icon' => 'fa-users', 'color' => '#7c3aed', 'desc' => 'Registered customers & credentials'],
                  'admins' => ['icon' => 'fa-user-tie', 'color' => '#2563eb', 'desc' => 'Hub managers & operations staff'],
                  'admin_logs' => ['icon' => 'fa-clock-rotate-left', 'color' => '#475569', 'desc' => 'Audit history of all admin actions'],
                  'product_reviews' => ['icon' => 'fa-star', 'color' => '#eab308', 'desc' => 'Customer ratings & verified feedback'],
                  'contact_inquiries' => ['icon' => 'fa-envelope-open-text', 'color' => '#06b6d4', 'desc' => 'Helpdesk messages & queries']
                ];
                foreach ($tablesList as $tbl): 
                  $meta = $tableMetadata[$tbl] ?? ['icon' => 'fa-table', 'color' => '#64748b', 'desc' => 'Data storage table'];
                  $count = $tableCounts[$tbl] ?? 0;
              ?>
                <div class="db-table-pill">
                  <div class="db-tbl-icon" style="background: <?= $meta['color'] ?>15; color: <?= $meta['color'] ?>;">
                    <i class="fa-solid <?= $meta['icon'] ?>"></i>
                  </div>
                  <div class="db-tbl-info">
                    <div class="db-tbl-top">
                      <strong><?= htmlspecialchars($tbl) ?></strong>
                      <span class="db-tbl-count"><?= $count ?> rows</span>
                    </div>
                    <small><?= htmlspecialchars($meta['desc']) ?></small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Audit Activity Trail -->
          <div class="db-audit-container">
            <div class="db-audit-header">
              <div>
                <h3 class="db-subheading"><i class="fa-solid fa-clock-rotate-left"></i> Security &amp; Operations Audit Trail</h3>
                <p class="section-desc">Logged administrative actions, hub switches, logins, catalog additions, and order updates.</p>
              </div>
              <span class="audit-counter-badge">Latest <?= count($adminLogs) ?> Actions</span>
            </div>

            <?php if (empty($adminLogs)): ?>
              <div class="admin-empty-state">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h3>No activity logged yet</h3>
                <p>Actions like adding products, confirming orders, or logging in will automatically be recorded here.</p>
              </div>
            <?php else: ?>
              <div class="audit-table-wrap">
                <table class="audit-data-table">
                  <thead>
                    <tr>
                      <th>Timestamp</th>
                      <th>Admin</th>
                      <th>Action</th>
                      <th>Details</th>
                      <th>Entity</th>
                      <th>IP</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($adminLogs as $lg): 
                      $act = strtoupper($lg['action'] ?? '');
                      $badgeClass = 'action-default';
                      if (str_contains($act, 'LOGIN') || str_contains($act, 'REGISTER') || str_contains($act, 'SWITCH')) $badgeClass = 'action-login';
                      elseif (str_contains($act, 'ADD')) $badgeClass = 'action-add';
                      elseif (str_contains($act, 'UPDATE') || str_contains($act, 'STATUS')) $badgeClass = 'action-update';
                      elseif (str_contains($act, 'DELETE')) $badgeClass = 'action-delete';
                    ?>
                      <tr>
                        <td class="log-time-col">
                          <i class="fa-regular fa-clock"></i>
                          <?= htmlspecialchars($lg['created_at'] ?? 'Now') ?>
                        </td>
                        <td class="log-admin-col">
                          <strong><?= htmlspecialchars($lg['admin_username'] ?? 'system') ?></strong>
                        </td>
                        <td>
                          <span class="log-action-badge <?= $badgeClass ?>">
                            <?= htmlspecialchars($lg['action']) ?>
                          </span>
                        </td>
                        <td class="log-details-col">
                          <?= htmlspecialchars($lg['details'] ?? '—') ?>
                        </td>
                        <td class="log-entity-col">
                          <?php if (!empty($lg['entity_type'])): ?>
                            <span class="log-entity-tag"><?= htmlspecialchars($lg['entity_type']) ?> #<?= (int)($lg['entity_id'] ?? 0) ?></span>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td class="log-ip-col">
                          <code><?= htmlspecialchars($lg['ip_address'] ?? '127.0.0.1') ?></code>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- Customer Inquiries -->
          <div class="db-inquiries-container">
            <div class="db-audit-header">
              <div>
                <h3 class="db-subheading"><i class="fa-solid fa-envelope-open-text"></i> Customer Support Messages (<?= count($allInquiries) ?>)</h3>
                <p class="section-desc">Inquiries and feedback messages sent from the customer storefront.</p>
              </div>
            </div>

            <?php if (empty($allInquiries)): ?>
              <div class="admin-empty-state" style="padding: 24px;">
                <i class="fa-regular fa-envelope"></i>
                <p>No customer support inquiries yet. Inquiries submitted via Contact form will be stored here.</p>
              </div>
            <?php else: ?>
              <div class="inquiries-list">
                <?php foreach ($allInquiries as $inq): ?>
                  <div class="inquiry-card">
                    <div class="inquiry-card-top">
                      <strong><?= htmlspecialchars($inq['name']) ?> &lt;<?= htmlspecialchars($inq['email']) ?>&gt;</strong>
                      <span class="order-timestamp"><?= htmlspecialchars($inq['created_at']) ?></span>
                    </div>
                    <?php if (!empty($inq['subject'])): ?>
                      <div class="inquiry-subject"><?= htmlspecialchars($inq['subject']) ?></div>
                    <?php endif; ?>
                    <p class="inquiry-msg"><?= nl2br(htmlspecialchars($inq['message'])) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>

      </div>
    </div>

    <!-- Admin Panel JavaScript -->
    <script>
      function toggleDashboardNewAdmin() {
        const box = document.getElementById('dashboardNewAdminBox');
        if (box) {
          const isHidden = (box.style.display === 'none' || box.style.display === '');
          box.style.display = isHidden ? 'block' : 'none';
          if (isHidden) {
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        }
      }

      function switchAdminTab(tabId) {
        document.querySelectorAll('.admin-tab-content').forEach(el => el.classList.remove('active-content'));
        document.querySelectorAll('.admin-tab-btn').forEach(el => el.classList.remove('is-active'));
        
        const targetTab = document.getElementById(tabId);
        if (targetTab) targetTab.classList.add('active-content');

        if (tabId === 'tabOrders') document.getElementById('btnTabOrders')?.classList.add('is-active');
        if (tabId === 'tabHubs') document.getElementById('btnTabHubs')?.classList.add('is-active');
        if (tabId === 'tabAddProduct') document.getElementById('btnTabAddProduct')?.classList.add('is-active');
        if (tabId === 'tabProducts') document.getElementById('btnTabProducts')?.classList.add('is-active');
        if (tabId === 'tabDatabase') document.getElementById('btnTabDatabase')?.classList.add('is-active');
      }

      function filterOrderRows(filterType, buttonEl) {
        document.querySelectorAll('.filter-pill').forEach(btn => btn.classList.remove('is-active'));
        if (buttonEl) buttonEl.classList.add('is-active');

        const cards = document.querySelectorAll('.admin-order-card');
        cards.forEach(card => {
          const status = card.dataset.orderStatus || '';
          if (filterType === 'all') {
            card.style.display = '';
          } else if (filterType === 'pending') {
            card.style.display = status === 'pending' ? '' : 'none';
          } else if (filterType === 'confirmed') {
            card.style.display = status === 'confirmed' ? '' : 'none';
          } else if (filterType === 'packed') {
            card.style.display = status.includes('pack') ? '' : 'none';
          } else if (filterType === 'transit') {
            card.style.display = (status.includes('out') || status.includes('transit')) ? '' : 'none';
          } else if (filterType === 'delivered') {
            card.style.display = status === 'delivered' ? '' : 'none';
          }
        });
      }

      function filterProductsTable() {
        const query = (document.getElementById('productSearchInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.product-data-row');
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(query) ? '' : 'none';
        });
      }

      // Sync active admin to localStorage
      try {
        localStorage.setItem('rm_admin_active_session', JSON.stringify({
          username: <?= json_encode($activeAdmin['username'] ?? '') ?>,
          name: <?= json_encode($activeAdmin['name'] ?? '') ?>,
          hub: <?= json_encode($activeAdmin['hub_short'] ?? '') ?>
        }));
      } catch(e) {}

      // Auto fade flash message after 5 seconds
      setTimeout(() => {
        const flash = document.getElementById('adminFlashMsg');
        if (flash) {
          flash.style.transition = 'opacity 0.4s ease';
          flash.style.opacity = '0';
          setTimeout(() => flash.remove(), 400);
        }
      }, 5000);
    </script>

  <?php endif; ?>
</body>
</html>
