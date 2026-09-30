const cart = JSON.parse(localStorage.getItem('pikuCart') || '[]');
const categoryProducts = {
  'Health and personal care': [
    { name: 'Daily Wellness Pack', price: 799 },
    { name: 'Protein Nutrition Box', price: 999 },
    { name: 'Face Cleansing Kit', price: 599 },
    { name: 'Vitamin Supplement', price: 449 },
    { name: 'Essential Care Combo', price: 699 },
    { name: 'Body Care Essentials', price: 899 }
  ],
  'BeautyPicks': [
    { name: 'Radiant Glow Serum', price: 499 },
    { name: 'Hydrating Face Cream', price: 649 },
    { name: 'Vitamin C Mist', price: 399 },
    { name: 'Rose Lip Balm', price: 199 },
    { name: 'Silk Makeup Brush Set', price: 899 },
    { name: 'Luxury Nail Kit', price: 759 }
  ],
  'Electronics': [
    { name: 'Wireless Earbuds', price: 1499 },
    { name: 'Bluetooth Speaker', price: 1999 },
    { name: 'Smart Watch', price: 2499 },
    { name: 'USB-C Charger', price: 499 },
    { name: 'Laptop Stand', price: 899 },
    { name: 'Mini Projector', price: 3299 }
  ],
  'Clothes': [
    { name: 'Casual Cotton Tee', price: 699 },
    { name: 'Classic Denim Shirt', price: 1199 },
    { name: 'Running Joggers', price: 999 },
    { name: 'Winter Hoodie', price: 1499 },
    { name: 'Lightweight Jacket', price: 1899 },
    { name: 'Cotton Kurta Set', price: 1699 }
  ],
  'Furniture': [
    { name: 'Accent Chair', price: 3299 },
    { name: 'Wooden Side Table', price: 2599 },
    { name: 'Storage Bench', price: 2999 },
    { name: 'Study Desk', price: 4299 },
    { name: 'Cozy Lounge Cushion', price: 899 },
    { name: 'Modern Lamp', price: 1499 }
  ],
  'Hair Accessories': [
    { name: 'Silk Scrunchie Set', price: 299 },
    { name: 'Hair Clip Combo', price: 399 },
    { name: 'Bamboo Hair Brush', price: 499 },
    { name: 'Velvet Headband', price: 349 },
    { name: 'Premium Styling Tool', price: 1299 },
    { name: 'Floral Hair Pins', price: 279 }
  ],
  'Home & Kitchen': [
    { name: 'Cookware Set', price: 2199 },
    { name: 'Aroma Diffuser', price: 1299 },
    { name: 'Kitchen Storage Basket', price: 799 },
    { name: 'Ceramic Mug Set', price: 899 },
    { name: 'Air Fryer Tray', price: 999 },
    { name: 'Chopping Board Set', price: 749 }
  ],
  'Books & Stationary': [
    { name: 'Creative Notebook', price: 299 },
    { name: 'Premium Sketch Kit', price: 599 },
    { name: 'Business Planner', price: 499 },
    { name: 'Fiction Best Sellers', price: 399 },
    { name: 'Desk Organizer Set', price: 799 },
    { name: 'Color Pen Set', price: 549 }
  ]
};

function getCartTotal() {
  return cart.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 0), 0);
}

function renderCart() {
  const cartItems = document.getElementById('cartItems');
  const cartTotal = document.getElementById('cartTotal');
  const cartCount = document.getElementById('cartCount');

  if (cartCount) {
    cartCount.textContent = cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
  }

  if (!cartItems || !cartTotal) return;

  if (!cart.length) {
    cartItems.innerHTML = '<p class="empty-cart">Your cart is empty.</p>';
    cartTotal.textContent = '₹0';
    return;
  }

  cartItems.innerHTML = cart
    .map((item) => `
      <div class="cart-item-row">
        <div class="cart-item-meta">
          <strong>${item.name}</strong>
          <div class="quantity-control">
            <button type="button" data-cart-action="decrease" data-product-name="${item.name}" aria-label="Decrease ${item.name} quantity">−</button>
            <span>${item.quantity}</span>
            <button type="button" data-cart-action="increase" data-product-name="${item.name}" aria-label="Increase ${item.name} quantity">+</button>
          </div>
        </div>
        <div class="cart-item-price"><strong>₹${(Number(item.price || 0) * Number(item.quantity || 0))}</strong><button type="button" data-cart-action="remove" data-product-name="${item.name}" aria-label="Remove ${item.name}">Remove</button></div>
      </div>
    `)
    .join('');

  cartTotal.textContent = `₹${getCartTotal()}`;
}

function updateCartUI() {
  renderCart();
  const cartMessage = document.getElementById('cartMessage');
  if (cartMessage) {
    cartMessage.classList.remove('show');
  }
}

function openCart() {
  const cartModal = document.getElementById('cartModal');
  if (cartModal) {
    renderCart();
    cartModal.classList.add('show');
  }
}

function closeCart() {
  const cartModal = document.getElementById('cartModal');
  if (cartModal) {
    cartModal.classList.remove('show');
  }
}

function showCartMessage(message) {
  const cartMessage = document.getElementById('cartMessage');
  if (!cartMessage) return;
  cartMessage.textContent = message;
  cartMessage.classList.add('show');
  clearTimeout(showCartMessage.timeoutId);
  showCartMessage.timeoutId = setTimeout(() => cartMessage.classList.remove('show'), 2200);
}

function addToCart(name, price) {
  const existingItem = cart.find((item) => item.name === name);

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({ name, price, quantity: 1 });
  }

  localStorage.setItem('pikuCart', JSON.stringify(cart));
  renderCart();
  openCart();
  showCartMessage(`${name} added to cart.`);
}

function handleBuyButtonClick(button) {
  const name = button.dataset.product || 'Product';
  const price = Number(button.dataset.price || 0);
  addToCart(name, price);
}

function getProductImage(categoryName, itemName) {
  const itemKey = (itemName || '').trim();

  const productImageMap = {
    'Daily Wellness Pack': 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
    'Protein Nutrition Box': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80',
    'Face Cleansing Kit': 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=900&q=80',
    'Vitamin Supplement': 'https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?auto=format&fit=crop&w=900&q=80',
    'Essential Care Combo': 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
    'Body Care Essentials': 'https://images.unsplash.com/photo-1528740561666-dc2479dc08ab?auto=format&fit=crop&w=900&q=80',
    'Radiant Glow Serum': 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
    'Hydrating Face Cream': 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
    'Vitamin C Mist': 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=900&q=80',
    'Rose Lip Balm': 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?auto=format&fit=crop&w=900&q=80',
    'Silk Makeup Brush Set': 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
    'Luxury Nail Kit': 'https://images.unsplash.com/photo-1596462502278-27bfdc403cfc?auto=format&fit=crop&w=900&q=80',
    'Wireless Earbuds': 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=900&q=80',
    'Bluetooth Speaker': 'https://images.unsplash.com/photo-1518444065439-e933c06ce9cd?auto=format&fit=crop&w=900&q=80',
    'Smart Watch': 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80',
    'USB-C Charger': 'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=900&q=80',
    'Laptop Stand': 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=900&q=80',
    'Mini Projector': 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=900&q=80',
    'Casual Cotton Tee': 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80',
    'Classic Denim Shirt': 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
    'Running Joggers': 'https://images.unsplash.com/photo-1554568218-0f1715e72254?auto=format&fit=crop&w=900&q=80',
    'Winter Hoodie': 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
    'Lightweight Jacket': 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=80',
    'Cotton Kurta Set': 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=900&q=80',
    'Accent Chair': 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    'Wooden Side Table': 'https://images.unsplash.com/photo-1533090481720-856c6e3c1fdc?auto=format&fit=crop&w=900&q=80',
    'Storage Bench': 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    'Study Desk': 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    'Cozy Lounge Cushion': 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    'Modern Lamp': 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    'Silk Scrunchie Set': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'Hair Clip Combo': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'Bamboo Hair Brush': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'Velvet Headband': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'Premium Styling Tool': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'Floral Hair Pins': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'Cookware Set': 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=900&q=80',
    'Aroma Diffuser': 'https://images.unsplash.com/photo-1517705008128-361805f42e86?auto=format&fit=crop&w=900&q=80',
    'Kitchen Storage Basket': 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=900&q=80',
    'Ceramic Mug Set': 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&w=900&q=80',
    'Air Fryer Tray': 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=900&q=80',
    'Chopping Board Set': 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=900&q=80',
    'Creative Notebook': 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80',
    'Premium Sketch Kit': 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=900&q=80',
    'Business Planner': 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80',
    'Fiction Best Sellers': 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80',
    'Desk Organizer Set': 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=900&q=80',
    'Color Pen Set': 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=900&q=80'
  };

  if (itemKey && productImageMap[itemKey]) {
    return productImageMap[itemKey] + '&sig=' + encodeURIComponent(itemKey);
  }

  const categoryImages = {
    'Health and personal care': 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=800&q=80',
    'BeautyPicks': 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80',
    'Electronics': 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
    'Clothes': 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&q=80',
    'Furniture': 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80',
    'Hair Accessories': 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80',
    'Home & Kitchen': 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80',
    'Books & Stationary': 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80'
  };

  const fallback = categoryImages[categoryName] || 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=800&q=80';
  return fallback + '&sig=' + encodeURIComponent(itemKey || categoryName || 'item');
}

