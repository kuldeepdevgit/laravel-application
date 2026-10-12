/* Northbay catalog. Edit PRODUCTS to change what the shop sells. */
const shot = (id) => `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=1000&q=80`;

const CATEGORIES = ["Electronics", "Home", "Fashion", "Kitchen", "Outdoors", "Grocery", "Beauty", "Stationery"];

const CATEGORY_LABELS = {
  Home: "Home goods",
  Stationery: "Stationery",
};

const SAMPLE_REVIEWS = [
  { title: "Daily driver now", body: "Comfortable for long sessions and the finish matches the photos.", stars: 5, date: "March 12, 2026" },
  { title: "Solid for the price", body: "Arrived on the estimated day and worked right out of the box.", stars: 4, date: "February 2, 2026" },
  { title: "Would buy again", body: "Packed with care. The description matched what showed up.", stars: 5, date: "January 18, 2026" },
];

const PRODUCTS = [
  {
    id: "nb-headphones",
    name: "Aero Quiet Headphones",
    category: "Electronics",
    price: 129,
    compare: 168,
    rating: 4.7,
    reviews: 2143,
    badge: "Bestseller",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1505740420928-5e560c06d30e"), shot("1484704849700-f032a568e944")],
    colors: ["Charcoal", "Sand", "Forest"],
    sizes: [],
    blurb: "Over-ear headphones with soft pads and a quiet-enough cabin for commutes and late work.",
    details: "A fold-flat hinge, a 30-hour battery, and a cable in the case when you would rather plug in.",
    specs: [["Driver", "40 mm"], ["Battery", "30 hours"], ["Weight", "252 g"], ["Connection", "Bluetooth 5.3"]],
  },
  {
    id: "nb-kettle",
    name: "Harbor Pour-Over Kettle",
    category: "Kitchen",
    price: 46,
    compare: 59,
    rating: 4.6,
    reviews: 890,
    badge: "Deal",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1517668808822-9ebb02f2a0e6"), shot("1495474472287-4d71bcdd2085")],
    colors: ["Matte black", "Cloud"],
    sizes: [],
    blurb: "A gooseneck kettle with a steady pour and a counter-friendly footprint.",
    details: "Holds 900 ml, marks the fill line inside, and sits flat so it does not drip down the side.",
    specs: [["Capacity", "900 ml"], ["Material", "Stainless steel"], ["Heat", "Stovetop"], ["Finish", "Matte"]],
  },
  {
    id: "nb-throw",
    name: "Dune Linen Throw",
    category: "Home",
    price: 64,
    compare: null,
    rating: 4.5,
    reviews: 412,
    badge: "New",
    stock: null,
    ship: "Ships in 2 days",
    images: [shot("1522771739844-6a9f6d5f14af")],
    colors: ["Oat", "Moss", "Ink"],
    sizes: [],
    blurb: "A washed linen throw that softens after the first weekend on the sofa.",
    details: "Loose weave, fringed edges, and a size that covers a two-seat sofa without pooling on the floor.",
    specs: [["Size", "130 × 180 cm"], ["Fabric", "Washed linen"], ["Care", "Cold wash"], ["Weight", "Light"]],
  },
  {
    id: "nb-runners",
    name: "Trailform Runners",
    category: "Outdoors",
    price: 98,
    compare: 128,
    rating: 4.8,
    reviews: 1560,
    badge: "Bestseller",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1542291026-7eec264c27ff"), shot("1460353581641-37baddab0fa2")],
    colors: ["Clay", "Night"],
    sizes: ["7", "8", "9", "10", "11"],
    blurb: "Road-to-trail shoes with a grippy outsole and a collar that does not bite.",
    details: "Daily mileage cushion, a reflective heel tab, and a lace garage so the bow stays put.",
    specs: [["Drop", "8 mm"], ["Use", "Road and light trail"], ["Upper", "Recycled mesh"], ["Closure", "Laces"]],
  },
  {
    id: "nb-bottle",
    name: "Ridge Steel Bottle",
    category: "Outdoors",
    price: 28,
    compare: null,
    rating: 4.4,
    reviews: 2301,
    badge: null,
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1602143407151-7111542de6e8")],
    colors: ["Slate", "Citrus", "Sea"],
    sizes: [],
    blurb: "A 750 ml bottle that keeps water cold through a long afternoon outside.",
    details: "Powder-coated steel, a leak-resistant cap, and a mouth wide enough for ice cubes.",
    specs: [["Capacity", "750 ml"], ["Cold", "24 hours"], ["Material", "Stainless steel"], ["Cap", "Screw, leak resistant"]],
  },
  {
    id: "nb-lamp",
    name: "Arc Desk Lamp",
    category: "Home",
    price: 54,
    compare: 70,
    rating: 4.6,
    reviews: 640,
    badge: "Deal",
    stock: null,
    ship: "Ships in 2 days",
    images: [shot("1507473885765-e6ed057f782c")],
    colors: ["Brass", "Black"],
    sizes: [],
    blurb: "A dimmable arm lamp that aims a warm pool of light at the page, not the whole room.",
    details: "Weighted base, a cloth cord, and three brightness steps from a single button.",
    specs: [["Light", "Warm LED"], ["Dimming", "3 steps"], ["Arm", "Adjustable"], ["Cord", "1.8 m"]],
  },
  {
    id: "nb-bag",
    name: "Weekender Leather Bag",
    category: "Fashion",
    price: 168,
    compare: null,
    rating: 4.7,
    reviews: 388,
    badge: null,
    stock: null,
    ship: "Ships in 2 days",
    images: [shot("1548036328-c9fa89d128fa"), shot("1590874103328-eac38a683ce7")],
    colors: ["Tan", "Black"],
    sizes: [],
    blurb: "A carry-on weekender in full-grain leather with a shoe pocket and a quiet lining.",
    details: "Fits under a narrow airline seat, with cotton handles that are wrapped for the shoulder.",
    specs: [["Volume", "32 L"], ["Material", "Full-grain leather"], ["Strap", "Detachable"], ["Pocket", "Shoes"]],
  },
  {
    id: "nb-speaker",
    name: "Pocket Pulse Speaker",
    category: "Electronics",
    price: 79,
    compare: 99,
    rating: 4.3,
    reviews: 1022,
    badge: "Deal",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1608043152269-423dbba4e7e1")],
    colors: ["Stone", "Red"],
    sizes: [],
    blurb: "A palm-size speaker with enough low end for a kitchen and a clip for a pack strap.",
    details: "Pairs quickly, shrugs off a splash, and charges from a USB-C cable you already own.",
    specs: [["Battery", "12 hours"], ["Resistance", "Splash"], ["Charge", "USB-C"], ["Link", "Bluetooth"]],
  },
  {
    id: "nb-tee",
    name: "Everyday Cotton Tee",
    category: "Fashion",
    price: 32,
    compare: null,
    rating: 4.5,
    reviews: 870,
    badge: "New",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1521572163474-6864f9cf17ab")],
    colors: ["White", "Navy", "Rust"],
    sizes: ["XS", "S", "M", "L", "XL"],
    blurb: "A midweight tee with a rib that keeps its shape after washing.",
    details: "Cut straight through the body, with a shoulder seam that sits where a shoulder seam should.",
    specs: [["Fabric", "Organic cotton"], ["Weight", "180 gsm"], ["Fit", "Straight"], ["Care", "Machine wash"]],
  },
  {
    id: "nb-skillet",
    name: "Hearth Cast Skillet",
    category: "Kitchen",
    price: 58,
    compare: 74,
    rating: 4.9,
    reviews: 1904,
    badge: "Bestseller",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1556909114-f6e7ad7d3136")],
    colors: ["Seasoned iron"],
    sizes: [],
    blurb: "A 10-inch skillet, pre-seasoned, with a handle you can actually grip.",
    details: "Moves from stove to oven, holds a sear, and hangs from the helper handle on the far side.",
    specs: [["Diameter", "10 in"], ["Material", "Cast iron"], ["Oven", "To 260°C"], ["Seasoning", "Ready to cook"]],
  },
  {
    id: "nb-mat",
    name: "Grounded Yoga Mat",
    category: "Outdoors",
    price: 36,
    compare: null,
    rating: 4.4,
    reviews: 733,
    badge: null,
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1601925260368-ae2f83cf8b7f")],
    colors: ["Sage", "Clay"],
    sizes: [],
    blurb: "A 5 mm mat with a tacky top that stays put when your hands get warm.",
    details: "Closed-cell cushion, alignment marks you can ignore, and a strap tucked in the end.",
    specs: [["Thickness", "5 mm"], ["Length", "180 cm"], ["Surface", "Tacky"], ["Carry", "Strap included"]],
  },
  {
    id: "nb-keyboard",
    name: "Keyline Mechanical Keyboard",
    category: "Electronics",
    price: 119,
    compare: 149,
    rating: 4.6,
    reviews: 501,
    badge: "Deal",
    stock: null,
    ship: "Ships in 2 days",
    images: [shot("1511467687858-23d96c32e4ae")],
    colors: ["Fog", "Carbon"],
    sizes: [],
    blurb: "A compact mechanical board with a soft typing sound and a cable you can swap.",
    details: "Hot-swap sockets, a gentle incline, and keycaps that do not shine after a month.",
    specs: [["Layout", "75%"], ["Switch", "Tactile"], ["Connection", "USB-C"], ["Keycaps", "PBT"]],
  },
  {
    id: "nb-candle",
    name: "Cedar Candle Trio",
    category: "Home",
    price: 29,
    compare: null,
    rating: 4.2,
    reviews: 266,
    badge: "New",
    stock: 0,
    ship: "Back soon",
    images: [shot("1572726729207-a78d6feb18d7")],
    colors: ["Cedar", "Fig", "Linen"],
    sizes: [],
    blurb: "Three small soy candles: cedar, fig leaf, and clean linen.",
    details: "Cotton wicks and glass you can wash and reuse. This set is currently sold out in the demo.",
    specs: [["Wax", "Soy blend"], ["Burn", "About 22 hours each"], ["Count", "3"], ["Vessel", "Reusable glass"]],
  },
  {
    id: "nb-shades",
    name: "Solstice Sunglasses",
    category: "Fashion",
    price: 68,
    compare: null,
    rating: 4.5,
    reviews: 944,
    badge: null,
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1511499767150-a48a237f0083")],
    colors: ["Tortoise", "Black"],
    sizes: [],
    blurb: "Lightweight acetate frames with polarized lenses and a case that survives a bag.",
    details: "Spring hinges, a nose bridge that does not pinch, and lenses that do not tint the world brown.",
    specs: [["Lens", "Polarized"], ["Frame", "Acetate"], ["UV", "UV400"], ["Case", "Hard shell"]],
  },
  {
    id: "nb-coffee",
    name: "Dawn Roast Coffee",
    category: "Grocery",
    price: 18,
    compare: 24,
    rating: 4.8,
    reviews: 3200,
    badge: "Bestseller",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1447933601403-0c6688de566e"), shot("1495474472287-4d71bcdd2085")],
    colors: [],
    sizes: [],
    blurb: "A one-kilo bag of medium roast, chocolate and orange peel, ground for a drip brewer.",
    details: "Roasted in small batches and sealed with a valve so the bag does not balloon in the pantry.",
    specs: [["Weight", "1 kg"], ["Roast", "Medium"], ["Grind", "Drip"], ["Notes", "Cocoa, orange peel"]],
  },
  {
    id: "nb-planter",
    name: "Clay Planter Pair",
    category: "Home",
    price: 34,
    compare: null,
    rating: 4.3,
    reviews: 190,
    badge: "New",
    stock: 4,
    ship: "Ships in 2 days",
    images: [shot("1485955900006-10f4d324d411")],
    colors: ["Sand"],
    sizes: [],
    blurb: "Two drainage-ready clay pots with saucers, sized for a windowsill herb and a trailing pothos.",
    details: "Unglazed outside, glazed inside, so water leaves through the hole and not the desk.",
    specs: [["Count", "2 pots, 2 saucers"], ["Widths", "12 cm and 16 cm"], ["Material", "Clay"], ["Drainage", "Yes"]],
  },
  {
    id: "nb-serum",
    name: "Calm Serum Set",
    category: "Beauty",
    price: 42,
    compare: 54,
    rating: 4.6,
    reviews: 610,
    badge: "Deal",
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1556228720-195a672e8a03")],
    colors: [],
    sizes: [],
    blurb: "A short routine: a light serum in the morning and a richer one at night.",
    details: "Fragrance-free, packaged in glass, with a dropper that does not dump half the bottle.",
    specs: [["Count", "2 × 30 ml"], ["Use", "Morning and night"], ["Scent", "Fragrance free"], ["Bottle", "Glass"]],
  },
  {
    id: "nb-journal",
    name: "Fieldcloth Journal",
    category: "Stationery",
    price: 16,
    compare: null,
    rating: 4.7,
    reviews: 280,
    badge: null,
    stock: null,
    ship: "Ships tomorrow",
    images: [shot("1512820790803-83ca734da794")],
    colors: ["Olive", "Navy"],
    sizes: [],
    blurb: "A cloth-covered notebook with paper that takes a fountain pen without blooming.",
    details: "192 pages, a ribbon marker, and a pocket in the back for a receipt or a ticket.",
    specs: [["Pages", "192"], ["Paper", "90 gsm"], ["Cover", "Cloth"], ["Lay-flat", "Sewn binding"]],
  },
];

