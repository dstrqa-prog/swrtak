const products = [
  { id: 'PRD-101', name: 'عطر ليلي فاخر', category: 'العطور', price: 185000, image: 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=60' },
  { id: 'PRD-102', name: 'ساعة كلاسيك', category: 'الساعات', price: 240000, image: 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=60' },
  { id: 'PRD-103', name: 'حقيبة نسائية جلد', category: 'الإكسسوارات', price: 210000, image: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=60' },
  { id: 'PRD-104', name: 'جاكيت كاجوال', category: 'الملابس', price: 175000, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=60' },
  { id: 'PRD-105', name: 'نظارة شمسية', category: 'الإكسسوارات', price: 98000, image: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=900&q=60' },
  { id: 'PRD-106', name: 'عطر صباحي منعش', category: 'العطور', price: 162000, image: 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=60' },
];

const state = { cart: [], activeCategory: 'الكل', query: '' };
const categories = ['الكل', ...new Set(products.map((p) => p.category))];

const productsGrid = document.getElementById('products-grid');
const categoriesEl = document.getElementById('categories');
const searchEl = document.getElementById('search');
const cartCountEl = document.getElementById('cart-count');
const cartBtn = document.getElementById('cart-btn');
const cartDrawer = document.getElementById('cart-drawer');
const cartItemsEl = document.getElementById('cart-items');
const cartTotalEl = document.getElementById('cart-total');
const paymentCodeEl = document.getElementById('payment-code');
const generateCodeBtn = document.getElementById('generate-code');

function generatePaymentCode() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let code = '';
  for (let i = 0; i < 10; i++) code += chars[Math.floor(Math.random() * chars.length)];
  return code;
}

function renderCategories() {
  categoriesEl.innerHTML = '';
  categories.forEach((cat) => {
    const chip = document.createElement('button');
    chip.className = `category-chip ${state.activeCategory === cat ? 'active' : ''}`;
    chip.textContent = cat;
    chip.onclick = () => {
      state.activeCategory = cat;
      renderCategories();
      renderProducts();
    };
    categoriesEl.appendChild(chip);
  });
}

function renderProducts() {
  const filtered = products.filter((p) => {
    const categoryMatch = state.activeCategory === 'الكل' || p.category === state.activeCategory;
    const q = state.query.trim().toLowerCase();
    const queryMatch = !q || p.name.toLowerCase().includes(q) || p.id.toLowerCase().includes(q);
    return categoryMatch && queryMatch;
  });

  productsGrid.innerHTML = '';
  if (!filtered.length) {
    productsGrid.innerHTML = '<p>لا توجد نتائج مطابقة حالياً.</p>';
    return;
  }

  filtered.forEach((p) => {
    const card = document.createElement('article');
    card.className = 'product-card';
    card.innerHTML = `
      <img src="${p.image}" alt="${p.name}">
      <h4>${p.name}</h4>
      <p class="meta">Product ID: ${p.id}</p>
      <p class="price">${p.price.toLocaleString()} ل.س</p>
      <button class="neon-btn">إضافة للسلة</button>
    `;
    card.querySelector('button').onclick = () => addToCart(p);
    productsGrid.appendChild(card);
  });
}

function addToCart(product) {
  const item = state.cart.find((i) => i.id === product.id);
  if (item) item.qty += 1;
  else state.cart.push({ ...product, qty: 1 });
  renderCart();
}

function renderCart() {
  const count = state.cart.reduce((sum, item) => sum + item.qty, 0);
  const total = state.cart.reduce((sum, item) => sum + item.qty * item.price, 0);
  cartCountEl.textContent = count;
  cartTotalEl.textContent = total.toLocaleString();

  cartItemsEl.innerHTML = '';
  if (!state.cart.length) {
    cartItemsEl.innerHTML = '<p class="cart-item">السلة فارغة.</p>';
    return;
  }

  state.cart.forEach((item) => {
    const row = document.createElement('div');
    row.className = 'cart-item';
    row.textContent = `${item.name} × ${item.qty}`;
    cartItemsEl.appendChild(row);
  });
}

searchEl.addEventListener('input', (e) => {
  state.query = e.target.value;
  renderProducts();
});

cartBtn.onclick = () => cartDrawer.classList.toggle('open');
generateCodeBtn.onclick = () => (paymentCodeEl.textContent = generatePaymentCode());

paymentCodeEl.textContent = generatePaymentCode();
renderCategories();
renderProducts();
renderCart();
