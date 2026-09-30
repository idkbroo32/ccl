// ============================================================================
// Ranchi Mart — Standalone Admin Portal Controller (Works with Go Live & PHP)
// ============================================================================

function resolveAssetPath(path) {
  if (!path) {
    return window.location.pathname.includes('/frontend/') ? 'photos/box1__image.png' : 'frontend/photos/box1__image.png';
  }
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
    return path;
  }
  const isFrontendDir = window.location.pathname.includes('/frontend/');
  const clean = path.replace(/^\/?(frontend\/)?/, '');
  return isFrontendDir ? clean : ('frontend/' + clean);
}

const ADMIN_MANAGERS = {
  admin: {
    username: 'admin',
    name: 'Abhishek Kumar',
    role: 'Headquarters Administrator & Founder',
    hub_code: 'HQ-RANCHI',
    hub_name: 'Ranchi Mart Central Headquarters',
    hub_short: 'Main HQ & All Hubs',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80',
    phone: '+91 98765 43210'
  },
  rajesh: {
    username: 'rajesh',
    name: 'Rajesh Sharma',
    role: 'Senior Operations Head & Hub Director',
    hub_code: 'HUB-MAIN',
    hub_name: 'Central Ranchi Super Hub & Superstore',
    hub_short: 'Main Road Hub',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80',
    phone: '+91 98351 24701'
  },
  pooja: {
    username: 'pooja',
    name: 'Pooja Verma',
    role: 'Lifestyle & Fashion Hub Lead',
    hub_code: 'HUB-LALPUR',
    hub_name: 'North Ranchi Express Dispatch Center',
    hub_short: 'Lalpur Express Hub',
    avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=100&q=80',
    phone: '+91 94311 58219'
  },
  amitabh: {
    username: 'amitabh',
    name: 'Amitabh Roy',
    role: 'Electronics & Logistics Dispatch Manager',
    hub_code: 'HUB-DORANDA',
    hub_name: 'South Ranchi Regional Express Hub',
    hub_short: 'Doranda Regional Hub',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80',
    phone: '+91 97714 83620'
  },
  sunita: {
    username: 'sunita',
    name: 'Sunita Singh',
    role: 'Quality Control & Quick Store Supervisor',
    hub_code: 'HUB-KANKE',
    hub_name: 'West Ranchi Quick-Delivery Store',
    hub_short: 'Kanke Quick Store',
    avatar: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=100&q=80',
    phone: '+91 93048 71954'
  }
};

const DEFAULT_CATALOG_PRODUCTS = [
  { id: 8, name: 'Creative Notebook Bundle', category_name: 'Books & Stationary', price: 699, stock: 28, image: 'photos/box10_images.png', description: 'A mix of study essentials for the work-from-home routine.' },
  { id: 7, name: 'Cookware Starter Pack', category_name: 'Home & Kitchen', price: 1999, stock: 16, image: 'photos/box9_images.png', description: 'Essential kitchen tools for everyday cooking.' },
  { id: 6, name: 'Silk Hair Care Set', category_name: 'Hair Accessories', price: 799, stock: 22, image: 'photos/box6_images.png', description: 'Complete hair nourishment for healthy shine.' },
  { id: 5, name: 'Compact Study Desk', category_name: 'Home & Kitchen', price: 2499, stock: 12, image: 'photos/box5_image.png', description: 'Minimal desk designed for modern home workspaces.' },
  { id: 4, name: 'Minimal Relaxed Fit Tee', category_name: 'Fashion', price: 899, stock: 45, image: 'photos/box8_image.png', description: 'Soft cotton comfort with a clean modern fit.' },
  { id: 3, name: 'Smart Wireless Speaker', category_name: 'Electronics', price: 1499, stock: 19, image: 'photos/box4_image.png', description: 'Compact sound with rich bass and Bluetooth pairing.' },
  { id: 2, name: 'Daily Radiance Skincare Duo', category_name: 'Beauty', price: 1299, stock: 35, image: 'photos/box1__image.png', description: 'Skin-first beauty essentials for everyday care.' },
  { id: 1, name: 'Wellness Essentials Combo', category_name: 'Health', price: 999, stock: 40, image: 'photos/box7_image.png', description: 'Daily health support for immunity and energy.' }
];

