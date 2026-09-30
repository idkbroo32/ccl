<?php
session_start();
require dirname(__DIR__) . '/backend/db.php';
ensureDatabase();

$isCheckout = isset($_GET['checkout']);

if (isset($_GET['logout'])) {
    unset($_SESSION['piku_user']);
    unset($_SESSION['piku_user_data']);
    echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"></head><body><script>
        localStorage.removeItem('pikuLoggedIn');
        localStorage.removeItem('pikuUser');
        window.location.href = '/frontend/index.html';
    </script></body></html>";
    exit;
}

$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $mode = $_POST['mode'] ?? 'login';
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($mode === 'signup') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Name, email, and password are required.';
        } else {
            try {
                $userId = registerUser($name, $email, $password, $phone, $address);
                $_SESSION['piku_user'] = $name;
                $_SESSION['piku_user_data'] = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                ];
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                ];
                echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"></head><body><script>
                    localStorage.setItem('pikuLoggedIn', 'true');
                    localStorage.setItem('pikuUser', " . json_encode(json_encode($userData)) . ");
                    window.location.href = '/frontend/index.html';
                </script></body></html>";
                exit;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    } else {
        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } else {
            // 1. Check if credentials belong to an Admin
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

                logAdminActivity($admin['id'] ?? null, $admin['username'], 'LOGIN', "Logged into Hub: {$admin['hub_short']} via Login Page");

                $token = hash('sha256', $admin['username'] . '_ranchimart_secret_2026');
                @setcookie('rm_admin_user', $admin['username'], time() + 86400 * 30, '/');
                @setcookie('rm_admin_token', $token, time() + 86400 * 30, '/');

                $adminTarget = 'admin.php?quick_login=' . urlencode($admin['username']) . '&pass=' . urlencode($password);
                if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/frontend/') !== false) {
                    $adminTarget = '/frontend/admin.php?quick_login=' . urlencode($admin['username']) . '&pass=' . urlencode($password);
                }
                echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"></head><body><script>
                    localStorage.setItem('adminLoggedIn', 'true');
                    localStorage.setItem('pikuLoggedIn', 'true');
                    localStorage.setItem('pikuUser', " . json_encode(json_encode(['name' => $admin['name'], 'email' => $admin['email'], 'role' => $admin['role'], 'is_admin' => true])) . ");
                    window.location.href = " . json_encode($adminTarget) . ";
                </script></body></html>";
                exit;
            }

            // 2. Otherwise authenticate regular Customer
            $user = authenticateUser($email, $password);
            if ($user) {
                $_SESSION['piku_user'] = $user['name'];
                $_SESSION['piku_user_data'] = $user;
                $targetUrl = $isCheckout ? '/frontend/login.php?checkout=1' : '/frontend/index.html';
                echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"></head><body><script>
                    localStorage.setItem('pikuLoggedIn', 'true');
                    localStorage.setItem('pikuUser', " . json_encode(json_encode($user)) . ");
                    window.location.href = '$targetUrl';
                </script></body></html>";
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $mode === 'signup' ? 'Create a new account' : 'Login' ?> - Ranchi Mart</title>
  <link rel="stylesheet" href="/frontend/style.css">
  <style>
    .auth-tab-buttons {
      display: flex;
      gap: 10px;
      margin-bottom: 22px;
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 12px;
    }
    .auth-tab-btn {
      flex: 1;
      padding: 10px 14px;
      border: 1px solid #e5e7eb;
      border-radius: 99px;
      background: #f9fafb;
      color: #4b5563;
      font-weight: 700;
      font-size: 0.88rem;
      cursor: pointer;
      transition: all 0.25s ease;
      text-align: center;
      text-decoration: none;
    }
    .auth-tab-btn.active {
      background: #15231d;
      border-color: #15231d;
      color: #fff;
    }
    .auth-back-link {
      display: inline-block;
      margin-top: 18px;
      text-align: center;
      width: 100%;
      color: #6b7280;
      font-size: 0.85rem;
      text-decoration: none;
      font-weight: 600;
    }
    .auth-back-link:hover {
      color: #ef6b58;
    }
  </style>
</head>
<body class="<?= $isCheckout ? 'checkout-page' : '' ?>" data-logged-in="<?= !empty($_SESSION['piku_user']) ? 'true' : 'false' ?>" data-checkout-page="<?= $isCheckout ? 'true' : 'false' ?>">
  <?php if ($isCheckout): ?>
    <header class="checkout-header">
      <a class="brand" href="/frontend/index.html"><span>RANCHI</span> MART</a>
      <div class="checkout-actions">
        <button type="button" class="track-order-button" onclick="openOrderTrackingModal()" style="padding: 7px 14px; font-size: 0.78rem;">
          <span class="track-icon">📦</span> Track Order
        </button>
        <a href="/frontend/admin.php" class="admin-store-btn" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none;">⚡ Admin Panel</a>
        <a class="back-link" href="/frontend/index.html">← Continue shopping</a>
        <a class="logout-link" href="/frontend/login.php?logout=1">Logout</a>
      </div>
    </header>
    <main class="checkout-layout">
      <section class="checkout-intro"><p class="eyebrow">SECURE CHECKOUT</p><h1>Make it<br><em>yours.</em></h1><p>Review your edit, choose how you would like to pay, and we will take care of the rest.</p><div class="checkout-steps"><span class="is-current">01 Cart</span><i></i><span class="is-current">02 Payment</span><i></i><span>03 Done</span></div></section>
      <section class="checkout-card"><div class="checkout-card-heading"><div><p class="eyebrow">YOUR ORDER</p><h2>Order summary</h2></div><span id="checkoutCount">0 items</span></div><div id="checkoutItems" class="checkout-items"></div><div class="checkout-total"><span>Total</span><strong id="checkoutTotal">₹0</strong></div></section>
      <section class="checkout-card payment-card">
        <p class="eyebrow">DELIVERY & PAYMENT</p>
        <h2>Finish your order</h2>
        <form id="checkoutPaymentForm" class="payment-form">
          <!-- Step 1: Delivery Address -->
          <div class="checkout-section">
            <div class="checkout-section-title">
              <span class="step-badge">Step 1</span>
              <strong>Delivery Address</strong>
            </div>

            <div id="checkoutSavedAddressBox" class="address-card">
              <div class="address-card-header">
                <strong id="checkoutReceiverName">User</strong>
                <span class="address-tag">Deliver to this address</span>
              </div>
              <p id="checkoutReceiverAddress" class="address-card-body"></p>
              <div id="checkoutReceiverPhone" class="address-card-phone"></div>
            </div>

            <button type="button" id="btnCheckoutToggleAddress" class="address-toggle-btn" onclick="toggleCheckoutAddressForm()">
              + Add New / Edit Delivery Address
            </button>

            <div id="checkoutAddressFields" class="address-fields-grid">
              <div class="address-row-two">
                <label>Receiver Full Name<input name="deliveryName" id="checkoutDeliveryName" required placeholder="e.g. Abhishek Kumar"></label>
                <label>Phone Number<input name="deliveryPhone" id="checkoutDeliveryPhone" type="tel" required placeholder="e.g. 98765 43210"></label>
              </div>
              <label>Flat, House no., Building, Apartment<input name="deliveryStreet" id="checkoutDeliveryStreet" required placeholder="e.g. House No. 12B, Green Park"></label>
              <label>Area, Colony, Street, Landmark<input name="deliveryArea" id="checkoutDeliveryArea" required placeholder="e.g. Circular Road, Near Nucleus Mall"></label>
              <div class="address-row-two">
                <label>City<input name="deliveryCity" id="checkoutDeliveryCity" required value="Ranchi" placeholder="City"></label>
                <label>Pincode<input name="deliveryPincode" id="checkoutDeliveryPincode" inputmode="numeric" pattern="[0-9]{6}" required placeholder="e.g. 834001"></label>
              </div>
            </div>
          </div>

          <!-- Step 2: Payment Details -->
          <div class="checkout-section" style="border-bottom: none; padding-bottom: 0;">
            <div class="checkout-section-title">
              <span class="step-badge">Step 2</span>
              <strong>Payment Method</strong>
            </div>
            <fieldset class="checkout-methods">
              <legend>Payment method</legend>
              <label class="radio-option"><input type="radio" name="method" value="card" checked> Credit / debit card <span>Secure</span></label>
              <label class="radio-option"><input type="radio" name="method" value="cod"> Cash on delivery <span>Pay at door</span></label>
            </fieldset>

            <div id="cardFields" class="card-fields">
              <label>Cardholder name<input name="cardName" required placeholder="Your full name"></label>
              <label>Card number<input name="cardNumber" inputmode="numeric" pattern="[0-9 ]{12,19}" required placeholder="1234 5678 9012 3456"></label>
              <div class="payment-row">
                <label>Expiry<input name="expiry" required placeholder="MM / YY"></label>
                <label>CVV<input name="cvv" inputmode="numeric" pattern="[0-9]{3,4}" required placeholder="123"></label>
              </div>
            </div>

            <div id="checkoutCodNotice">
              💵 <strong>Cash on Delivery:</strong> Pay safely with cash or UPI when your parcel arrives.
            </div>
          </div>

          <button type="submit" class="primary-button">Place order <span>↗</span></button>
        </form>

        <div id="checkoutSuccess" class="checkout-success" hidden>
          <span>✓</span>
          <h3>Order placed successfully!</h3>
          <p id="checkoutSuccessDetail">Thank you for shopping with Ranchi Mart. Your order is being prepared.</p>
          <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 18px;">
            <button type="button" class="primary-button" onclick="openOrderTrackingModal(localStorage.getItem('lastPlacedOrderCode') || '')">Track Order Live <span>📦</span></button>
            <a class="text-button" href="/frontend/index.html">Back to shopping <span>→</span></a>
          </div>
        </div>
      </section>
    </main>

    <!-- Order Tracking Modal -->
    <div id="trackingModal" class="overlay-modal" aria-hidden="true">
      <div class="tracking-panel modal-panel">
        <button type="button" class="modal-close" data-close="trackingModal" aria-label="Close tracking">×</button>
        <p class="eyebrow">LIVE DELIVERY RADAR</p>
        <h2>Track your order.</h2>

        <div class="tracking-search-box">
          <div class="tracking-input-wrap">
            <input id="trackingInput" type="text" placeholder="Enter Order ID (e.g. RM-892415) or Mobile">
            <button type="button" class="primary-button tracking-search-btn" onclick="handleTrackSearch()">
              Track <span>↗</span>
            </button>
          </div>
          <div class="tracking-demo-tags">
            <span>Quick check:</span>
            <button type="button" class="quick-tag-btn" onclick="quickTrackOrder('demo')">#RM-DEMO99</button>
          </div>
        </div>

        <div id="trackingRecentOrdersWrap" class="recent-orders-bar">
          <span class="recent-label">Your Recent Orders:</span>
          <div id="recentOrdersList" class="recent-orders-pills"></div>
        </div>

        <div id="trackingResultCard" class="tracking-card">
          <div class="tracking-card-header">
            <div>
              <span class="order-id-label">ORDER ID</span>
              <strong id="trackOrderCode" class="order-code-display">#RM-892415</strong>
              <p id="trackOrderDate" class="order-date-text">Placed Today</p>
            </div>
            <div class="tracking-badge-wrap">
              <span id="trackStatusBadge" class="status-pill status-in-transit">● In Transit</span>
              <strong id="trackEtaText" class="eta-text">Est. Delivery: Within 2 Days</strong>
            </div>
          </div>

          <div class="tracking-stepper">
            <div class="step-item step-completed" id="stepPlaced">
              <div class="step-circle">✓</div>
              <div class="step-content">
                <strong>Order Confirmed</strong>
                <span id="stepPlacedTime">Order accepted & verified</span>
              </div>
            </div>
            <div class="step-line step-line-active" id="line1"></div>

            <div class="step-item step-completed" id="stepPacked">
              <div class="step-circle">📦</div>
              <div class="step-content">
                <strong>Packed & Dispatched</strong>
                <span id="stepPackedTime">Ranchi Sorting Hub</span>
              </div>
            </div>
            <div class="step-line step-line-active" id="line2"></div>

            <div class="step-item step-active" id="stepTransit">
              <div class="step-circle">🚚</div>
              <div class="step-content">
                <strong>Out for Delivery</strong>
                <span id="stepTransitTime">Delivery partner on route</span>
              </div>
            </div>
            <div class="step-line" id="line3"></div>

            <div class="step-item" id="stepDelivered">
              <div class="step-circle">🏠</div>
              <div class="step-content">
                <strong>Delivered</strong>
                <span id="stepDeliveredTime">To your doorstep</span>
              </div>
            </div>
          </div>

          <div class="courier-info-box">
            <div class="courier-avatar">🛵</div>
            <div class="courier-details">
              <strong id="trackCourierName">Delivery Partner: Ramesh Kumar (Ranchi Mart Express)</strong>
              <span>Contact: +91 98765-43210 • Contactless delivery</span>
            </div>
            <span class="courier-tag">Express Delivery</span>
          </div>

          <div class="origin-hub-box">
            <div class="origin-hub-icon">🏬</div>
            <div class="origin-hub-details">
              <strong id="trackHubName">Current dispatch location</strong>
              <span id="trackHubManager">Dispatch hub manager</span>
              <span id="trackTrackingNotes">Latest order location update will appear here.</span>
            </div>
            <span class="hub-verified-pill">Order Tracking</span>
          </div>

          <div class="tracking-details-grid">
            <div class="track-info-col">
              <h4>Delivery Address</h4>
              <p id="trackReceiverName" class="track-person-name">Abhishek Kumar</p>
              <p id="trackFullAddress" class="track-address-text">Circular Road, Lalpur, Ranchi - 834001</p>
              <p id="trackPhone" class="track-phone-text">📞 +91 98765 43210</p>
            </div>
            <div class="track-info-col">
              <h4>Order & Payment Summary</h4>
              <div id="trackItemsList" class="track-items-mini"></div>
              <div class="track-bill-row">
                <span>Payment Mode:</span>
                <strong id="trackPayMethod">Cash on delivery</strong>
              </div>
              <div class="track-bill-row total-row">
                <span>Total Amount:</span>
                <strong id="trackTotalAmount">₹1,499</strong>
              </div>
            </div>
          </div>
        </div>

        <div id="trackingNotFound" class="tracking-empty">
          <span class="empty-icon">🔍</span>
          <h3>Order not found</h3>
          <p id="trackingNotFoundMsg">We couldn't find an order matching that ID. Please check your Order ID or try the demo order.</p>
        </div>
      </div>
    </div>

    <script src="/frontend/script.js"></script>
  <?php else: ?>
  <div class="auth-page">
    <div class="auth-card">
      <h2 id="pageAuthTitle"><?= $mode === 'signup' ? 'Create a new account' : 'Login to Ranchi Mart' ?></h2>

      <div class="auth-tab-buttons">
        <a href="?mode=login" class="auth-tab-btn <?= $mode !== 'signup' ? 'active' : '' ?>" id="btnTabLogin" onclick="setMode('login'); return false;">Sign in</a>
        <a href="?mode=signup" class="auth-tab-btn <?= $mode === 'signup' ? 'active' : '' ?>" id="btnTabSignup" onclick="setMode('signup'); return false;">Create account</a>
      </div>

      <form class="auth-form" id="pageAuthForm" method="POST" action="">
        <input type="hidden" name="mode" id="formModeInput" value="<?= htmlspecialchars($mode) ?>">

        <div id="fieldWrapName" style="<?= $mode === 'signup' ? '' : 'display: none;' ?>">
          <label for="name">Full Name</label>
          <input id="name" name="name" type="text" placeholder="John Doe">
        </div>

        <div>
          <label for="email">Username or Email</label>
          <input id="email" name="email" type="text" placeholder="Username (admin / rajesh) or Email" required autocomplete="username">
        </div>

        <div>
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
        </div>

        <div id="fieldWrapPhone" style="<?= $mode === 'signup' ? '' : 'display: none;' ?>">
          <label for="phone">Phone Number</label>
          <input id="phone" name="phone" type="tel" placeholder="+91 98765 43210">
        </div>

        <div id="fieldWrapAddress" style="<?= $mode === 'signup' ? '' : 'display: none;' ?>">
          <label for="address">Delivery Address</label>
          <input id="address" name="address" type="text" placeholder="House no, Street, City">
        </div>

        <button type="submit" id="btnPageSubmit"><?= $mode === 'signup' ? 'Create a new account' : 'Sign In' ?></button>
      </form>

      <?php if ($error !== ''): ?>
        <div class="auth-error" style="color: #b91c1c; font-weight: 600; margin-top: 14px; text-align: center;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="auth-switch-row" style="margin-top: 18px; text-align: center;">
        <span id="pageSwitchText"><?= $mode === 'signup' ? 'Already have an account?' : "Don't have an account?" ?></span>
        <button type="button" class="auth-switch" id="pageSwitchBtn" onclick="togglePageMode()"><?= $mode === 'signup' ? 'Sign in' : 'Create a new account' ?></button>
      </div>

      <div class="demo-login" id="pageDemoBox" style="<?= $mode === 'signup' ? 'display: none;' : '' ?>">
        <strong>⚡ 1-Click Quick Fill Credentials:</strong>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
          <button type="button" class="auth-switch" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" onclick="fillLoginCredentials('admin', 'admin123')">👑 Master Admin (admin)</button>
          <button type="button" class="auth-switch" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" onclick="fillLoginCredentials('rajesh', 'admin123')">🏢 Hub Director (rajesh)</button>
          <button type="button" class="auth-switch" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" onclick="fillLoginCredentials('admin@pikumart.com', '123456')">🛒 Customer (Piku)</button>
        </div>
      </div>

      <a href="/frontend/index.html" class="auth-back-link">← Back to Ranchi Mart</a>
      <a href="admin.html" onclick="if(window.location.port==='8000'){location.href='admin.php';return false;}" class="auth-back-link" style="margin-top: 6px; color: #4338ca; font-weight: 700;">⚡ Open Admin Dashboard →</a>
    </div>
  </div>

  <script>
    function setMode(mode) {
      const isSignup = mode === 'signup';
      document.getElementById('formModeInput').value = mode;
      document.getElementById('pageAuthTitle').textContent = isSignup ? 'Create a new account' : 'Login to Ranchi Mart';
      document.getElementById('btnPageSubmit').textContent = isSignup ? 'Create a new account' : 'Login';
      document.getElementById('fieldWrapName').style.display = isSignup ? 'block' : 'none';
      document.getElementById('fieldWrapPhone').style.display = isSignup ? 'block' : 'none';
      document.getElementById('fieldWrapAddress').style.display = isSignup ? 'block' : 'none';
      document.getElementById('pageDemoBox').style.display = isSignup ? 'none' : 'grid';
      document.getElementById('pageSwitchText').textContent = isSignup ? 'Already have an account?' : "Don't have an account?";
      document.getElementById('pageSwitchBtn').textContent = isSignup ? 'Sign in' : 'Create a new account';
      
      const nameInput = document.getElementById('name');
      if (isSignup) {
        nameInput.setAttribute('required', 'required');
      } else {
        nameInput.removeAttribute('required');
      }

      document.getElementById('btnTabLogin').classList.toggle('active', !isSignup);
      document.getElementById('btnTabSignup').classList.toggle('active', isSignup);
    }

    function togglePageMode() {
      const currentMode = document.getElementById('formModeInput').value;
      setMode(currentMode === 'signup' ? 'login' : 'signup');
    }

    function fillLoginCredentials(u, p) {
      setMode('login');
      document.getElementById('email').value = u;
      document.getElementById('password').value = p;
    }
  </script>
  <?php endif; ?>
</body>
</html>