function renderBeautyProducts(categoryName = 'BeautyPicks') {
  const beautyProductList = document.getElementById('beautyProductList');
  const categoryHeading = document.querySelector('.product-modal-header h3');
  const products = categoryProducts[categoryName] || categoryProducts['BeautyPicks'];

  if (categoryHeading) {
    categoryHeading.textContent = categoryName;
  }

  if (!beautyProductList) return;

  beautyProductList.innerHTML = products
    .map(
      (item) => {
        const productImage = item.image || getProductImage(categoryName, item.name);
        return `
          <div class="beauty-product-card">
            <div class="beauty-product-tag">Featured Item</div>
            <img class="beauty-product-image" src="${productImage}" alt="${item.name}" />
            <h4 class="beauty-product-name">${item.name}</h4>
            <div class="beauty-product-info">
              <span class="beauty-product-label">Price</span>
              <strong class="beauty-product-price">₹${item.price}</strong>
            </div>
            <button type="button" class="beauty-buy-btn" data-name="${item.name}" data-price="${item.price}">Buy now</button>
          </div>
        `;
      }
    )
    .join('');
}

function openBeautyProductsModal(categoryName = 'BeautyPicks') {
  const modal = document.getElementById('beautyProductsModal');
  if (!modal) return;
  renderBeautyProducts(categoryName);
  modal.classList.add('show');
}

function closeBeautyProductsModal() {
  const modal = document.getElementById('beautyProductsModal');
  if (modal) {
    modal.classList.remove('show');
  }
}

function getSavedDeliveryAddress() {
  const custom = localStorage.getItem('pikuDeliveryAddress');
  if (custom) {
    try { return JSON.parse(custom); } catch (_) {}
  }
  const user = localStorage.getItem('pikuUser');
  if (user) {
    try {
      const parsed = JSON.parse(user);
      if (parsed && (parsed.address || parsed.name || parsed.phone)) {
        return {
          name: parsed.name || '',
          phone: parsed.phone || '',
          street: parsed.address || '',
          area: 'Main Road',
          city: 'Ranchi',
          pincode: '834001'
        };
      }
    } catch (_) {}
  }
  return null;
}

function saveDeliveryAddress(addr) {
  if (!addr) return;
  localStorage.setItem('pikuDeliveryAddress', JSON.stringify(addr));
}

function populateDeliveryAddress() {
  const saved = getSavedDeliveryAddress();
  const savedBox = document.getElementById('modalSavedAddressBox');
  const fields = document.getElementById('modalAddressFields');
  const toggleBtn = document.getElementById('btnToggleAddAddress');

  const nameInput = document.getElementById('deliveryName');
  const phoneInput = document.getElementById('deliveryPhone');
  const streetInput = document.getElementById('deliveryStreet');
  const areaInput = document.getElementById('deliveryArea');
  const cityInput = document.getElementById('deliveryCity');
  const pincodeInput = document.getElementById('deliveryPincode');

  if (saved && (saved.street || saved.address)) {
    const fullStreet = saved.street || saved.address || '';
    const fullArea = saved.area || 'Main Road';
    const city = saved.city || 'Ranchi';
    const pincode = saved.pincode || '834001';

    if (savedBox) {
      document.getElementById('savedReceiverName').textContent = saved.name || 'User';
      document.getElementById('savedReceiverAddress').textContent = `${fullStreet}, ${fullArea}, ${city} - ${pincode}`;
      document.getElementById('savedReceiverPhone').textContent = `📞 ${saved.phone || ''}`;
      savedBox.style.display = 'block';
    }
    if (fields) fields.style.display = 'none';
    if (toggleBtn) toggleBtn.textContent = '✎ Change / Edit Delivery Address';

    if (nameInput) nameInput.value = saved.name || '';
    if (phoneInput) phoneInput.value = saved.phone || '';
    if (streetInput) streetInput.value = fullStreet;
    if (areaInput) areaInput.value = fullArea;
    if (cityInput) cityInput.value = city;
    if (pincodeInput) pincodeInput.value = pincode;
  } else {
    if (savedBox) savedBox.style.display = 'none';
    if (fields) fields.style.display = 'grid';
    if (toggleBtn) toggleBtn.textContent = 'Hide Address Form';

    const user = JSON.parse(localStorage.getItem('pikuUser') || 'null');
    if (user) {
      if (nameInput && !nameInput.value) nameInput.value = user.name || '';
      if (phoneInput && !phoneInput.value) phoneInput.value = user.phone || '';
      if (streetInput && !streetInput.value) streetInput.value = user.address || '';
    }
  }
}

function toggleAddressForm() {
  const fields = document.getElementById('modalAddressFields');
  const toggleBtn = document.getElementById('btnToggleAddAddress');
  if (!fields) return;

  const isHidden = fields.style.display === 'none';
  fields.style.display = isHidden ? 'grid' : 'none';
  if (toggleBtn) {
    toggleBtn.textContent = isHidden ? 'Hide Address Form' : '✎ Change / Edit Delivery Address';
  }
}

function toggleCheckoutAddressForm() {
  const fields = document.getElementById('checkoutAddressFields');
  const toggleBtn = document.getElementById('btnCheckoutToggleAddress');
  if (!fields) return;

  const isHidden = fields.style.display === 'none';
  fields.style.display = isHidden ? 'grid' : 'none';
  if (toggleBtn) {
    toggleBtn.textContent = isHidden ? 'Hide Address Form' : '✎ Change / Edit Delivery Address';
  }
}

function checkout() {
  if (!cart.length) {
    showCartMessage('Your cart is empty.');
    return;
  }

  const loginState = document.body.dataset.loggedIn;
  const hasLocalLogin = localStorage.getItem('pikuLoggedIn') === 'true';
  if (loginState === 'false' && !hasLocalLogin) {
    showCartMessage('Sign in before checkout.');
    openAuthModal('login');
    return;
  }

  closeCart();
  if (document.getElementById('paymentModal')) {
    populateDeliveryAddress();
    openModal('paymentModal');
    return;
  }
  window.location.href = '/frontend/login.php?checkout=1';
}