const INITIAL_DEMO_ORDERS = [
  {
    orderCode: 'RM-847615',
    order_code: 'RM-847615',
    displayDate: 'Today, 2:30 PM',
    status: 'Out for Delivery',
    customerName: 'Abhishek Kumar',
    customerPhone: '9876543210',
    deliveryAddress: 'Circular Road, Lalpur, Ranchi - 834001',
    paymentMethod: 'Cash on delivery',
    totalAmount: 1499,
    origin_hub_code: 'HUB-MAIN',
    hub_name: 'Central Ranchi Super Hub & Superstore',
    tracking_notes: 'Rider dispatched with parcel from Central Super Hub. ETA 20 minutes.',
    updated_at: new Date().toLocaleString('en-IN'),
    items: [{ name: 'Smart Wireless Speaker', price: 1499, quantity: 1 }]
  },
  {
    orderCode: 'RM-DEMO99',
    order_code: 'RM-DEMO99',
    displayDate: 'Today, 1:15 PM',
    status: 'Confirmed',
    customerName: 'Priya Sen',
    customerPhone: '9431100223',
    deliveryAddress: 'Flat 302, Royal Enclave, Kanke Road, Ranchi - 834008',
    paymentMethod: 'Prepaid / UPI',
    totalAmount: 2298,
    origin_hub_code: 'HUB-KANKE',
    hub_name: 'West Ranchi Quick-Delivery Store',
    tracking_notes: 'Order received and verified at local dispatch desk.',
    updated_at: new Date().toLocaleString('en-IN'),
    items: [
      { name: 'Daily Radiance Skincare Duo', price: 1299, quantity: 1 },
      { name: 'Wellness Essentials Combo', price: 999, quantity: 1 }
    ]
  }
];

// Ensure initial orders in localStorage
function ensureLocalStorageOrders() {
  const existing = JSON.parse(localStorage.getItem('pikuOrders') || '[]');
  if (existing.length === 0) {
    localStorage.setItem('pikuOrders', JSON.stringify(INITIAL_DEMO_ORDERS));
  } else {
    // Make sure RM-847615 is in the list
    const hasOrder = existing.some(o => (o.orderCode || o.order_code) === 'RM-847615');
    if (!hasOrder) {
      existing.unshift(INITIAL_DEMO_ORDERS[0]);
      localStorage.setItem('pikuOrders', JSON.stringify(existing));
    }
  }
}

// Get All Orders
function getOrders() {
  ensureLocalStorageOrders();
  return JSON.parse(localStorage.getItem('pikuOrders') || '[]');
}

// Save Orders
function saveOrders(orders) {
  localStorage.setItem('pikuOrders', JSON.stringify(orders));
}

// Get Products
function getAllProducts() {
  const custom = JSON.parse(localStorage.getItem('rm_custom_products') || '[]');
  const deleted = JSON.parse(localStorage.getItem('rm_deleted_products') || '[]');
  
  const all = [...custom, ...DEFAULT_CATALOG_PRODUCTS];
  return all.filter(p => !deleted.includes(p.id) && !deleted.includes(p.name));
}

// Active Admin
function getActiveAdmin() {
  const saved = localStorage.getItem('rm_admin_user');
  return ADMIN_MANAGERS[saved] || ADMIN_MANAGERS.admin;
}

// --- AUTHENTICATION ---
function checkAuthState() {
  const isLoggedIn = localStorage.getItem('adminLoggedIn') === 'true';
  const loginScreen = document.getElementById('adminLoginScreen');
  const dashScreen = document.getElementById('adminDashboardScreen');

  if (isLoggedIn) {
    if (loginScreen) loginScreen.style.display = 'none';
    if (dashScreen) dashScreen.style.display = 'block';
    initDashboard();
  } else {
    if (loginScreen) loginScreen.style.display = 'block';
    if (dashScreen) dashScreen.style.display = 'none';
  }
}

function instantLoginManager(username) {
  const manager = ADMIN_MANAGERS[username] || ADMIN_MANAGERS.admin;
  localStorage.setItem('adminLoggedIn', 'true');
  localStorage.setItem('rm_admin_user', manager.username);
  localStorage.setItem('pikuLoggedIn', 'true');
  localStorage.setItem('pikuUser', JSON.stringify({
    name: manager.name,
    email: manager.username + '@ranchimart.com',
    role: manager.role,
    is_admin: true
  }));

  showDashAlert(`⚡ Signed in as ${manager.name} (${manager.hub_short})`, 'success');
  checkAuthState();
}