const CART_KEY = "northbay-cart";
const PAGE_SIZE = 9;
let promoMessage = "";

function esc(value) {
  return String(value).replace(/[&<>"']/g, (ch) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  }[ch]));
}

function catLabel(category) {
  return CATEGORY_LABELS[category] || category;
}

function roundMoney(n) {
  return Math.round(Number(n) * 100) / 100;
}

const moneyFmt = new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" });

function money(n) {
  return moneyFmt.format(roundMoney(n));
}

function defaultState() {
  return {
    cart: [],
    promo: "",
    ship: "standard",
    dealEnd: Date.now() + 8 * 60 * 60 * 1000,
  };
}

function sanitize(raw) {
  const base = defaultState();
  const source = raw && typeof raw === "object" ? raw : {};
  const cart = Array.isArray(source.cart)
    ? source.cart
        .filter((line) => line && PRODUCTS.some((product) => product.id === line.id) && Number(line.qty) > 0)
        .map((line) => ({ id: line.id, qty: Math.min(99, Math.floor(Number(line.qty))) }))
    : [];
  let dealEnd = Number(source.dealEnd);
  if (!dealEnd || dealEnd < Date.now()) dealEnd = base.dealEnd;
  return {
    cart,
    promo: source.promo === "WELCOME10" ? "WELCOME10" : "",
    ship: source.ship === "express" ? "express" : "standard",
    dealEnd,
  };
}

function loadState() {
  try {
    if (location.protocol === "file:" && window.name.startsWith("nb1:")) {
      return sanitize(JSON.parse(decodeURIComponent(window.name.slice(4))));
    }
    if (location.protocol !== "file:") {
      const raw = localStorage.getItem(CART_KEY);
      if (raw) return sanitize(JSON.parse(raw));
    }
  } catch (error) {
    /* Keep the empty cart if storage is blocked. */
  }
  return defaultState();
}

let state = loadState();

function persist() {
  const json = JSON.stringify(state);
  if (location.protocol === "file:") {
    window.name = `nb1:${encodeURIComponent(json)}`;
    return;
  }
  try {
    localStorage.setItem(CART_KEY, json);
  } catch (error) {
    /* Ignore private-mode storage failures. */
  }
}

function productById(id) {
  return PRODUCTS.find((product) => product.id === id);
}

function maxQty(product) {
  if (!product || product.stock === 0) return 0;
  if (typeof product.stock === "number") return product.stock;
  return 12;
}

function stars(rating) {
  const rounded = Math.round(rating * 2) / 2;
  let html = "";
  for (let i = 1; i <= 5; i += 1) {
    if (rounded >= i) html += '<i class="bi bi-star-fill"></i>';
    else if (rounded >= i - 0.5) html += '<i class="bi bi-star-half"></i>';
    else html += '<i class="bi bi-star"></i>';
  }
  return html;
}

function availability(product) {
  if (product.stock === 0) return { text: "Sold out", cls: "out" };
  if (typeof product.stock === "number" && product.stock <= 5) {
    return { text: `Only ${product.stock} left`, cls: "low" };
  }
  return { text: product.ship, cls: "" };
}

function badge(product) {
  if (product.stock === 0) return '<span class="product-badge sold">Sold out</span>';
  if (!product.badge) return "";
  const kind = product.badge === "Deal" ? "deal" : product.badge === "New" ? "new" : "best";
  return `<span class="product-badge ${kind}">${esc(product.badge)}</span>`;
}

function cardHTML(product) {
  const avail = availability(product);
  const save = product.compare ? Math.round((1 - product.price / product.compare) * 100) : 0;
  const action = product.stock === 0
    ? '<button class="btn btn-accent w-100" type="button" disabled>Sold out</button>'
    : `<button class="btn btn-accent w-100" type="button" data-add="${esc(product.id)}">Add to cart</button>`;
  return `
    <article class="product-card">
      <a class="product-media" href="product.html?id=${esc(product.id)}">
        <img src="${esc(product.images[0])}" alt="${esc(product.name)}" onerror="this.style.visibility='hidden'">
        ${badge(product)}
      </a>
      <div class="product-body">
        <p class="product-cat">${esc(catLabel(product.category))}</p>
        <h3><a href="product.html?id=${esc(product.id)}">${esc(product.name)}</a></h3>
        <div class="stars" aria-label="Rated ${product.rating.toFixed(1)} out of 5">${stars(product.rating)}<span>${product.reviews.toLocaleString()} ratings</span></div>
        <div class="price-row">
          <span class="price">${money(product.price)}</span>
          ${product.compare ? `<s>${money(product.compare)}</s><span class="save">Save ${save}%</span>` : ""}
        </div>
        <p class="product-ship ${avail.cls}"><i class="bi bi-truck"></i> ${esc(avail.text)}</p>
        ${action}
      </div>
    </article>`;
}

function mount(id, products) {
  const el = document.getElementById(id);
  if (!el) return;
  el.innerHTML = products.map(cardHTML).join("");
}

function countItems() {
  return state.cart.reduce((sum, line) => sum + line.qty, 0);
}

function updateCount() {
  const count = countItems();
  document.querySelectorAll("[data-cart-count]").forEach((el) => {
    el.textContent = count > 99 ? "99+" : String(count);
  });
  document.querySelectorAll(".cart-btn").forEach((link) => {
    link.setAttribute("aria-label", `Cart, ${count} ${count === 1 ? "item" : "items"}`);
  });
}

function totals() {
  const subtotal = roundMoney(state.cart.reduce((sum, line) => {
    const product = productById(line.id);
    return sum + (product ? product.price * line.qty : 0);
  }, 0));
  const off = state.promo === "WELCOME10" ? roundMoney(subtotal * 0.1) : 0;
  const discounted = roundMoney(subtotal - off);
  const ship = subtotal === 0 ? 0 : state.ship === "express" ? 12.99 : discounted >= 49 ? 0 : 5.99;
  return { subtotal, off, ship, total: roundMoney(discounted + ship), discounted };
}

function summaryHTML(withCheckout) {
  const bill = totals();
  const lines = state.cart.map((line) => {
    const product = productById(line.id);
    if (!product) return "";
    return `<li><span>${esc(product.name)} × ${line.qty}</span><span>${money(product.price * line.qty)}</span></li>`;
  }).join("");
  const standardLabel = bill.subtotal === 0 || bill.discounted >= 49 ? "Free" : "$5.99";
  const need = roundMoney(49 - bill.discounted);
  const hint = bill.subtotal > 0 && state.ship !== "express" && need > 0
    ? `<p class="fine">Add ${money(need)} for free standard shipping.</p>`
    : "";
  const note = state.promo === "WELCOME10" ? "Code applied: 10% off." : promoMessage;
  const checkout = withCheckout
    ? `<a class="btn btn-accent w-100 mt-3" href="checkout.html" ${state.cart.length ? "" : 'aria-disabled="true" tabindex="-1"'}>Checkout</a>`
    : `<button class="btn btn-accent w-100 mt-3" type="submit">Place order</button>`;
  return `
    <aside class="summary-card">
      <h2>Order summary</h2>
      <ul class="summary-lines">${lines}</ul>
      <div class="sum-row"><span>Subtotal</span><span>${money(bill.subtotal)}</span></div>
      <div class="sum-row"><span>Discount</span><span>−${money(bill.off)}</span></div>
      <div class="sum-row"><span>Shipping</span><span>${bill.ship === 0 ? "Free" : money(bill.ship)}</span></div>
      ${hint}
      <div class="sum-row total"><span>Total</span><span>${money(bill.total)}</span></div>
      <div class="ship-options">
        <label><span><input type="radio" name="ship-choice" value="standard" ${state.ship !== "express" ? "checked" : ""}> Standard</span><small>${standardLabel}</small></label>
        <label><span><input type="radio" name="ship-choice" value="express" ${state.ship === "express" ? "checked" : ""}> Express</span><small>$12.99</small></label>
      </div>
      <div class="promo">
        <label class="visually-hidden" for="promo-code">Promo code</label>
        <input id="promo-code" class="form-control" placeholder="Promo code" value="${esc(state.promo)}" autocomplete="off">
        <button class="btn btn-ghost" type="button" data-promo>Apply</button>
      </div>
      <p id="promo-note" class="promo-note">${esc(note)}</p>
      <p class="fine">Try WELCOME10 for 10% off.${state.promo ? ' <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-promo-clear>Remove code</button>' : ""}</p>
      ${checkout}
      <p class="fine">Demo checkout. No payment is processed.</p>
    </aside>`;
}

function renderSummary(targetId) {
  const target = document.getElementById(targetId);
  if (!target) return;
  target.innerHTML = summaryHTML(false);
}

function showToast(name) {
  const el = document.getElementById("cart-toast");
  const text = document.getElementById("toast-text");
  if (!el || !text || !window.bootstrap) return;
  text.textContent = `${name} added to cart`;
  window.bootstrap.Toast.getOrCreateInstance(el, { delay: 2600 }).show();
}

function addToCart(id, qty) {
  const product = productById(id);
  const cap = maxQty(product);
  if (!product || cap === 0) return;
  const line = state.cart.find((item) => item.id === id);
  const nextQty = Math.min(cap, (line ? line.qty : 0) + qty);
  if (line) line.qty = nextQty;
  else state.cart.push({ id, qty: nextQty });
  persist();
  updateCount();
  showToast(product.name);
  const page = document.body.dataset.page;
  if (page === "cart") renderCart();
  if (page === "checkout") renderSummary("order-summary");
}

function changeQty(id, direction) {
  const line = state.cart.find((item) => item.id === id);
  const product = productById(id);
  if (!line || !product) return;
  const next = line.qty + direction;
  if (next <= 0) state.cart = state.cart.filter((item) => item.id !== id);
  else line.qty = Math.min(Math.max(maxQty(product), 1), next);
  persist();
  updateCount();
  if (document.body.dataset.page === "cart") renderCart();
  if (document.body.dataset.page === "checkout") renderSummary("order-summary");
}

function applyPromo() {
  const input = document.getElementById("promo-code");
  const code = (input?.value || "").trim().toUpperCase();
  if (code === "WELCOME10") {
    state.promo = "WELCOME10";
    promoMessage = "";
  } else if (!code) {
    state.promo = "";
    promoMessage = "";
  } else {
    state.promo = "";
    promoMessage = "That code is not active. Try WELCOME10.";
  }
  persist();
  if (document.body.dataset.page === "cart") renderCart();
  if (document.body.dataset.page === "checkout") renderSummary("order-summary");
}

function renderHome() {
  const deals = PRODUCTS.filter((product) => product.compare && product.stock !== 0).slice(0, 4);
  const popular = [...PRODUCTS].sort((a, b) => b.reviews - a.reviews).slice(0, 8);
  const more = PRODUCTS.filter((product) => !popular.includes(product)).slice(0, 4);
  mount("deal-grid", deals);
  mount("feature-grid", popular);
  mount("more-grid", more);
  const moreSection = document.getElementById("more-section");
  if (moreSection) moreSection.hidden = more.length === 0;
}

function startClock() {
  const el = document.getElementById("deal-clock");
  if (!el) return;
  const tick = () => {
    let diff = Math.max(0, state.dealEnd - Date.now());
    const hours = Math.floor(diff / 3600000);
    diff %= 3600000;
    const minutes = Math.floor(diff / 60000);
    diff %= 60000;
    const seconds = Math.floor(diff / 1000);
    el.textContent = [hours, minutes, seconds].map((n) => String(n).padStart(2, "0")).join(":");
  };
  tick();
  window.setInterval(tick, 1000);
}

function shopFilters() {
  return {
    q: (document.getElementById("search-q")?.value || "").trim().toLowerCase(),
    cat: document.querySelector('input[name="dept"]:checked')?.value || "",
    max: Number(document.getElementById("price-max").value),
    rated: document.getElementById("rated").checked,
    stock: document.getElementById("instock").checked,
    sort: document.getElementById("sort").value,
    page: Math.max(1, Number(document.getElementById("shop-grid").dataset.page || 1)),
  };
}

function renderShop() {
  const filters = shopFilters();
  let list = PRODUCTS.filter((product) => {
    if (filters.cat && product.category !== filters.cat) return false;
    if (product.price > filters.max) return false;
    if (filters.rated && product.rating < 4) return false;
    if (filters.stock && product.stock === 0) return false;
    if (filters.q && !`${product.name} ${product.category} ${product.blurb}`.toLowerCase().includes(filters.q)) return false;
    return true;
  });
  if (filters.sort === "price-asc") list.sort((a, b) => a.price - b.price);
  else if (filters.sort === "price-desc") list.sort((a, b) => b.price - a.price);
  else if (filters.sort === "rating") list.sort((a, b) => b.rating - a.rating || b.reviews - a.reviews);
  else if (filters.sort === "reviews") list.sort((a, b) => b.reviews - a.reviews);

  const pages = Math.max(1, Math.ceil(list.length / PAGE_SIZE));
  const page = Math.min(filters.page, pages);
  document.getElementById("shop-grid").dataset.page = String(page);
  const slice = list.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);
  const title = document.getElementById("shop-title");
  title.textContent = filters.cat ? catLabel(filters.cat) : filters.q ? `Results for “${filters.q}”` : "All products";
  document.title = `${title.textContent} — Northbay`;
  document.getElementById("result-count").textContent = `${list.length} ${list.length === 1 ? "product" : "products"}`;
  const select = document.getElementById("search-cat");
  if (select) select.value = filters.cat;

  const grid = document.getElementById("shop-grid");
  grid.innerHTML = slice.length
    ? slice.map(cardHTML).join("")
    : `<div class="empty-state" style="grid-column: 1 / -1">
        <i class="bi bi-search"></i>
        <h2>No matches</h2>
        <p>Try another department or a higher price.</p>
        <button class="btn btn-accent" type="button" data-clear>Clear filters</button>
      </div>`;

  const pager = document.getElementById("pager");
  if (pages < 2) {
    pager.innerHTML = "";
  } else {
    const buttons = [];
    for (let i = 1; i <= pages; i += 1) {
      buttons.push(`<button type="button" data-page="${i}" ${i === page ? 'aria-current="page"' : ""}>${i}</button>`);
    }
    pager.innerHTML = `
      <button type="button" data-page="${Math.max(1, page - 1)}" ${page === 1 ? "disabled" : ""} aria-label="Previous page">‹</button>
      ${buttons.join("")}
      <button type="button" data-page="${Math.min(pages, page + 1)}" ${page === pages ? "disabled" : ""} aria-label="Next page">›</button>`;
  }

  const params = new URLSearchParams();
  if (filters.q) params.set("q", filters.q);
  if (filters.cat) params.set("cat", filters.cat);
  if (filters.max < 200) params.set("max", String(filters.max));
  if (filters.rated) params.set("rated", "1");
  if (filters.stock) params.set("stock", "1");
  if (filters.sort !== "featured") params.set("sort", filters.sort);
  if (page > 1) params.set("page", String(page));
  const qs = params.toString();
  history.replaceState(null, "", qs ? `?${qs}` : location.pathname);
}

function initShop() {
  const params = new URLSearchParams(location.search);
  const query = params.get("q") || "";
  const category = params.get("cat") || "";
  const input = document.getElementById("search-q");
  if (input) input.value = query;
  const select = document.getElementById("search-cat");
  if (select && [...select.options].some((option) => option.value === category)) select.value = category;

  const wrap = document.getElementById("cat-filters");
  wrap.innerHTML = [
    `<label class="cat-option"><input type="radio" name="dept" value="" ${category ? "" : "checked"}> All departments</label>`,
    ...CATEGORIES.map((item) => `<label class="cat-option"><input type="radio" name="dept" value="${esc(item)}" ${item === category ? "checked" : ""}> ${esc(catLabel(item))}</label>`),
  ].join("");

  const range = document.getElementById("price-max");
  if (params.get("max")) range.value = params.get("max");
  document.getElementById("price-label").textContent = money(Number(range.value));
  document.getElementById("rated").checked = params.get("rated") === "1";
  document.getElementById("instock").checked = params.get("stock") === "1";
  const sort = params.get("sort") || "featured";
  if ([...document.getElementById("sort").options].some((option) => option.value === sort)) {
    document.getElementById("sort").value = sort;
  }
  document.getElementById("shop-grid").dataset.page = String(Math.max(1, Number(params.get("page") || 1)));

  const resetAndRender = () => {
    document.getElementById("shop-grid").dataset.page = "1";
    document.getElementById("price-label").textContent = money(Number(range.value));
    renderShop();
  };
  wrap.addEventListener("change", resetAndRender);
  range.addEventListener("input", resetAndRender);
  document.getElementById("rated").addEventListener("change", resetAndRender);
  document.getElementById("instock").addEventListener("change", resetAndRender);
  document.getElementById("sort").addEventListener("change", resetAndRender);
  document.getElementById("filters").addEventListener("submit", (event) => event.preventDefault());
  document.getElementById("clear-filters").addEventListener("click", () => {
    location.href = "shop.html";
  });
  document.getElementById("shop-grid").addEventListener("click", (event) => {
    if (event.target.closest("[data-clear]")) location.href = "shop.html";
  });
  document.getElementById("pager").addEventListener("click", (event) => {
    const button = event.target.closest("[data-page]");
    if (!button || button.disabled) return;
    document.getElementById("shop-grid").dataset.page = button.dataset.page;
    renderShop();
  });
  renderShop();
}

function choiceRow(label, values) {
  if (!values.length) return "";
  return `
    <div class="mb-3">
      <div class="fw-semibold mb-2">${esc(label)}</div>
      <div class="choice-row">
        ${values.map((value, index) => `<button type="button" class="choice" aria-pressed="${index === 0 ? "true" : "false"}">${esc(value)}</button>`).join("")}
      </div>
    </div>`;
}

function renderProduct() {
  const root = document.getElementById("product-root");
  const id = new URLSearchParams(location.search).get("id");
  const product = id ? productById(id) : PRODUCTS[0];
  if (!product) {
    root.innerHTML = `
      <div class="empty-state">
        <i class="bi bi-box-seam"></i>
        <h2>We could not find that product</h2>
        <p>It may have left the catalog.</p>
        <a class="btn btn-accent" href="shop.html">Browse the shop</a>
      </div>`;
    return;
  }
  document.title = `${product.name} — Northbay`;
  const avail = availability(product);
  const save = product.compare ? Math.round((1 - product.price / product.compare) * 100) : 0;
  const cap = maxQty(product);
  const actions = cap === 0
    ? '<button class="btn btn-accent w-100" type="button" disabled>Sold out</button>'
    : `<div class="d-flex flex-wrap gap-2 mt-3">
        <button class="btn btn-accent flex-grow-1" type="button" data-add="${esc(product.id)}" data-use-qty>Add to cart</button>
        <button class="btn btn-ghost" type="button" data-buy="${esc(product.id)}">Buy now</button>
      </div>`;
  const related = PRODUCTS.filter((item) => item.id !== product.id && item.category === product.category).slice(0, 4);

  root.innerHTML = `
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item"><a href="shop.html?cat=${esc(product.category)}">${esc(catLabel(product.category))}</a></li>
        <li class="breadcrumb-item active" aria-current="page">${esc(product.name)}</li>
      </ol>
    </nav>
    <div class="product-layout">
      <div>
        <div class="gallery-main">
          <img id="gallery-main" src="${esc(product.images[0])}" alt="${esc(product.name)}" onerror="this.style.visibility='hidden'">
        </div>
        <div class="thumbs">
          ${product.images.map((image, index) => `
            <button type="button" data-thumb="${esc(image)}" aria-label="Photo ${index + 1}" aria-pressed="${index === 0 ? "true" : "false"}">
              <img src="${esc(image)}" alt="" onerror="this.style.visibility='hidden'">
            </button>`).join("")}
        </div>
      </div>
      <div>
        <p class="product-cat mb-1">${esc(catLabel(product.category))}</p>
        <h1 class="product-title">${esc(product.name)}</h1>
        <div class="stars" aria-label="Rated ${product.rating.toFixed(1)} out of 5">${stars(product.rating)}<span class="rating-count">${product.reviews.toLocaleString()} ratings</span></div>
        <div class="price-row mt-3">
          <span class="price-lg">${money(product.price)}</span>
          ${product.compare ? `<s class="was">${money(product.compare)}</s><span class="save">Save ${save}%</span>` : ""}
        </div>
        <p class="mt-3 mb-3">${esc(product.blurb)}</p>
        ${choiceRow("Color", product.colors)}
        ${choiceRow("Size", product.sizes)}
        ${cap === 0 ? "" : `
          <label class="fw-semibold mb-2" for="qty">Quantity</label>
          <input id="qty" class="form-control qty-input mb-2" type="number" min="1" max="${cap}" value="1">`}
        <div class="buy-box">
          <p class="product-ship ${avail.cls} mb-2"><i class="bi bi-truck"></i> ${esc(avail.text)}</p>
          ${actions}
          <div class="ship-box">
            <i class="bi bi-box-seam"></i>
            <div>
              <strong>Arrives in 2–4 business days</strong>
              <p>Standard shipping is free on orders of $49 or more.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="detail-tabs">
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#tab-about">About</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#tab-specs">Details</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#tab-reviews">Sample reviews</button></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-about">
          <p>${esc(product.blurb)}</p>
          <p class="mb-0">${esc(product.details)}</p>
        </div>
        <div class="tab-pane fade" id="tab-specs">
          <table class="table mb-0">
            <tbody>
              ${product.specs.map(([label, value]) => `<tr><th scope="row">${esc(label)}</th><td>${esc(value)}</td></tr>`).join("")}
            </tbody>
          </table>
        </div>
        <div class="tab-pane fade" id="tab-reviews">
          <p class="fine">Sample copy for this template, not live customer feedback.</p>
          ${SAMPLE_REVIEWS.map((review) => `
            <article class="review">
              <div class="stars" aria-label="Rated ${review.stars} out of 5">${stars(review.stars)}</div>
              <h3>${esc(review.title)}</h3>
              <p>${esc(review.body)}</p>
              <p class="fine mb-0">Verified buyer · ${esc(review.date)}</p>
            </article>`).join("")}
        </div>
      </div>
    </div>
    ${related.length ? `<section class="section px-0">
      <div class="section-head">
        <h2>More in ${esc(catLabel(product.category))}</h2>
        <a href="shop.html?cat=${esc(product.category)}">See the department</a>
      </div>
      <div class="product-grid">${related.map(cardHTML).join("")}</div>
    </section>` : ""}`;
}

function renderCart() {
  const root = document.getElementById("cart-root");
  if (!state.cart.length) {
    root.innerHTML = `
      <div class="empty-state">
        <i class="bi bi-bag"></i>
        <h2>Your cart is empty</h2>
        <p>Add a kettle, a throw, or whatever the week needs.</p>
        <a class="btn btn-accent" href="shop.html">Browse products</a>
      </div>`;
    return;
  }
  const lines = state.cart.map((line) => {
    const product = productById(line.id);
    if (!product) return "";
    return `
      <article class="cart-line">
        <a href="product.html?id=${esc(product.id)}"><img src="${esc(product.images[0])}" alt="" onerror="this.style.visibility='hidden'"></a>
        <div>
          <h2><a href="product.html?id=${esc(product.id)}">${esc(product.name)}</a></h2>
          <p class="fine mb-2">${money(product.price)} each</p>
          <div class="qty-step" aria-label="Quantity for ${esc(product.name)}">
            <button type="button" data-qty="${esc(product.id)}" data-dir="-1" aria-label="Decrease quantity">−</button>
            <span>${line.qty}</span>
            <button type="button" data-qty="${esc(product.id)}" data-dir="1" aria-label="Increase quantity">+</button>
          </div>
          <button type="button" class="btn btn-link btn-sm px-0" data-remove="${esc(product.id)}">Remove</button>
        </div>
        <div class="line-total">${money(product.price * line.qty)}</div>
      </article>`;
  }).join("");
  root.innerHTML = `
    <div class="row g-4">
      <div class="col-lg-8"><div class="panel">${lines}</div></div>
      <div class="col-lg-4">${summaryHTML(true)}</div>
    </div>`;
}

function initCheckout() {
  const form = document.getElementById("checkout-form");
  const empty = document.getElementById("empty-checkout");
  if (!state.cart.length) {
    form.hidden = true;
    empty.hidden = false;
    return;
  }
  renderSummary("order-summary");
  const syncPay = () => {
    const delivery = document.getElementById("pay-delivery").checked;
    document.getElementById("card-fields").hidden = delivery;
    document.querySelectorAll("#card-fields input").forEach((input) => {
      input.required = !delivery;
    });
  };
  document.querySelectorAll('input[name="pay"]').forEach((input) => {
    input.addEventListener("change", syncPay);
  });
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    if (!state.cart.length) return;
    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      form.querySelector(":invalid")?.focus();
      return;
    }
    const order = `NB-${Math.floor(100000 + Math.random() * 900000)}`;
    state.cart = [];
    state.promo = "";
    persist();
    updateCount();
    form.hidden = true;
    const done = document.getElementById("confirmation");
    done.hidden = false;
    done.querySelector("[data-order]").textContent = order;
  });
}