function initializeCheckoutPage() {
  const items = document.getElementById('checkoutItems');
  const total = document.getElementById('checkoutTotal');
  const count = document.getElementById('checkoutCount');
  const form = document.getElementById('checkoutPaymentForm');
  const cardFields = document.getElementById('cardFields');
  const codNotice = document.getElementById('checkoutCodNotice');
  const success = document.getElementById('checkoutSuccess');
  const successDetail = document.getElementById('checkoutSuccessDetail');
  const isLoggedIn = document.body.dataset.loggedIn === 'true' || localStorage.getItem('pikuLoggedIn') === 'true';

  if (!isLoggedIn) {
    window.location.href = '/frontend/index.html';
    return;
  }

  if (!cart.length) {
    items.innerHTML = '<p class="checkout-empty">Your cart is empty. <a href="/frontend/index.html">Return to shopping.</a></p>';
    return;
  }

  items.innerHTML = cart.map((item) => `<div class="checkout-item"><div><strong>${item.name}</strong><span>Qty ${item.quantity}</span></div><strong>₹${Number(item.price || 0) * Number(item.quantity || 0)}</strong></div>`).join('');
  total.textContent = `₹${getCartTotal().toLocaleString('en-IN')}`;
  count.textContent = `${cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0)} items`;

  const saved = getSavedDeliveryAddress();
  const savedBox = document.getElementById('checkoutSavedAddressBox');
  const addressFields = document.getElementById('checkoutAddressFields');
  const toggleBtn = document.getElementById('btnCheckoutToggleAddress');

  const nameInput = document.getElementById('checkoutDeliveryName');
  const phoneInput = document.getElementById('checkoutDeliveryPhone');
  const streetInput = document.getElementById('checkoutDeliveryStreet');
  const areaInput = document.getElementById('checkoutDeliveryArea');
  const cityInput = document.getElementById('checkoutDeliveryCity');
  const pincodeInput = document.getElementById('checkoutDeliveryPincode');

  if (saved && (saved.street || saved.address)) {
    const fullStreet = saved.street || saved.address || '';
    const fullArea = saved.area || 'Main Road';
    const city = saved.city || 'Ranchi';
    const pincode = saved.pincode || '834001';

    if (savedBox) {
      document.getElementById('checkoutReceiverName').textContent = saved.name || 'User';
      document.getElementById('checkoutReceiverAddress').textContent = `${fullStreet}, ${fullArea}, ${city} - ${pincode}`;
      document.getElementById('checkoutReceiverPhone').textContent = `📞 ${saved.phone || ''}`;
      savedBox.style.display = 'block';
    }
    if (addressFields) addressFields.style.display = 'none';
    if (toggleBtn) toggleBtn.textContent = '✎ Change / Edit Delivery Address';

    if (nameInput) nameInput.value = saved.name || '';
    if (phoneInput) phoneInput.value = saved.phone || '';
    if (streetInput) streetInput.value = fullStreet;
    if (areaInput) areaInput.value = fullArea;
    if (cityInput) cityInput.value = city;
    if (pincodeInput) pincodeInput.value = pincode;
  } else {
    if (savedBox) savedBox.style.display = 'none';
    if (addressFields) addressFields.style.display = 'grid';
    if (toggleBtn) toggleBtn.textContent = 'Hide Address Form';

    const user = JSON.parse(localStorage.getItem('pikuUser') || 'null');
    if (user) {
      if (nameInput && !nameInput.value) nameInput.value = user.name || '';
      if (phoneInput && !phoneInput.value) phoneInput.value = user.phone || '';
      if (streetInput && !streetInput.value) streetInput.value = user.address || '';
    }
  }

  form.querySelectorAll('input[name="method"]').forEach((method) => {
    method.addEventListener('change', () => {
      const isCard = method.value === 'card' && method.checked;
      if (cardFields) cardFields.style.display = isCard ? 'grid' : 'none';
      if (codNotice) codNotice.style.display = isCard ? 'none' : 'block';
      if (cardFields) cardFields.querySelectorAll('input').forEach((field) => { field.required = isCard; });
    });
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(form);
    const delName = String(formData.get('deliveryName') || '').trim();
    const delPhone = String(formData.get('deliveryPhone') || '').trim();
    const delStreet = String(formData.get('deliveryStreet') || '').trim();
    const delArea = String(formData.get('deliveryArea') || '').trim();
    const delCity = String(formData.get('deliveryCity') || 'Ranchi').trim();
    const delPincode = String(formData.get('deliveryPincode') || '').trim();
    const payMethod = String(formData.get('method') || 'card');

    if (!delName || !delPhone || !delStreet) {
      alert('Please fill in your Delivery Address completely.');
      if (addressFields) addressFields.style.display = 'grid';
      return;
    }

    const addr = {
      name: delName,
      phone: delPhone,
      street: delStreet,
      area: delArea,
      city: delCity,
      pincode: delPincode
    };
    saveDeliveryAddress(addr);

    const orderCode = 'RM-' + Math.floor(100000 + Math.random() * 900000);
    const totalAmt = getCartTotal();
    const newOrder = {
      orderCode: orderCode,
      order_code: orderCode,
      displayDate: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
      status: 'Confirmed',
      customerName: delName,
      customerPhone: delPhone,
      deliveryAddress: `${delStreet}, ${delArea}, ${delCity} - ${delPincode}`,
      paymentMethod: payMethod,
      totalAmount: totalAmt,
      items: [...cart]
    };
    saveOrderToHistory(newOrder);

    fetch('/backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'create_order',
        order_code: orderCode,
        customer_name: delName,
        customer_phone: delPhone,
        delivery_address: newOrder.deliveryAddress,
        total_amount: totalAmt,
        payment_method: payMethod,
        items: cart
      })
    }).catch(() => {});

    cart.length = 0;
    localStorage.removeItem('pikuCart');
    form.hidden = true;
    if (successDetail) {
      successDetail.innerHTML = `Order Code: <strong>#${orderCode}</strong><br>Delivering to: <strong>${delName}</strong> (${delPhone})<br>${delStreet}, ${delArea}, ${delCity} - ${delPincode}<br>Payment method: <em>${payMethod === 'cod' ? 'Cash on Delivery (Pay at door)' : 'Card payment'}</em>`;
    }
    success.hidden = false;
  });
}

// --- Order Tracking Helpers ---
function getSavedOrders() {
  try {
    const list = JSON.parse(localStorage.getItem('pikuOrders') || '[]');
    if (list.length === 0) {
      const initial = [
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
          updated_at: 'Today, 2:30 PM',
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
          updated_at: 'Today, 1:15 PM',
          items: [
            { name: 'Daily Radiance Skincare Duo', price: 1299, quantity: 1 },
            { name: 'Wellness Essentials Combo', price: 999, quantity: 1 }
          ]
        }
      ];
      localStorage.setItem('pikuOrders', JSON.stringify(initial));
      return initial;
    }
    return list;
  } catch (_) {
    return [];
  }
}

function saveOrderToHistory(order) {
  const orders = getSavedOrders();
  orders.unshift(order);
  localStorage.setItem('pikuOrders', JSON.stringify(orders));
  localStorage.setItem('lastPlacedOrderCode', order.orderCode);
}

function getDemoOrder() {
  return {
    orderCode: 'RM-DEMO99',
    order_code: 'RM-DEMO99',
    displayDate: 'Today, 2:30 PM',
    status: 'Out for Delivery',
    customerName: 'Abhishek Kumar',
    customerPhone: '+91 98765 43210',
    deliveryAddress: 'Flat 402, Royal Residency, Kanke Road, Ranchi - 834008',
    paymentMethod: 'Cash on delivery',
    totalAmount: 1499,
    courierName: 'Ramesh Kumar (Ranchi Mart Express)',
    items: [
      { name: 'Smart Wireless Speaker', price: 1499, quantity: 1 }
    ]
  };
}