function handleManualLogin(e) {
  e.preventDefault();
  const username = (document.getElementById('loginUsername')?.value || '').trim().toLowerCase();
  const password = (document.getElementById('loginPassword')?.value || '').trim();

  const validUsers = ['admin', 'rajesh', 'pooja', 'amitabh', 'sunita', 'admin@ranchimart.com'];
  if (validUsers.includes(username) && password === 'admin123') {
    const key = (username === 'admin@ranchimart.com') ? 'admin' : username;
    instantLoginManager(key);
  } else {
    // Custom added admin?
    const customAdmins = JSON.parse(localStorage.getItem('rm_custom_admins') || '[]');
    const match = customAdmins.find(a => (a.username.toLowerCase() === username || a.email.toLowerCase() === username) && a.password === password);
    if (match) {
      ADMIN_MANAGERS[match.username] = match;
      instantLoginManager(match.username);
      return;
    }

    const alertBox = document.getElementById('loginAlertBox');
    if (alertBox) {
      alertBox.textContent = 'Invalid credentials. Password is "admin123" or click any 1-Click Manager card above.';
      alertBox.className = 'admin-message error';
      alertBox.style.display = 'block';
    }
  }
}

function handleRegisterAdmin(e) {
  e.preventDefault();
  const name = document.getElementById('regName').value.trim();
  const username = document.getElementById('regUsername').value.trim().toLowerCase();
  const email = document.getElementById('regEmail').value.trim().toLowerCase();
  const password = document.getElementById('regPassword').value.trim();
  const hubName = document.getElementById('regHubName').value.trim() || (name + ' Express Hub');
  const pincodes = document.getElementById('regPincodes').value.trim() || '834001';

  const newAdmin = {
    username,
    name,
    email,
    password,
    role: 'Hub Lead',
    hub_code: 'HUB-' + username.toUpperCase(),
    hub_name: hubName,
    hub_short: name + ' Hub',
    avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80',
    phone: '+91 98350 00000',
    pincodes
  };

  const customAdmins = JSON.parse(localStorage.getItem('rm_custom_admins') || '[]');
  customAdmins.push(newAdmin);
  localStorage.setItem('rm_custom_admins', JSON.stringify(customAdmins));
  ADMIN_MANAGERS[username] = newAdmin;

  instantLoginManager(username);
}

function handleAdminLogout() {
  localStorage.removeItem('adminLoggedIn');
  checkAuthState();
}

function switchLoginTab(tab) {
  const isLogin = tab === 'login';
  document.getElementById('tabBtnLogin')?.classList.toggle('is-active', isLogin);
  document.getElementById('tabBtnRegister')?.classList.toggle('is-active', !isLogin);
  document.getElementById('loginPanelExisting').style.display = isLogin ? 'block' : 'none';
  document.getElementById('loginPanelRegister').style.display = isLogin ? 'none' : 'block';
}

function togglePass(id, btn) {
  const input = document.getElementById(id);
  if (!input) return;
  const isPass = input.type === 'password';
  input.type = isPass ? 'text' : 'password';
  btn.innerHTML = isPass ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
}

// --- DASHBOARD INITIALIZATION ---
function initDashboard() {
  const admin = getActiveAdmin();

  // Populate Header
  document.getElementById('topbarAdminName').textContent = admin.name;
  document.getElementById('topbarAdminRole').textContent = admin.role;
  document.getElementById('topbarHubName').textContent = admin.hub_name;
  document.getElementById('topbarAvatar').src = admin.avatar;

  renderStats();
  renderOrders('all');
  renderInventory();
  renderHubs();
}

// --- STATS ---
function renderStats() {
  const orders = getOrders();
  const prods = getAllProducts();

  const total = orders.length;
  const pending = orders.filter(o => (o.status || '').toLowerCase() === 'pending').length;
  const transit = orders.filter(o => (o.status || '').toLowerCase().includes('out') || (o.status || '').toLowerCase().includes('transit') || (o.status || '').toLowerCase().includes('pack')).length;

  document.getElementById('statTotalOrders').textContent = total;
  document.getElementById('statPendingOrders').textContent = pending;
  document.getElementById('statTransitOrders').textContent = transit;
  document.getElementById('statProductsCount').textContent = prods.length;
  document.getElementById('badgePendingCount').textContent = pending;
}