function requestedQty(trigger) {
  if (!(trigger.hasAttribute("data-use-qty") || trigger.hasAttribute("data-buy"))) return 1;
  return Math.max(1, Number(document.getElementById("qty")?.value) || 1);
}

function bindGlobal() {
  document.addEventListener("click", (event) => {
    const thumb = event.target.closest("[data-thumb]");
    const choice = event.target.closest(".choice");
    const add = event.target.closest("[data-add]");
    const buy = event.target.closest("[data-buy]");
    const remove = event.target.closest("[data-remove]");
    const qtyBtn = event.target.closest("[data-qty]");
    const promo = event.target.closest("[data-promo]");
    const clearPromo = event.target.closest("[data-promo-clear]");

    if (thumb) {
      const main = document.getElementById("gallery-main");
      if (main) main.src = thumb.dataset.thumb;
      document.querySelectorAll("[data-thumb]").forEach((button) => button.setAttribute("aria-pressed", "false"));
      thumb.setAttribute("aria-pressed", "true");
      return;
    }
    if (choice) {
      choice.parentElement.querySelectorAll(".choice").forEach((button) => button.setAttribute("aria-pressed", "false"));
      choice.setAttribute("aria-pressed", "true");
      return;
    }
    if (add) {
      addToCart(add.dataset.add, requestedQty(add));
      return;
    }
    if (buy) {
      addToCart(buy.dataset.buy, requestedQty(buy));
      location.href = "checkout.html";
      return;
    }
    if (remove) {
      state.cart = state.cart.filter((line) => line.id !== remove.dataset.remove);
      persist();
      updateCount();
      renderCart();
      return;
    }
    if (qtyBtn) {
      changeQty(qtyBtn.dataset.qty, Number(qtyBtn.dataset.dir));
      return;
    }
    if (clearPromo) {
      state.promo = "";
      promoMessage = "";
      persist();
      if (document.body.dataset.page === "cart") renderCart();
      if (document.body.dataset.page === "checkout") renderSummary("order-summary");
      return;
    }
    if (promo) applyPromo();
  });

  document.addEventListener("change", (event) => {
    if (event.target.name !== "ship-choice") return;
    state.ship = event.target.value === "express" ? "express" : "standard";
    persist();
    if (document.body.dataset.page === "cart") renderCart();
    if (document.body.dataset.page === "checkout") renderSummary("order-summary");
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Enter" && event.target.id === "promo-code") {
      event.preventDefault();
      applyPromo();
    }
  });

  const news = document.getElementById("news-form");
  if (news) {
    news.addEventListener("submit", (event) => {
      event.preventDefault();
      const msg = document.getElementById("news-msg");
      if (msg) msg.hidden = false;
      news.reset();
    });
  }

  const auth = document.getElementById("auth-form");
  if (auth) {
    auth.addEventListener("submit", (event) => {
      event.preventDefault();
      const note = document.getElementById("auth-note");
      if (note) note.hidden = false;
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  persist();
  updateCount();
  startClock();
  bindGlobal();
  const page = document.body.dataset.page;
  if (page === "home") renderHome();
  if (page === "shop") initShop();
  if (page === "product") renderProduct();
  if (page === "cart") renderCart();
  if (page === "checkout") initCheckout();
});