function renderTrackingDetails(order) {
  const card = document.getElementById('trackingResultCard');
  const notFound = document.getElementById('trackingNotFound');
  if (!card) return;

  const code = order.orderCode || order.order_code || 'RM-UNKNOWN';
  const name = order.customerName || order.customer_name || order.address?.name || 'Customer';
  const phone = order.customerPhone || order.customer_phone || order.address?.phone || '';
  const addr = order.deliveryAddress || order.delivery_address || (order.address ? `${order.address.street}, ${order.address.area}, ${order.address.city} - ${order.address.pincode}` : 'Ranchi, Jharkhand');
  const payMethod = order.paymentMethod || order.payment_method || 'cod';
  const total = order.totalAmount || order.total_amount || order.total || 0;
  const items = Array.isArray(order.items) ? order.items : [];
  const status = order.status || 'Confirmed';

  const orderCodeEl = document.getElementById('trackOrderCode');
  if (orderCodeEl) orderCodeEl.textContent = '#' + code.replace(/^#/, '');
  const orderDateEl = document.getElementById('trackOrderDate');
  if (orderDateEl) orderDateEl.textContent = 'Placed on ' + (order.displayDate || order.created_at || 'Recently');
  const receiverNameEl = document.getElementById('trackReceiverName');
  if (receiverNameEl) receiverNameEl.textContent = name;
  const fullAddrEl = document.getElementById('trackFullAddress');
  if (fullAddrEl) fullAddrEl.textContent = addr;
  const phoneEl = document.getElementById('trackPhone');
  if (phoneEl) phoneEl.textContent = phone ? '📞 ' + phone : '';
  const payMethodEl = document.getElementById('trackPayMethod');
  if (payMethodEl) payMethodEl.textContent = payMethod === 'cod' ? 'Cash on Delivery (Pay at doorstep)' : 'Credit / Debit Card (Paid)';
  const totalAmountEl = document.getElementById('trackTotalAmount');
  if (totalAmountEl) totalAmountEl.textContent = '₹' + Number(total).toLocaleString('en-IN');

  // Dedicated Live Delivery Status & Tracking Notes Callout Box
  const liveStatusTitleEl = document.getElementById('trackLiveStatusTitle');
  const liveNotesEl = document.getElementById('trackLiveNotes');
  const updateTimestampEl = document.getElementById('trackUpdateTimestamp');

  if (liveStatusTitleEl) {
    liveStatusTitleEl.textContent = 'Order Status: ' + status;
  }
  if (liveNotesEl) {
    const note = order.tracking_notes || '';
    liveNotesEl.textContent = note ? note : 'Order verified and confirmed. Preparing for local hub dispatch.';
  }
  if (updateTimestampEl) {
    const updatedStr = order.updated_at_formatted || order.updated_at;
    updateTimestampEl.innerHTML = '<i class="fa-regular fa-clock"></i> ' + (updatedStr ? 'Updated: ' + updatedStr : 'Just updated');
  }

  const hubNameEl = document.getElementById('trackHubName');
  const hubManagerEl = document.getElementById('trackHubManager');
  const trackingNotesEl = document.getElementById('trackTrackingNotes');
  if (hubNameEl) {
    const hubLocation = [order.hub_name, order.hub_address].filter(Boolean).join(' - ');
    hubNameEl.textContent = 'Current dispatch location: ' + (hubLocation || order.origin_hub_code || 'Central Ranchi Super Hub');
  }
  if (hubManagerEl) {
    hubManagerEl.textContent = 'Verified Hub Director: ' + (order.hub_manager || 'Rajesh Sharma (+91 98351 24701) • 100% Quality Inspected');
  }
  if (trackingNotesEl) {
    const note = order.tracking_notes || '';
    trackingNotesEl.textContent = note ? 'Latest update: ' + note : 'No additional location update yet.';
  }

  const itemsContainer = document.getElementById('trackItemsList');
  if (itemsContainer) {
    if (items.length) {
      itemsContainer.innerHTML = items.map((it) => `
        <div class="track-item-row">
          <span>${it.name || 'Product'} × ${it.quantity || 1}</span>
          <strong>₹${((it.price || 0) * (it.quantity || 1)).toLocaleString('en-IN')}</strong>
        </div>
      `).join('');
    } else {
      itemsContainer.innerHTML = '<div class="track-item-row"><span>Ranchi Mart Curated Item × 1</span><strong>₹' + Number(total).toLocaleString('en-IN') + '</strong></div>';
    }
  }

  const stepPlaced = document.getElementById('stepPlaced');
  const stepPacked = document.getElementById('stepPacked');
  const stepTransit = document.getElementById('stepTransit');
  const stepDelivered = document.getElementById('stepDelivered');
  const line1 = document.getElementById('line1');
  const line2 = document.getElementById('line2');
  const line3 = document.getElementById('line3');
  const badge = document.getElementById('trackStatusBadge');
  const etaText = document.getElementById('trackEtaText');

  [stepPlaced, stepPacked, stepTransit, stepDelivered].forEach(el => {
    if (el) el.className = 'step-item';
  });
  [line1, line2, line3].forEach(el => {
    if (el) el.className = 'step-line';
  });

  const normStatus = status.toLowerCase();
  if (normStatus.includes('cancel')) {
    if (stepPlaced) stepPlaced.className = 'step-item';
    if (stepPacked) stepPacked.className = 'step-item';
    if (stepTransit) stepTransit.className = 'step-item';
    if (stepDelivered) stepDelivered.className = 'step-item';
    if (line1) line1.className = 'step-line';
    if (line2) line2.className = 'step-line';
    if (line3) line3.className = 'step-line';
    if (badge) { badge.className = 'status-pill status-cancelled'; badge.textContent = '● Cancelled'; }
    if (etaText) etaText.textContent = 'Order Cancelled';
    if (liveStatusTitleEl) liveStatusTitleEl.textContent = 'Order Status: Cancelled';
  } else if (normStatus.includes('deliver') && !normStatus.includes('out')) {
    if (stepPlaced) stepPlaced.className = 'step-item step-completed';
    if (stepPacked) stepPacked.className = 'step-item step-completed';
    if (stepTransit) stepTransit.className = 'step-item step-completed';
    if (stepDelivered) stepDelivered.className = 'step-item step-completed';
    if (line1) line1.className = 'step-line step-line-active';
    if (line2) line2.className = 'step-line step-line-active';
    if (line3) line3.className = 'step-line step-line-active';
    if (badge) { badge.className = 'status-pill status-delivered'; badge.textContent = '● Delivered'; }
    if (etaText) etaText.textContent = 'Delivered to your doorstep';
  } else if (normStatus.includes('out') || normStatus.includes('transit')) {
    if (stepPlaced) stepPlaced.className = 'step-item step-completed';
    if (stepPacked) stepPacked.className = 'step-item step-completed';
    if (stepTransit) stepTransit.className = 'step-item step-active';
    if (stepDelivered) stepDelivered.className = 'step-item';
    if (line1) line1.className = 'step-line step-line-active';
    if (line2) line2.className = 'step-line step-line-active';
    if (line3) line3.className = 'step-line';
    if (badge) { badge.className = 'status-pill status-out-for-delivery'; badge.textContent = '● Out for Delivery'; }
    if (etaText) etaText.textContent = 'Arriving Today by 8:00 PM';
  } else if (normStatus.includes('pack') || normStatus.includes('dispatch')) {
    if (stepPlaced) stepPlaced.className = 'step-item step-completed';
    if (stepPacked) stepPacked.className = 'step-item step-active';
    if (stepTransit) stepTransit.className = 'step-item';
    if (stepDelivered) stepDelivered.className = 'step-item';
    if (line1) line1.className = 'step-line step-line-active';
    if (line2) line2.className = 'step-line';
    if (line3) line3.className = 'step-line';
    if (badge) { badge.className = 'status-pill status-packed'; badge.textContent = '● Packed & Dispatched'; }
    if (etaText) etaText.textContent = 'Expected in 1-2 Days';
  } else {
    // Confirmed or Pending
    if (stepPlaced) stepPlaced.className = 'step-item step-active';
    if (stepPacked) stepPacked.className = 'step-item';
    if (stepTransit) stepTransit.className = 'step-item';
    if (stepDelivered) stepDelivered.className = 'step-item';
    if (line1) line1.className = 'step-line';
    if (line2) line2.className = 'step-line';
    if (line3) line3.className = 'step-line';
    if (badge) {
      if (normStatus.includes('pend')) {
        badge.className = 'status-pill status-pending';
        badge.textContent = '● Pending Confirmation';
        if (etaText) etaText.textContent = 'Awaiting Store Confirmation';
      } else {
        badge.className = 'status-pill status-confirmed';
        badge.textContent = '● Order Confirmed';
        if (etaText) etaText.textContent = 'Estimated Delivery in 2-3 Days';
      }
    }
  }

  card.style.display = 'block';
  if (notFound) notFound.style.display = 'none';
}

function openOrderTrackingModal(targetCode = '') {
  openModal('trackingModal');
  const input = document.getElementById('trackingInput');
  const recentWrap = document.getElementById('trackingRecentOrdersWrap');
  const recentList = document.getElementById('recentOrdersList');
  const orders = getSavedOrders();

  if (recentWrap && recentList) {
    if (orders.length > 0) {
      recentWrap.style.display = 'flex';
      recentList.innerHTML = orders.slice(0, 4).map((o) => `
        <button type="button" class="recent-order-pill" onclick="quickTrackOrder('${o.orderCode}')">
          <span>📦 #${o.orderCode}</span>
          <strong>₹${Number(o.total || o.totalAmount || 0).toLocaleString('en-IN')}</strong>
        </button>
      `).join('');
    } else {
      recentWrap.style.display = 'none';
    }
  }

  const codeToTrack = targetCode || (orders.length ? orders[0].orderCode : 'RM-DEMO99');
  if (input) input.value = codeToTrack;
  quickTrackOrder(codeToTrack);
}

function quickTrackOrder(code) {
  const input = document.getElementById('trackingInput');
  if (input) input.value = code === 'demo' ? 'RM-DEMO99' : code;
  handleTrackSearch();
}

async function handleTrackSearch() {
  const input = document.getElementById('trackingInput');
  const card = document.getElementById('trackingResultCard');
  const notFound = document.getElementById('trackingNotFound');
  const rawQuery = String(input?.value || '').trim();

  if (!rawQuery) {
    alert('Please enter an Order ID or Mobile Number to track.');
    return;
  }

  const cleanQuery = rawQuery.toUpperCase().replace(/^#/, '');

  if (cleanQuery === 'DEMO' || cleanQuery === 'RM-DEMO99' || cleanQuery === 'DEMO99') {
    renderTrackingDetails(getDemoOrder());
    return;
  }

  const orders = getSavedOrders();
  const matchedLocal = orders.find((o) => {
    const code = (o.orderCode || '').toUpperCase().replace(/^#/, '');
    const phone = (o.customerPhone || o.address?.phone || '').replace(/\D/g, '');
    const queryDigits = cleanQuery.replace(/\D/g, '');
    return code === cleanQuery || (queryDigits && queryDigits.length >= 10 && phone.includes(queryDigits));
  });

  try {
    const apiUrl = typeof getApiEndpoint === 'function' ? getApiEndpoint() : '/backend/api.php';
    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'track_order', order_code: cleanQuery })
    });
    const data = await res.json();
    if (data && data.status === 'success' && data.order) {
      try {
        const localOrders = getSavedOrders();
        const existingIdx = localOrders.findIndex(o => (o.orderCode || o.order_code || '').toUpperCase().replace(/^#/, '') === cleanQuery);
        if (existingIdx !== -1) {
          localOrders[existingIdx].status = data.order.status;
          localOrders[existingIdx].tracking_notes = data.order.tracking_notes;
          localOrders[existingIdx].origin_hub_code = data.order.origin_hub_code;
          localStorage.setItem('pikuOrders', JSON.stringify(localOrders));
        }
      } catch (_) {}

      renderTrackingDetails(data.order);
      return;
    }
  } catch (_) {}

  if (matchedLocal) {
    renderTrackingDetails(matchedLocal);
    return;
  }

  if (card) card.style.display = 'none';
  if (notFound) {
    const msg = document.getElementById('trackingNotFoundMsg');
    if (msg) msg.textContent = `We couldn't find an order matching "${rawQuery}". Please check your Order ID or try the demo order.`;
    notFound.style.display = 'block';
  }
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.add('show');
  modal.setAttribute('aria-hidden', 'false');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('show');
  modal.setAttribute('aria-hidden', 'true');
}

function updateCartItem(name, action) {
  const item = cart.find((cartItem) => cartItem.name === name);
  if (!item) return;
  if (action === 'increase') item.quantity += 1;
  if (action === 'decrease') item.quantity -= 1;
  if (action === 'remove' || item.quantity <= 0) {
    const itemIndex = cart.indexOf(item);
    cart.splice(itemIndex, 1);
  }
  localStorage.setItem('pikuCart', JSON.stringify(cart));
  renderCart();
}

function openProductModal(card) {
  const content = document.getElementById('productDetailContent');
  if (!content || !card) return;
  const name = card.dataset.product;
  const price = Number(card.dataset.price || 0);
  const image = card.dataset.image;
  const category = card.dataset.category || 'Ranchi Mart edit';
  content.innerHTML = `
    <img class="detail-image" src="${image}" alt="${name}">
    <div class="detail-copy"><p class="eyebrow">${category}</p><h2>${name}</h2><p class="detail-description">A considered everyday piece with an easy silhouette, dependable quality, and enough character to keep it in rotation.</p><strong class="detail-price">₹${price.toLocaleString('en-IN')}</strong><button type="button" class="primary-button detail-add-button" data-product="${name}" data-price="${price}">Shop this item <span>↗</span></button></div>
  `;
  openModal('productModal');
}

function filterProductsByGender(gender) {
  const selectedGender = String(gender || 'all').toLowerCase();
  let visibleCount = 0;
  document.querySelectorAll('.product-card').forEach((card) => {
    const visible = selectedGender === 'all' || card.dataset.gender === selectedGender;
    card.hidden = !visible;
    if (visible) visibleCount += 1;
  });
  document.querySelectorAll('.gender-tab').forEach((tab) => tab.classList.toggle('is-selected', tab.dataset.gender === selectedGender));
  const emptyProducts = document.getElementById('emptyProducts');
  if (emptyProducts) emptyProducts.hidden = visibleCount !== 0;
  scrollToProducts();
}

function setActiveState(element, state = true) {
  if (!element) return;
  element.classList.toggle('is-active', state);
}

function activateInteractiveElement(element) {
  if (!element) return;

  document.querySelectorAll('.is-active').forEach((item) => {
    if (item !== element) item.classList.remove('is-active');
  });

  setActiveState(element, true);
}

function switchAuthMode(mode = 'login') {
  const authForm = document.getElementById('authForm');
  const title = document.getElementById('authModalTitle');
  const eyebrow = document.getElementById('authModalEyebrow');
  const submitButton = document.getElementById('authSubmitBtn');
  const switchText = document.getElementById('authSwitchText');
  const switchBtn = document.getElementById('authSwitchBtn');
  const tabLogin = document.getElementById('modalTabLogin');
  const tabSignup = document.getElementById('modalTabSignup');
  const demoBox = document.getElementById('demoLoginBox');
  const signupFields = document.querySelectorAll('.auth-field-signup');
  const nameInput = document.getElementById('signupName');

  const isSignup = mode === 'signup';
  if (authForm) {
    authForm.dataset.mode = isSignup ? 'signup' : 'login';
    authForm.classList.toggle('mode-signup', isSignup);
  }
  if (title) title.textContent = isSignup ? 'Create a new account' : 'Sign in to shop';
  if (eyebrow) eyebrow.textContent = isSignup ? 'JOIN RANCHI MART' : 'WELCOME BACK';
  if (submitButton) submitButton.innerHTML = isSignup ? 'Create account <span>→</span>' : 'Sign in <span>→</span>';
  if (switchText) switchText.textContent = isSignup ? 'Already have an account?' : "Don't have an account?";
  if (switchBtn) switchBtn.textContent = isSignup ? 'Sign in instead' : 'Create a new account';
  if (tabLogin) tabLogin.classList.toggle('is-active', !isSignup);
  if (tabSignup) tabSignup.classList.toggle('is-active', isSignup);
  if (demoBox) demoBox.style.display = isSignup ? 'none' : 'grid';

  signupFields.forEach((field) => {
    field.style.display = isSignup ? 'flex' : 'none';
  });

  if (nameInput) {
    if (isSignup) {
      nameInput.setAttribute('required', 'required');
    } else {
      nameInput.removeAttribute('required');
    }
  }
}

function toggleAuthMode() {
  const authForm = document.getElementById('authForm');
  const currentMode = authForm?.dataset.mode || 'login';
  switchAuthMode(currentMode === 'signup' ? 'login' : 'signup');
}

function openAuthModal(mode = 'login') {
  switchAuthMode(mode);
  openModal('authModal');
}

function closeAuthModal() {
  const modal = document.getElementById('authModal');
  if (modal) {
    closeModal('authModal');
  }
}

function isUserLoggedIn() {
  return document.body.dataset.loggedIn === 'true' || localStorage.getItem('pikuLoggedIn') === 'true';
}

function updateAuthButton() {
  const authButton = document.getElementById('authButton');
  if (!authButton) return;
  const label = authButton.querySelector('.auth-button-label');
  const loggedIn = isUserLoggedIn();
  const savedUser = JSON.parse(localStorage.getItem('pikuUser') || 'null');
  const firstName = savedUser?.name ? savedUser.name.split(' ')[0] : '';
  if (label) {
    label.textContent = loggedIn ? (firstName ? `Logout (${firstName})` : 'Logout') : 'Sign in';
  }
  authButton.setAttribute('aria-label', loggedIn ? 'Log out' : 'Sign in');
  authButton.dataset.loggedIn = loggedIn ? 'true' : 'false';
}

function logoutUser() {
  localStorage.removeItem('pikuLoggedIn');
  localStorage.removeItem('pikuUser');
  document.body.dataset.loggedIn = 'false';
  updateAuthButton();
  closeAuthModal();
  showCartMessage('You are logged out.');
}

function handleAuthButtonClick() {
  if (isUserLoggedIn()) {
    logoutUser();
    return;
  }
  openAuthModal('login');
}

function scrollToProducts() {
  const products = document.getElementById('products') || document.querySelector('.shop-section');
  if (products) products.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function searchProducts() {
  const input = document.getElementById('searchInput') || document.querySelector('.search-input');
  const query = String(input?.value || '').trim().toLowerCase();
  const productCards = document.querySelectorAll('.product-card');

  if (productCards.length) {
    productCards.forEach((card) => {
      const text = card.textContent.toLowerCase();
      card.style.display = !query || text.includes(query) ? '' : 'none';
    });
    scrollToProducts();
    return;
  }

  const categoryButtons = document.querySelectorAll('.see-more-btn');
  const matchingButton = [...categoryButtons].find((button) =>
    button.dataset.category.toLowerCase().includes(query)
  );
  if (matchingButton) matchingButton.click();
}

function filterCategory(category) {
  const normalizedCategory = String(category || '').toLowerCase();
  document.querySelectorAll('.product-card').forEach((card) => {
    card.style.display = card.dataset.category.toLowerCase() === normalizedCategory ? '' : 'none';
  });
  scrollToProducts();
}

function showAllProducts() {
  document.querySelectorAll('.product-card').forEach((card) => {
    card.style.display = '';
  });
  scrollToProducts();
}

function toggleWishlist(button) {
  if (!button) return;
  button.classList.toggle('is-active');
  button.textContent = button.classList.contains('is-active') ? '♥' : '♡';
  showCartMessage(button.classList.contains('is-active') ? 'Added to wishlist.' : 'Removed from wishlist.');
}

function getApiEndpoint() {
  const loc = window.location;
  if (loc.pathname) {
    const segments = loc.pathname.split('/').filter(Boolean);
    const idx = segments.findIndex(s => s === 'frontend' || s === 'backend');
    if (idx !== -1) {
      const base = segments.slice(0, idx).join('/');
      return (base ? '/' + base : '') + '/backend/api.php';
    }
  }
  return '/backend/api.php';
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function escapeJs(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'")
    .replace(/"/g, '\\"');
}

function renderProductGridFromBackend(products) {
  const grid = document.getElementById('productGrid');
  if (!grid || !Array.isArray(products) || products.length === 0) return;

  grid.innerHTML = products.map((p) => {
    const cat = p.category_name || 'Curated Edit';
    const name = p.name || 'Mart Item';
    const price = Number(p.price || 0);
    let img = p.image || '/frontend/photos/box1__image.png';
    if (img.startsWith('photos/')) {
      img = '/frontend/' + img;
    }
    const desc = p.description || 'Thoughtful everyday essential curated for comfort and modern style.';

    // Determine suitable gender/collection tag
    const textLower = (name + ' ' + cat + ' ' + desc).toLowerCase();
    let gender = 'unisex';
    if (textLower.includes('women') || textLower.includes('ladies') || textLower.includes('girl') || textLower.includes('serum') || textLower.includes('lip') || textLower.includes('scrunchie') || textLower.includes('skirt') || textLower.includes('tote') || textLower.includes('blazer')) {
      gender = 'women';
    } else if (textLower.includes('men') || textLower.includes('boy') || textLower.includes('shirt') || textLower.includes('kurta') || textLower.includes('jogger') || textLower.includes('denim')) {
      gender = 'men';
    } else if (textLower.includes('kid') || textLower.includes('child') || textLower.includes('toy') || textLower.includes('pack')) {
      gender = 'kids';
    }

    const genderLabel = gender.charAt(0).toUpperCase() + gender.slice(1);

    return `
      <article class="product-card" data-product="${escapeHtml(name)}" data-price="${price}" data-category="${escapeHtml(cat)}" data-gender="${gender}" data-image="${escapeHtml(img)}" data-description="${escapeHtml(desc)}">
        <button type="button" class="wishlist" aria-label="Add ${escapeHtml(name)} to wishlist" onclick="event.stopPropagation(); toggleWishlist(this);">♡</button>
        <div class="product-image">
          <img src="${escapeHtml(img)}" alt="${escapeHtml(name)}" onerror="this.src='/frontend/photos/box1__image.png'">
        </div>
        <p class="product-category">${escapeHtml(cat)} / ${genderLabel}</p>
        <h3>${escapeHtml(name)}</h3>
        <div class="product-footer">
          <strong>₹${price.toLocaleString('en-IN')}</strong>
          <span role="button" tabindex="0" onclick="event.stopPropagation(); addToCart('${escapeJs(name)}', ${price});">+ Add</span>
        </div>
      </article>
    `;
  }).join('');

  // Re-attach card click for product detail modal
  grid.querySelectorAll('.product-card').forEach((card) => {
    card.addEventListener('click', (event) => {
      if (event.target.closest('button') || event.target.closest('span[role="button"]')) return;
      openProductModal(card);
    });
  });
}

const DEFAULT_FALLBACK_PRODUCTS = [
  { id: 8, name: 'Creative Notebook Bundle', category_name: 'Books & Stationary', price: 699, stock: 28, image: 'photos/box10_images.png', description: 'A mix of study essentials for the work-from-home routine.' },
  { id: 7, name: 'Cookware Starter Pack', category_name: 'Home & Kitchen', price: 1999, stock: 16, image: 'photos/box9_images.png', description: 'Essential kitchen tools for everyday cooking.' },
  { id: 6, name: 'Silk Hair Care Set', category_name: 'Hair Accessories', price: 799, stock: 22, image: 'photos/box6_images.png', description: 'Complete hair nourishment for healthy shine.' },
  { id: 5, name: 'Compact Study Desk', category_name: 'Home & Kitchen', price: 2499, stock: 12, image: 'photos/box5_image.png', description: 'Minimal desk designed for modern home workspaces.' },
  { id: 4, name: 'Minimal Relaxed Fit Tee', category_name: 'Fashion', price: 899, stock: 45, image: 'photos/box8_image.png', description: 'Soft cotton comfort with a clean modern fit.' },
  { id: 3, name: 'Smart Wireless Speaker', category_name: 'Electronics', price: 1499, stock: 19, image: 'photos/box4_image.png', description: 'Compact sound with rich bass and Bluetooth pairing.' },
  { id: 2, name: 'Daily Radiance Skincare Duo', category_name: 'Beauty', price: 1299, stock: 35, image: 'photos/box1__image.png', description: 'Skin-first beauty essentials for everyday care.' },
  { id: 1, name: 'Wellness Essentials Combo', category_name: 'Health', price: 999, stock: 40, image: 'photos/box7_image.png', description: 'Daily health support for immunity and energy.' }
];

async function loadCatalogFromBackend() {
  let baseProducts = [];

  try {
    const apiUrl = getApiEndpoint();
    const res = await fetch(apiUrl + '?action=get_products');
    if (res.ok) {
      const data = await res.json();
      if (data.status === 'success' && Array.isArray(data.products) && data.products.length > 0) {
        baseProducts = data.products;
      }
    }
  } catch (_) {}

  if (baseProducts.length === 0) {
    baseProducts = DEFAULT_FALLBACK_PRODUCTS;
  }

  // Merge custom products and filter deleted products (works on Go Live & PHP)
  let custom = [];
  let deleted = [];
  try {
    custom = JSON.parse(localStorage.getItem('rm_custom_products') || '[]');
    deleted = JSON.parse(localStorage.getItem('rm_deleted_products') || '[]');
  } catch (_) {}

  const merged = [...custom, ...baseProducts];
  const finalProducts = merged.filter(p => !deleted.includes(p.id) && !deleted.includes(p.name));

  if (finalProducts.length > 0) {
    renderProductGridFromBackend(finalProducts);

    finalProducts.forEach((p) => {
      const cat = p.category_name || 'BeautyPicks';
      if (!categoryProducts[cat]) categoryProducts[cat] = [];
      const exists = categoryProducts[cat].some(cp => cp.name.toLowerCase() === p.name.toLowerCase());
      if (!exists) {
        categoryProducts[cat].unshift({
          name: p.name,
          price: Number(p.price),
          image: p.image || '',
          description: p.description || '',
          stock: Number(p.stock || 0)
        });
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateAuthButton();
  loadCatalogFromBackend();

  if (document.body.dataset.checkoutPage === 'true') {
    initializeCheckoutPage();
    return;
  }

  renderCart();

  document.querySelectorAll('.product-card').forEach((card) => {
    card.addEventListener('click', (event) => {
      if (event.target.closest('button')) return;
      openProductModal(card);
    });
  });

  document.querySelectorAll('.gender-tab').forEach((tab) => {
    tab.addEventListener('click', () => filterProductsByGender(tab.dataset.gender));
  });

  document.querySelectorAll('.buy-btn').forEach((button) => {
    button.addEventListener('click', () => {
      activateInteractiveElement(button);
      handleBuyButtonClick(button);
    });
  });

  document.querySelectorAll('.wishlist, .icon-btn').forEach((button) => {
    button.addEventListener('click', () => toggleWishlist(button));
  });

  document.querySelectorAll('.product-footer span').forEach((addAction) => {
    addAction.setAttribute('role', 'button');
    addAction.setAttribute('tabindex', '0');
    const addProduct = () => {
      const card = addAction.closest('.product-card');
      if (card) addToCart(card.dataset.product, Number(card.dataset.price || 0));
    };
    addAction.addEventListener('click', (event) => {
      event.stopPropagation();
      addProduct();
    });
    addAction.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      addProduct();
    });
  });

  document.addEventListener('click', (event) => {
    const cartAction = event.target.closest('[data-cart-action]');
    if (cartAction) {
      updateCartItem(cartAction.dataset.productName, cartAction.dataset.cartAction);
      return;
    }

    const detailAddButton = event.target.closest('.detail-add-button');
    if (detailAddButton) {
      addToCart(detailAddButton.dataset.product, Number(detailAddButton.dataset.price || 0));
      closeModal('productModal');
    }
  });

  document.querySelectorAll('.see-more-btn').forEach((button) => {
    button.addEventListener('click', () => {
      activateInteractiveElement(button);
      const category = button.dataset.category || 'BeautyPicks';
      openBeautyProductsModal(category);
    });
  });

  const navCart = document.getElementById('navCart');
  if (navCart) {
    navCart.addEventListener('click', () => {
      activateInteractiveElement(navCart);
      if (!cart.length) {
        showCartMessage('Your cart is empty.');
        return;
      }
      openCart();
    });
  }

  const navLogo = document.querySelector('.nav-logo');
  if (navLogo) {
    navLogo.addEventListener('click', () => {
      activateInteractiveElement(navLogo);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  const navSearch = document.querySelector('.nav-search');
  const searchButton = document.querySelector('.search-icon');
  if (navSearch) {
    navSearch.addEventListener('click', () => {
      activateInteractiveElement(searchButton || navSearch);
      const input = navSearch.querySelector('.search-input');
      if (input) input.focus();
    });
  }

  const searchTrigger = document.querySelector('.search-icon');
  if (searchTrigger) {
    searchTrigger.addEventListener('click', (event) => {
      event.stopPropagation();
      activateInteractiveElement(searchTrigger);
      const input = document.querySelector('.search-input');
      if (input) {
        input.focus();
      }
      searchProducts();
    });
  }

  document.querySelectorAll('.login-btn').forEach((button) => {
    button.addEventListener('click', () => {
      if (document.getElementById('authModal')) openAuthModal('login');
      else window.location.href = 'login.php';
    });
  });

  document.querySelectorAll('.nav-signin, .nav-return, .nav address, .panel, .panel-option, .panel-options p, .auth-action').forEach((item) => {
    item.addEventListener('click', () => activateInteractiveElement(item));
  });

  document.querySelectorAll('.auth-action').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      const mode = link.textContent.toLowerCase().includes('sign') ? 'signup' : 'login';
      openAuthModal(mode);
    });
  });

  const authCloseButton = document.querySelector('.auth-close');
  if (authCloseButton) {
    authCloseButton.addEventListener('click', closeAuthModal);
  }

  document.querySelectorAll('[data-close]').forEach((button) => {
    button.addEventListener('click', () => closeModal(button.dataset.close));
  });

  const authSwitchButton = document.querySelector('.auth-switch');
  if (authSwitchButton) {
    authSwitchButton.addEventListener('click', () => {
      const nextMode = authSwitchButton.dataset.mode === 'signup' ? 'signup' : 'login';
      openAuthModal(nextMode);
    });
  }

  const authForm = document.getElementById('authForm');
  if (authForm) {
    authForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      const mode = authForm.dataset.mode || 'login';
      const formData = new FormData(authForm);
      const email = String(formData.get('email') || '').trim();
      const password = String(formData.get('password') || '').trim();

      if (!email || !password) {
        alert('Email and password are required.');
        return;
      }

      if (mode === 'signup') {
        const name = String(formData.get('name') || '').trim();
        const phone = String(formData.get('phone') || '').trim();
        const address = String(formData.get('address') || '').trim();

        if (!name) {
          alert('Please enter your full name.');
          return;
        }

        const newUser = { name, email, phone, address, password };

        const users = JSON.parse(localStorage.getItem('pikuUsers') || '[]');
        const exists = users.some((u) => u.email.toLowerCase() === email.toLowerCase());
        if (exists) {
          alert('An account with this email already exists. Please sign in.');
          switchAuthMode('login');
          return;
        }

        users.push(newUser);
        localStorage.setItem('pikuUsers', JSON.stringify(users));
        localStorage.setItem('pikuUser', JSON.stringify(newUser));
        localStorage.setItem('pikuLoggedIn', 'true');

        try {
          await fetch('/backend/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'register', name, email, password, phone, address })
          });
        } catch (_) {}

        alert(`Welcome to Ranchi Mart, ${name}! Your account has been created.`);
        closeAuthModal();
        updateAuthButton();
        window.location.reload();
        return;
      }

      const savedUser = JSON.parse(localStorage.getItem('pikuUser') || 'null');
      const users = JSON.parse(localStorage.getItem('pikuUsers') || '[]');
      const registeredUser = users.find((u) => u.email.toLowerCase() === email.toLowerCase() && u.password === password);
      const isDemo = email === 'admin@pikumart.com' && password === '123456';
      const isSaved = savedUser && savedUser.email && savedUser.email.toLowerCase() === email.toLowerCase() && savedUser.password === password;

      try {
        const apiEndpoint = typeof getApiEndpoint === 'function' ? getApiEndpoint() : '/backend/api.php';
        const res = await fetch(apiEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'login', email, password })
        });
        const data = await res.json();
        if (data && data.status === 'success') {
          const user = data.user || { name: 'User', email };
          localStorage.setItem('pikuUser', JSON.stringify(user));
          localStorage.setItem('pikuLoggedIn', 'true');

          if (data.is_admin) {
            localStorage.setItem('adminLoggedIn', 'true');
            alert(`Welcome Admin ${user.name}! Redirecting to Admin Dashboard...`);
            if (window.location.port === '8000') {
              const targetBase = window.location.pathname.includes('/frontend/') ? '/frontend/admin.php' : 'admin.php';
              window.location.href = targetBase + '?quick_login=' + encodeURIComponent(data.user?.username || email) + '&pass=' + encodeURIComponent(password);
            } else {
              window.location.href = window.location.pathname.includes('/frontend/') ? 'admin.html' : 'frontend/admin.html';
            }
            return;
          }

          alert(`Login successful! Welcome back, ${user.name}!`);
          closeAuthModal();
          updateAuthButton();
          window.location.reload();
          return;
        }
      } catch (_) {}

      // Check client-side admin fallback (works on Go Live port 5500)
      const normEmail = email.toLowerCase();
      const isAdminUser = (normEmail === 'admin' || normEmail === 'rajesh' || normEmail === 'pooja' || normEmail === 'amitabh' || normEmail === 'sunita' || normEmail === 'admin@ranchimart.com') && (password === 'admin123');
      if (isAdminUser) {
        localStorage.setItem('adminLoggedIn', 'true');
        localStorage.setItem('pikuLoggedIn', 'true');
        localStorage.setItem('pikuUser', JSON.stringify({ name: email, email, is_admin: true }));
        alert(`Welcome Admin ${email}! Opening Admin Dashboard...`);
        if (window.location.port === '8000') {
          const targetBase = window.location.pathname.includes('/frontend/') ? '/frontend/admin.php' : 'admin.php';
          window.location.href = targetBase + '?quick_login=' + encodeURIComponent(email) + '&pass=' + encodeURIComponent(password);
        } else {
          window.location.href = window.location.pathname.includes('/frontend/') ? 'admin.html' : 'frontend/admin.html';
        }
        return;
      }

      if (isDemo || isSaved || registeredUser) {
        const user = registeredUser || savedUser || { name: 'Demo User', email };
        localStorage.setItem('pikuUser', JSON.stringify(user));
        localStorage.setItem('pikuLoggedIn', 'true');
        alert(`Login successful! Welcome back, ${user.name}!`);
        closeAuthModal();
        updateAuthButton();
        window.location.reload();
        return;
      }

      alert('Invalid username/email or password. Please check credentials or use the 1-click demo buttons.');
    });
  }

  // Quick fill helper for storefront modal
  window.quickFillStoreAuth = function(u, p) {
    const emailInput = document.getElementById('authEmail');
    const passInput = document.getElementById('authPassword');
    if (emailInput) emailInput.value = u;
    if (passInput) passInput.value = p;
  };

  // Smart router for top navbar Admin Panel link
  window.handleAdminPortalNav = function(event) {
    if (event) event.preventDefault();
    if (window.location.port === '8000') {
      window.location.href = window.location.pathname.includes('/frontend/') ? 'admin.php' : 'frontend/admin.php';
    } else {
      window.location.href = window.location.pathname.includes('/frontend/') ? 'admin.html' : 'frontend/admin.html';
    }
  };

  const authModal = document.getElementById('authModal');
  if (authModal) {
    authModal.addEventListener('click', (event) => {
      if (event.target === authModal) closeAuthModal();
    });
  }

  const trackingModal = document.getElementById('trackingModal');
  if (trackingModal) {
    trackingModal.addEventListener('click', (event) => {
      if (event.target === trackingModal) closeModal('trackingModal');
    });
  }

  document.querySelectorAll('.panel-options p').forEach((panelItem) => {
    panelItem.classList.add('panel-option');
    panelItem.addEventListener('click', () => {
      activateInteractiveElement(panelItem);
      const label = panelItem.textContent.trim();
      const target = document.querySelector('.shop-section');
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      if (label.toLowerCase().includes('deal')) {
        showCartMessage('Deals section opened.');
      }
    });
  });

  const closeButton = document.querySelector('.cart-close');
  if (closeButton) {
    closeButton.addEventListener('click', closeCart);
  }

  const checkoutButton = document.querySelector('.checkout-btn');
  if (checkoutButton) {
    checkoutButton.addEventListener('click', checkout);
  }

  const paymentForm = document.getElementById('paymentForm');
  if (paymentForm) {
    paymentForm.addEventListener('change', (event) => {
      if (event.target.name !== 'method') return;
      const isCard = event.target.value === 'card';
      const cardContainer = document.getElementById('modalCardFields');
      const codNotice = document.getElementById('modalCodNotice');
      if (cardContainer) cardContainer.style.display = isCard ? 'block' : 'none';
      if (codNotice) codNotice.style.display = isCard ? 'none' : 'block';
      const cardInputs = paymentForm.querySelectorAll('[name="cardName"], [name="cardNumber"], [name="expiry"], [name="cvv"]');
      cardInputs.forEach((field) => { field.required = isCard; });
    });

    paymentForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(paymentForm);
      const delName = String(formData.get('deliveryName') || '').trim();
      const delPhone = String(formData.get('deliveryPhone') || '').trim();
      const delStreet = String(formData.get('deliveryStreet') || '').trim();
      const delArea = String(formData.get('deliveryArea') || '').trim();
      const delCity = String(formData.get('deliveryCity') || 'Ranchi').trim();
      const delPincode = String(formData.get('deliveryPincode') || '').trim();
      const payMethod = String(formData.get('method') || 'card');

      if (!delName || !delPhone || !delStreet) {
        alert('Please fill in your Delivery Address completely.');
        const fields = document.getElementById('modalAddressFields');
        if (fields) fields.style.display = 'grid';
        return;
      }

      const addr = {
        name: delName,
        phone: delPhone,
        street: delStreet,
        area: delArea,
        city: delCity,
        pincode: delPincode
      };
      saveDeliveryAddress(addr);

      const orderCode = 'RM-' + Math.floor(100000 + Math.random() * 900000);
      const totalAmt = getCartTotal();
      const newOrder = {
        orderCode: orderCode,
        order_code: orderCode,
        displayDate: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
        status: 'Confirmed',
        customerName: delName,
        customerPhone: delPhone,
        deliveryAddress: `${delStreet}, ${delArea}, ${delCity} - ${delPincode}`,
        paymentMethod: payMethod,
        totalAmount: totalAmt,
        items: [...cart]
      };
      saveOrderToHistory(newOrder);

      fetch('/backend/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'create_order',
          order_code: orderCode,
          customer_name: delName,
          customer_phone: delPhone,
          delivery_address: newOrder.deliveryAddress,
          total_amount: totalAmt,
          payment_method: payMethod,
          items: cart
        })
      }).catch(() => {});

      cart.length = 0;
      localStorage.setItem('pikuCart', JSON.stringify(cart));
      renderCart();
      closeModal('paymentModal');
      showCartMessage(`Order #${orderCode} placed! Tracking is now live under "Track Order".`);
      setTimeout(() => {
        openOrderTrackingModal(orderCode);
      }, 1200);
    });
  }

  const beautyCloseButton = document.querySelector('.product-modal-close');
  if (beautyCloseButton) {
    beautyCloseButton.addEventListener('click', closeBeautyProductsModal);
  }

  const beautyModal = document.getElementById('beautyProductsModal');
  if (beautyModal) {
    beautyModal.addEventListener('click', (event) => {
      if (event.target === beautyModal) closeBeautyProductsModal();
    });
  }

  document.addEventListener('click', (event) => {
    const buyButton = event.target.closest('.beauty-buy-btn');
    if (buyButton) {
      activateInteractiveElement(buyButton);
      addToCart(buyButton.dataset.name, Number(buyButton.dataset.price || 0));
      closeBeautyProductsModal();
    }
  });

  const cartModal = document.getElementById('cartModal');
  if (cartModal) {
    cartModal.addEventListener('click', (event) => {
      if (event.target === cartModal) closeCart();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeCart();
    closeModal('productModal');
    closeModal('paymentModal');
    closeModal('trackingModal');
    closeBeautyProductsModal();
    closeAuthModal();
  });

  // Handle URL parameter ?track=RM-xxxxxx to auto-open customer track order
  const urlParams = new URLSearchParams(window.location.search);
  const trackParam = urlParams.get('track') || urlParams.get('order_code');
  if (trackParam) {
    setTimeout(() => {
      openOrderTrackingModal(trackParam);
    }, 450);
  }
});

window.addToCart = addToCart;
window.handleBuyButtonClick = handleBuyButtonClick;
window.openCart = openCart;
window.closeCart = closeCart;
window.checkout = checkout;
window.searchProducts = searchProducts;
window.filterCategory = filterCategory;
window.scrollToProducts = scrollToProducts;
window.showAllProducts = showAllProducts;
window.toggleWishlist = toggleWishlist;
window.filterProductsByGender = filterProductsByGender;
window.toggleAddressForm = toggleAddressForm;
window.toggleCheckoutAddressForm = toggleCheckoutAddressForm;
window.populateDeliveryAddress = populateDeliveryAddress;
window.openOrderTrackingModal = openOrderTrackingModal;
window.quickTrackOrder = quickTrackOrder;
window.handleTrackSearch = handleTrackSearch;