// --- ORDERS MANAGEMENT ---
function renderOrders(filter = 'all') {
  const container = document.getElementById('ordersContainer');
  if (!container) return;

  const orders = getOrders();
  let filtered = orders;

  if (filter === 'pending') {
    filtered = orders.filter(o => (o.status || '').toLowerCase() === 'pending');
  } else if (filter === 'confirmed') {
    filtered = orders.filter(o => (o.status || '').toLowerCase() === 'confirmed');
  } else if (filter === 'transit') {
    filtered = orders.filter(o => (o.status || '').toLowerCase().includes('out') || (o.status || '').toLowerCase().includes('transit') || (o.status || '').toLowerCase().includes('pack'));
  } else if (filter === 'delivered') {
    filtered = orders.filter(o => (o.status || '').toLowerCase() === 'delivered');
  }

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="admin-empty-state">
        <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 12px;"></i>
        <h3>No orders found in this category</h3>
        <p>Incoming customer orders will appear here automatically.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = filtered.map(order => {
    const code = order.orderCode || order.order_code || 'RM-UNKNOWN';
    const status = order.status || 'Confirmed';
    const name = order.customerName || order.customer_name || 'Customer';
    const phone = order.customerPhone || order.customer_phone || '';
    const address = order.deliveryAddress || order.delivery_address || 'Ranchi';
    const total = Number(order.totalAmount || order.total || 0).toLocaleString('en-IN');
    const pay = order.paymentMethod || order.payment_method || 'cod';
    const notes = order.tracking_notes || 'Order verified and processing.';
    const updated = order.updated_at || order.displayDate || 'Recently';
    const items = order.items || [];

    const statusPillClass = getStatusPillClass(status);

    return `
      <div class="admin-order-card" data-order-code="${code}" data-order-status="${status.toLowerCase()}">
        <div class="order-card-header">
          <div class="order-id-group">
            <span class="order-code-badge">#${code}</span>
            <span class="order-time-text"><i class="fa-regular fa-clock"></i> Last updated: ${updated}</span>
          </div>
          <div style="display: flex; gap: 8px; align-items: center;">
            <a href="index.html?track=${code}" target="_blank" class="admin-track-preview-btn" title="Preview exact customer tracking radar">
              <i class="fa-solid fa-radar"></i> Customer View ↗
            </a>
            <span class="status-pill ${statusPillClass}">${status}</span>
          </div>
        </div>

        <div class="order-details-grid">
          <div class="order-info-block">
            <span class="block-label"><i class="fa-solid fa-user"></i> Customer Info</span>
            <strong>${name}</strong>
            <span>📞 ${phone}</span>
            <small style="color: #64748b;">${address}</small>
          </div>

          <div class="order-info-block">
            <span class="block-label"><i class="fa-solid fa-wallet"></i> Payment &amp; Value</span>
            <strong style="color: #047857; font-size: 1.1rem;">₹${total}</strong>
            <span style="text-transform: capitalize;">Mode: ${pay}</span>
            <small style="color: #64748b;">${items.length} item(s) ordered</small>
          </div>

          <div class="order-info-block" style="grid-column: span 2;">
            <span class="block-label"><i class="fa-solid fa-box"></i> Items in Parcel</span>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              ${items.map(it => `<span class="item-tag-chip">${it.name} (x${it.quantity || 1})</span>`).join('')}
            </div>
          </div>
        </div>

        <!-- Dispatch & Status Action Desk -->
        <div class="order-actions-bar">
          <div class="action-field-group">
            <label>Update Delivery Stage:</label>
            <select id="statusSelect_${code}" class="admin-select-input">
              <option value="Pending" ${status === 'Pending' ? 'selected' : ''}>⏳ Pending Confirmation</option>
              <option value="Confirmed" ${status === 'Confirmed' ? 'selected' : ''}>✓ Order Confirmed</option>
              <option value="Packed & Dispatched" ${status.includes('Packed') ? 'selected' : ''}>📦 Packed &amp; Dispatched</option>
              <option value="Out for Delivery" ${status.includes('Out') ? 'selected' : ''}>🚚 Out for Delivery</option>
              <option value="Delivered" ${status === 'Delivered' ? 'selected' : ''}>🏠 Delivered Successfully</option>
              <option value="Cancelled" ${status === 'Cancelled' ? 'selected' : ''}>✕ Cancelled</option>
            </select>
          </div>

          <div class="action-field-group" style="flex: 2;">
            <label>Delivery Update / Live Note (Customer Track Order me dikhega):</label>
            <input type="text" id="notesInput_${code}" class="admin-text-input" value="${notes}" placeholder="e.g. Rider Rahul (Ph: 98765 43210) dispatched on bike #JH01-4455" />
          </div>

          <button type="button" class="btn-update-status" onclick="applyOrderStatus('${code}')">
            <i class="fa-solid fa-check-double"></i> Update Delivery Status
          </button>
        </div>
      </div>
    `;
  }).join('');
}

function getStatusPillClass(status) {
  const s = status.toLowerCase();
  if (s === 'delivered') return 'status-delivered';
  if (s.includes('out') || s.includes('transit')) return 'status-in-transit';
  if (s.includes('pack')) return 'status-packed';
  if (s === 'pending') return 'status-pending';
  if (s === 'cancelled') return 'status-cancelled';
  return 'status-confirmed';
}

function filterOrders(type, btn) {
  document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('is-active'));
  if (btn) btn.classList.add('is-active');
  renderOrders(type);
}

// APPLY ORDER UPDATE (LIVE SYNC)
function applyOrderStatus(orderCode) {
  const statusEl = document.getElementById(`statusSelect_${orderCode}`);
  const notesEl = document.getElementById(`notesInput_${orderCode}`);
  if (!statusEl || !notesEl) return;

  const newStatus = statusEl.value;
  const newNotes = notesEl.value.trim() || `Status updated to ${newStatus}`;
  const nowStr = new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

  // Update in localStorage
  const orders = getOrders();
  const idx = orders.findIndex(o => (o.orderCode || o.order_code) === orderCode);
  if (idx !== -1) {
    orders[idx].status = newStatus;
    orders[idx].tracking_notes = newNotes;
    orders[idx].updated_at = nowStr;
    saveOrders(orders);
  }

  // Also try updating backend SQLite API in background
  try {
    fetch('/backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'update_order_status',
        order_code: orderCode,
        status: newStatus,
        tracking_notes: newNotes
      })
    }).catch(() => {});
  } catch (_) {}

  showDashAlert(`✓ Order #${orderCode} delivery status updated to "${newStatus}"! Visible on customer Track Order.`, 'success');
  renderStats();
  renderOrders('all');
}

// --- PRODUCT CATALOG (ADD & DELETE) ---
function renderInventory() {
  const tbody = document.getElementById('inventoryTableBody');
  if (!tbody) return;

  const prods = getAllProducts();
  if (prods.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 24px; color: #64748b;">No products in catalog. Click "Add New Product" to create one.</td></tr>`;
    return;
  }

  tbody.innerHTML = prods.map(p => {
    const img = resolveAssetPath(p.image || 'photos/box1__image.png');
    const fallback = resolveAssetPath('photos/box1__image.png');
    const price = Number(p.price || 0).toLocaleString('en-IN');
    const stock = Number(p.stock || 0);

    return `
      <tr class="product-data-row">
        <td>
          <img src="${img}" alt="${p.name}" class="table-prod-thumb" onerror="this.src='${fallback}'" />
        </td>
        <td>
          <strong>${p.name}</strong>
          <small class="table-prod-desc">${p.description || ''}</small>
        </td>
        <td><span class="category-chip">${p.category_name || 'General'}</span></td>
        <td><strong>₹${price}</strong></td>
        <td>
          <span class="stock-badge ${stock < 15 ? 'stock-low' : 'stock-ok'}">${stock} in stock</span>
        </td>
        <td><span class="status-pill status-delivered">Live</span></td>
        <td style="text-align: center;">
          <button type="button" class="btn-delete-prod" onclick="handleDeleteProduct(${p.id || 0}, '${p.name.replace(/'/g, "\\'")}')" title="Delete product from storefront">
            <i class="fa-solid fa-trash-can"></i> Delete
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function handleAddNewProduct(e) {
  e.preventDefault();
  const name = document.getElementById('newProdName').value.trim();
  const category = document.getElementById('newProdCategory').value;
  const price = Number(document.getElementById('newProdPrice').value || 0);
  const stock = Number(document.getElementById('newProdStock').value || 10);
  const gender = document.getElementById('newProdGender').value;
  const directUrl = document.getElementById('newProdImageUrl').value.trim();
  const preset = document.getElementById('selectedPresetImg').value;
  const desc = document.getElementById('newProdDesc').value.trim();

  let finalImage = directUrl || preset || 'photos/box1__image.png';

  const newProduct = {
    id: Date.now(),
    name,
    category_name: category,
    price,
    stock,
    gender,
    image: finalImage,
    description: desc,
    created_at: new Date().toISOString()
  };

  // 1. Save to LocalStorage
  const custom = JSON.parse(localStorage.getItem('rm_custom_products') || '[]');
  custom.unshift(newProduct);
  localStorage.setItem('rm_custom_products', JSON.stringify(custom));

  // 2. Try background API sync
  try {
    fetch('/backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'add_product',
        name,
        category_name: category,
        price,
        stock,
        image_url: finalImage,
        description: desc
      })
    }).catch(() => {});
  } catch (_) {}

  // Reset form
  document.getElementById('addProductForm').reset();
  showDashAlert(`🎉 Product "${name}" published successfully! It now appears on the customer storefront.`, 'success');

  renderStats();
  renderInventory();
  switchAdminTab('tabProducts');
}

function handleDeleteProduct(id, name) {
  if (!confirm(`Are you sure you want to delete "${name}" from the store catalog?`)) return;

  // 1. Save to deleted list in localStorage
  const deleted = JSON.parse(localStorage.getItem('rm_deleted_products') || '[]');
  if (id) deleted.push(id);
  deleted.push(name);
  localStorage.setItem('rm_deleted_products', JSON.stringify(deleted));

  // 2. Also remove from rm_custom_products
  let custom = JSON.parse(localStorage.getItem('rm_custom_products') || '[]');
  custom = custom.filter(p => p.id !== id && p.name !== name);
  localStorage.setItem('rm_custom_products', JSON.stringify(custom));

  // 3. Try background API delete
  try {
    fetch('/backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_product', product_id: id, name })
    }).catch(() => {});
  } catch (_) {}

  showDashAlert(`Product "${name}" deleted from store catalog.`, 'success');
  renderStats();
  renderInventory();
}

function selectPresetImg(path, btn) {
  document.getElementById('selectedPresetImg').value = path;
  document.querySelectorAll('.preset-img-chip').forEach(b => b.classList.remove('is-selected'));
  if (btn) btn.classList.add('is-selected');
}

function previewSelectedFile(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('selectedPresetImg').value = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function filterInventoryTable() {
  const q = (document.getElementById('inventorySearchInput')?.value || '').toLowerCase().trim();
  const rows = document.querySelectorAll('.product-data-row');
  rows.forEach(r => {
    const txt = r.textContent.toLowerCase();
    r.style.display = txt.includes(q) ? '' : 'none';
  });
}

// --- PHYSICAL HUBS ---
function renderHubs() {
  const grid = document.getElementById('hubsGrid');
  if (!grid) return;

  const hubs = Object.values(ADMIN_MANAGERS);
  grid.innerHTML = hubs.map(h => `
    <div class="admin-hub-card">
      <div class="hub-card-header">
        <div>
          <span class="hub-code-pill">${h.hub_code}</span>
          <h3>${h.hub_short}</h3>
          <p class="hub-full-name">${h.hub_name}</p>
        </div>
        <img src="${h.avatar}" alt="${h.name}" class="hub-mgr-avatar" />
      </div>

      <div class="hub-details-list">
        <div><i class="fa-solid fa-user-tie"></i> <strong>Manager:</strong> ${h.name} (${h.role})</div>
        <div><i class="fa-solid fa-phone"></i> <strong>Contact:</strong> ${h.phone}</div>
        <div><i class="fa-solid fa-bolt"></i> <strong>Speed:</strong> 20 - 35 Mins Express Delivery</div>
        <div><i class="fa-solid fa-star text-amber"></i> <strong>Rating:</strong> 4.9 ★ (Verified Hub)</div>
      </div>
    </div>
  `).join('');
}

// --- TAB SWITCHER ---
function switchAdminTab(tabId) {
  document.querySelectorAll('.admin-tab-content').forEach(el => el.classList.remove('active-content'));
  document.querySelectorAll('.admin-tab-btn').forEach(el => el.classList.remove('is-active'));

  const target = document.getElementById(tabId);
  if (target) target.classList.add('active-content');

  if (tabId === 'tabOrders') document.getElementById('btnTabOrders')?.classList.add('is-active');
  if (tabId === 'tabAddProduct') document.getElementById('btnTabAddProduct')?.classList.add('is-active');
  if (tabId === 'tabProducts') document.getElementById('btnTabProducts')?.classList.add('is-active');
  if (tabId === 'tabHubs') document.getElementById('btnTabHubs')?.classList.add('is-active');
}

// --- ALERTS ---
function showDashAlert(msg, type = 'success') {
  const box = document.getElementById('dashAlertBox');
  if (!box) return;
  box.textContent = msg;
  box.className = `admin-message ${type}`;
  box.style.display = 'block';
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

  setTimeout(() => {
    box.style.display = 'none';
  }, 6000);
}

// --- BOOTSTRAP ---
document.addEventListener('DOMContentLoaded', () => {
  ensureLocalStorageOrders();
  checkAuthState();
});
