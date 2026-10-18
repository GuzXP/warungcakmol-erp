<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: takeaway.php (USER TAKEAWAY ORDERING SYSTEM)
 * UNTUK: Pemesanan bawa pulang (takeaway) secara mandiri dari HP pelanggan.
 */

// 1. KONEKSI & UTILITY DATABASE
require 'fungsi.php';

$menu_items = query("SELECT * FROM menu ORDER BY id_menu ASC");

$menuData = [];
foreach ($menu_items as $row) {
    // Normalisasi Nama Kategori
    $kategori = trim(html_entity_decode($row['kategori']));
    $kategori = str_replace("\xc2\xa0", " ", $kategori);

    // Casting harga ke integer
    $menuData[] = [
        "id" => $row['id_menu'], 
        "name" => sanitize($row['nama_menu']),
        "price" => (int)$row['harga'], 
        "description" => sanitize($row['deskripsi']),
        "category" => sanitize($kategori), 
        "badge" => sanitize($row['badge']),       
        "badgeColor" => sanitize($row['badge_color']),
        "image" => "../img/" . sanitize($row['gambar']) 
    ];
}

// DEFINISI TAB KATEGORI DINAMIS DARI DATABASE
$kategori_db = query("SELECT * FROM kategori ORDER BY urutan ASC");
$categories = [
  ["id" => "all", "name" => "Semua Menu", "icon" => "utensils-crossed"]
];
foreach ($kategori_db as $kat) {
    // Tentukan nama pendek untuk tombol tab agar pas di mobile screen
    $short_name = $kat['nama_kategori'];
    if ($kat['nama_kategori'] === 'Nasi Goreng Series') $short_name = 'Nasi Goreng';
    elseif ($kat['nama_kategori'] === 'Mie & Noodle Series') $short_name = 'Mie & Noodle';
    elseif ($kat['nama_kategori'] === 'Capcay (Special)') $short_name = 'Capcay';
    elseif ($kat['nama_kategori'] === 'Minuman & Pelengkap') $short_name = 'Minuman';

    $categories[] = [
        "id" => $kat['nama_kategori'],
        "name" => $short_name,
        "icon" => $kat['icon_name'] ? $kat['icon_name'] : 'utensils'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bawa Pulang | Nasi Goreng Cak Mol</title>
    
    <!-- LOAD STYLE CUSTOM -->
    <link rel="stylesheet" href="../css/style-user-order.css">
    
    <!-- ICON LIBRARY (Lucide) -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <!-- FLOATING NAV (Mobile Only) -->
    <div class="floating-nav">
        <a href="index.php" class="floating-nav-btn">Home</a>
        <a href="order.php" class="floating-nav-btn">Dine In</a>
        <a href="takeaway.php" class="floating-nav-btn active">Bawa Pulang</a>
    </div>

    <!-- HEADER & CART STATUS -->
    <header class="sticky-header">
        <div class="container header-inner">
            <div class="logo-wrapper">
                <div class="relative">
                    <i data-lucide="chef-hat" class="icon-xl"></i>
                    <i data-lucide="flame" class="flame-icon-header"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Nasi Goreng Cak Mol</h1>
                    <p class="text-xs text-muted-foreground text-meja-wrapper">
                        Bawa Pulang • Pulomas
                    </p>
                </div>
            </div>
            
            <!-- Tombol Buka Keranjang -->
            <button class="btn btn-outline btn-lg relative cart-trigger-btn" onclick="openDrawer()">
                <i data-lucide="shopping-cart" class="icon-md"></i>
                <span class="cart-text font-semibold" style="margin-left: 0.5rem;">Keranjang</span>
                <span id="cart-count-badge" class="badge badge-primary cart-badge hidden">0</span>
            </button>
        </div>
    </header>

    <!-- TABS KATEGORI -->
    <section class="category-section">
        <div class="container tabs-scroll-area" id="category-tabs">
            <?php foreach($categories as $index => $cat): ?>
                <button class="btn <?php echo $cat['id'] === 'all' ? 'btn-default' : 'btn-outline'; ?> btn-sm" 
                        onclick="filterCategory('<?php echo $cat['id']; ?>', this)">
                    <i data-lucide="<?php echo $cat['icon']; ?>" class="icon-sm"></i>
                    <?php echo $cat['name']; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- MAIN MENU GRID -->
    <main class="container">
        <div class="menu-grid" id="menu-grid">
            <?php foreach($menuData as $index => $item): ?>
                <div class="card card-animate visible" 
                     data-category="<?php echo $item['category']; ?>" 
                     style="transition-delay: <?php echo $index * 0.05; ?>s">
                    
                    <div class="card-horizontal-wrapper">
                        <?php if(isset($item['image']) && $item['image']): ?>
                            <div class="card-img-container">
                                <img src="<?php echo $item['image']; ?>" class="card-img-order" alt="<?php echo $item['name']; ?>">
                            </div>
                        <?php else: ?>
                            <div class="card-img-placeholder">
                                <i data-lucide="image" class="text-muted-foreground"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-content">
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="font-bold text-base capitalize"><?php echo $item['name']; ?></h3>
                                <?php if($item['badge']): ?>
                                    <span class="badge <?php echo $item['badgeColor']; ?>"><?php echo $item['badge']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-1"><?php echo $item['description']; ?></p>
                            
                            <div class="card-footer-action mt-3">
                                <span class="price-text">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                <button class="btn btn-default btn-sm" onclick='openCustomizeModal(<?php echo json_encode($item); ?>)'>
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- CUSTOMIZE MODAL OVERLAY -->
    <div id="customize-overlay" class="overlay" onclick="closeModal('customize-overlay')">
        <div class="modal-card" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 id="modal-menu-title" class="text-xl font-bold capitalize">Customize Menu</h3>
                <button class="btn btn-ghost btn-icon" onclick="closeModal('customize-overlay')">
                    <i data-lucide="x" class="icon-md"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <!-- Level Pedas -->
                <div id="spice-section">
                    <label class="text-sm font-semibold">Tingkat Kepedasan</label>
                    <div class="spice-grid mt-2">
                        <?php for($l=0; $l<=4; $l++): ?>
                            <button class="spice-btn-v2 <?php echo $l===1 ? 'active' : ''; ?>" onclick="selectSpice(<?php echo $l; ?>, this)">
                                <?php echo $l; ?>
                                <?php if($l===0): ?>
                                    <div class="text-[10px] text-muted-foreground font-normal">Ori</div>
                                <?php elseif($l===4): ?>
                                    <div class="text-[10px] text-red-500 font-bold">Max</div>
                                <?php endif; ?>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <p id="spice-label-text" class="text-xs text-muted-foreground">Sedikit pedas</p>
                </div>

                <!-- Input Catatan -->
                <div style="margin-top: 1.5rem;">
                    <label class="text-sm font-medium">Catatan Khusus (Opsional)</label>
                    <textarea id="modal-notes" class="textarea-v2" rows="3" placeholder="Contoh: Tanpa bawang, tambah acar, dll..."></textarea>
                </div>

                <!-- Footer Action Modal -->
                <div style="margin-top: 2rem; border-top: 1px solid oklch(var(--border)); padding-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p class="text-sm text-muted-foreground">Total</p>
                        <p id="modal-total-price" class="text-2xl font-bold">Rp 0</p>
                    </div>
                    <button class="btn btn-default btn-lg" onclick="addToCartAction()">
                        <i data-lucide="check" class="icon-sm"></i>
                        Tambahkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CART DRAWER (SIDEBAR KERANJANG) -->
    <div id="drawer-overlay" class="overlay" onclick="closeDrawer()" style="background-color: oklch(var(--background) / 0.8)"></div>
    
    <div id="cart-drawer" class="drawer">
        <div class="drawer-header">
            <div>
                <h2 class="text-2xl font-bold">Keranjang</h2>
                <p id="cart-items-count-text" class="text-sm text-muted-foreground">0 item</p>
            </div>
            <button class="btn btn-ghost btn-icon" onclick="closeDrawer()">
                <i data-lucide="x" class="icon-md"></i>
            </button>
        </div>

        <div class="drawer-content" id="cart-items-list">
            <div id="cart-empty-state" class="h-full flex flex-col items-center justify-center text-center gap-4">
                <i data-lucide="shopping-cart" style="width: 4rem; height: 4rem;" class="text-muted-foreground"></i>
                <div>
                    <p class="font-semibold">Keranjang Kosong</p>
                    <p class="text-sm text-muted-foreground">Tambahkan menu favorit Anda</p>
                </div>
            </div>
        </div>

        <div class="drawer-footer" id="cart-footer">
            <!-- Input Nama Pelanggan (Wajib untuk Takeaway) -->
            <div style="margin-bottom: 1.25rem;">
                <label for="customer-name" class="text-sm font-semibold" style="display: block; margin-bottom: 0.5rem; color: oklch(var(--foreground));">Nama Anda (Wajib)</label>
                <input type="text" id="customer-name" placeholder="Nama untuk pesanan..." style="width: 100%; padding: 0.75rem; border: 1px solid oklch(var(--border)); border-radius: var(--radius); background: oklch(var(--background)); color: oklch(var(--foreground)); font-size: 0.875rem; outline: none; transition: border-color 0.2s;">
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span id="cart-subtotal" class="font-semibold">Rp 0</span>
                </div>
                <div style="height: 1px; background: oklch(var(--border));"></div>
                <div class="flex justify-between">
                    <span class="font-semibold text-lg">Total</span>
                    <span id="cart-total-footer" class="text-2xl font-bold">Rp 0</span>
                </div>
            </div>
            <button class="btn btn-default w-full btn-lg" onclick="confirmOrder()">
                <i data-lucide="check" class="icon-md"></i>
                Konfirmasi Pesanan
            </button>
            <p class="text-xs text-center text-muted-foreground mt-4">Pesanan akan langsung dikirim ke dapur</p>
        </div>
    </div>

    <!-- OVERLAY ANIMASI MEMASAK -->
    <div id="cooking-overlay" class="cooking-overlay">
        <div class="cooking-container">
            <div class="cooking-animation">
                <i data-lucide="chef-hat" class="cooking-icon"></i>
                <div class="cooking-particles">
                    <i data-lucide="flame" class="particle"></i>
                    <i data-lucide="flame" class="particle"></i>
                    <i data-lucide="flame" class="particle"></i>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">Sedang Memasak...</h2>
            <p class="text-white/80">Pesanan Anda sedang disiapkan dengan cinta.</p>
        </div>
    </div>

    <!-- OVERLAY STRUK DIGITAL -->
    <div id="receipt-overlay" class="overlay" onclick="closeModal('receipt-overlay')">
        <div class="receipt-card" onclick="event.stopPropagation()">
            <div class="receipt-zigzag-top"></div>
            
            <div class="receipt-header">
                <div class="receipt-logo">
                    <i data-lucide="chef-hat" class="Icon-lg"></i>
                </div>
                <h2 class="text-xl font-bold mt-2">Nasi Goreng Cak Mol</h2>
                <p class="text-xs text-muted-foreground">Jl. Pulo Mas Barat V No.4</p>
                <div class="receipt-divider"></div>
            </div>

            <div class="receipt-body">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-muted-foreground">Tanggal:</span>
                    <span id="receipt-date">-</span>
                </div>
                <div class="flex justify-between text-xs mb-4">
                    <span class="text-muted-foreground">Order ID:</span>
                    <span id="receipt-id">-</span>
                </div>

                <div class="receipt-items-container" id="receipt-items"></div>

                <div class="receipt-divider"></div>

                <div class="flex justify-between font-bold text-lg mt-4">
                    <span>TOTAL</span>
                    <span id="receipt-total">Rp 0</span>
                </div>
            </div>

            <div class="receipt-footer">
                <div class="receipt-divider"></div>
                <p class="text-xs font-medium mb-1">Terima Kasih!</p>
                <p class="text-[10px] text-muted-foreground">Silahkan tunjukkan struk ini saat pembayaran</p>
            </div>
            
            <div class="receipt-zigzag-bottom"></div>

            <button class="btn btn-default w-full mt-6" onclick="location.reload()">
                Selesai & Pesan Lagi
            </button>
        </div>
    </div>

    <!-- SCRIPT LOGIKA UTAMA -->
    <script>
        lucide.createIcons();

        // --- STATE VARIABLES ---
        let tableId = 0; // 0 untuk Takeaway
        let cart = []; 
        let currentlyCustomizing = null; 
        let selectedSpice = 1; 

        // Load data keranjang dari localStorage
        if (localStorage.getItem('cart')) {
            try {
                cart = JSON.parse(localStorage.getItem('cart'));
            } catch(e) {
                cart = [];
            }
        }

        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        /**
         * FUNGSI 1: FILTER KATEGORI MENU
         */
        function filterCategory(catId, btn) {
            const tabContainer = document.getElementById('category-tabs');
            tabContainer.querySelectorAll('.btn').forEach(b => {
                b.classList.remove('btn-default');
                b.classList.add('btn-outline');
            });
            btn.classList.add('btn-default');
            btn.classList.remove('btn-outline');

            const cards = document.querySelectorAll('.card[data-category]');
            cards.forEach(card => {
                const cat = card.getAttribute('data-category').trim();
                if (catId === 'all' || cat === catId.trim()) {
                    card.classList.remove('hidden');
                    requestAnimationFrame(() => {
                        card.classList.add('visible');
                    });
                } else {
                    card.classList.remove('visible');
                    card.classList.add('hidden');
                }
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        /**
         * FUNGSI 2: MODAL CUSTOMIZE ITEM
         */
        function openCustomizeModal(item) {
            currentlyCustomizing = item;
            document.getElementById('modal-menu-title').innerText = item.name;
            document.getElementById('modal-notes').value = '';
            
            const spiceSection = document.getElementById('spice-section');
            if (item.category.includes('Minuman')) {
                spiceSection.style.display = 'none';
                selectedSpice = 0;
            } else {
                spiceSection.style.display = 'block';
                selectedSpice = 1;
                const buttons = spiceSection.querySelectorAll('.spice-btn-v2');
                buttons.forEach((b, idx) => {
                    b.classList.toggle('active', idx === 1);
                });
                document.getElementById('spice-label-text').innerText = 'Sedikit pedas';
            }
            
            updateModalTotal();
            
            const overlay = document.getElementById('customize-overlay');
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function selectSpice(level, btn) {
            selectedSpice = level;
            const buttons = btn.parentElement.querySelectorAll('.spice-btn-v2');
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const labels = ['Tidak pedas', 'Sedikit pedas', 'Pedas sedang', 'Sangat pedas', 'Ekstra pedas! 🔥'];
            document.getElementById('spice-label-text').innerText = labels[level];
        }

        function updateModalTotal() {
            document.getElementById('modal-total-price').innerText = formatPrice(currentlyCustomizing.price);
        }

        function openDrawer() {
            document.getElementById('drawer-overlay').classList.add('open');
            document.getElementById('cart-drawer').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            document.getElementById('drawer-overlay').classList.remove('open');
            document.getElementById('cart-drawer').classList.remove('open');
            document.body.style.overflow = 'auto';
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
            document.body.style.overflow = 'auto';
        }

        function formatPrice(num) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        }

        /**
         * FUNGSI 3: CART MANAGEMENT
         */
        function addToCartAction() {
            const notes = document.getElementById('modal-notes').value;
            const existingIndex = cart.findIndex(c => 
                c.id === currentlyCustomizing.id && 
                c.spice === selectedSpice && 
                c.notes === notes
            );
            
            if (existingIndex > -1) {
                cart[existingIndex].quantity += 1;
            } else {
                cart.push({
                    ...currentlyCustomizing,
                    quantity: 1,
                    spice: selectedSpice,
                    notes: notes,
                    cartId: Date.now()
                });
            }
            
            updateCartUI();
            closeModal('customize-overlay');
            openDrawer();
        }

        function updateQuantity(cartId, delta) {
            const idx = cart.findIndex(c => c.cartId === cartId);
            if(idx > -1) {
                cart[idx].quantity += delta;
                if(cart[idx].quantity <= 0) cart.splice(idx, 1);
            }
            updateCartUI();
        }

        function removeFromCart(cartId) {
            cart = cart.filter(c => c.cartId !== cartId);
            updateCartUI();
        }

        function updateCartUI() {
            saveCart();
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            
            const badge = document.getElementById('cart-count-badge');
            badge.innerText = count;
            badge.classList.toggle('hidden', count === 0);
            
            document.getElementById('cart-items-count-text').innerText = count + ' item';
            
            const list = document.getElementById('cart-items-list');
            const empty = document.getElementById('cart-empty-state');
            const footer = document.getElementById('cart-footer');
            
            if (cart.length === 0) {
                list.innerHTML = '';
                list.appendChild(empty);
                footer.classList.add('hidden');
            } else {
                list.innerHTML = '<div class="flex flex-col gap-4"></div>';
                const wrapper = list.querySelector('div');
                
                let total = 0;
                cart.forEach(item => {
                    total += (item.price * item.quantity);
                    
                    const el = document.createElement('div');
                    el.className = 'cart-item-v2';
                    el.innerHTML = `
                        <div class="flex-1">
                            <h4 class="font-bold text-sm capitalize">${item.name.toLowerCase()}</h4>
                            <p class="text-[10px] text-muted-foreground mt-0.5">
                                ${item.spice > 0 && !item.category.includes('Minuman') ? `Lvl ${item.spice}` : ''}
                                ${item.notes ? `• ${item.notes}` : ''}
                            </p>
                            <span class="text-xs font-semibold text-primary mt-1 block">${formatPrice(item.price)}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button class="btn btn-outline btn-icon" style="width: 24px; height: 24px;" onclick="updateQuantity(${item.cartId}, -1)">
                                <i data-lucide="minus" class="icon-xs"></i>
                            </button>
                            <span class="text-sm font-bold w-4 text-center">${item.quantity}</span>
                            <button class="btn btn-outline btn-icon" style="width: 24px; height: 24px;" onclick="updateQuantity(${item.cartId}, 1)">
                                <i data-lucide="plus" class="icon-xs"></i>
                            </button>
                        </div>
                    `;
                    wrapper.appendChild(el);
                });
                
                document.getElementById('cart-subtotal').innerText = formatPrice(total);
                document.getElementById('cart-total-footer').innerText = formatPrice(total);
                footer.classList.remove('hidden');
                lucide.createIcons();
            }
        }

        /**
         * FUNGSI 4: CHECKOUT / KONFIRMASI PESANAN
         */
        async function confirmOrder() {
            if (cart.length === 0) return;
            
            const customerName = document.getElementById('customer-name').value.trim();
            if (customerName === '') {
                alert('Nama Anda wajib diisi untuk pemesanan bawa pulang (Takeaway)!');
                return;
            }
            
            const totalBayar = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            closeDrawer();
            document.body.style.overflow = 'hidden';

            const payloadItems = cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: item.quantity,
                spice: item.spice,
                notes: item.notes
            }));

            try {
                const response = await fetch('process_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        items: payloadItems,
                        total_bayar: totalBayar,
                        meja_id: 0, // 0 untuk Takeaway
                        nama_pelanggan: customerName,
                        tipe_pesanan: 'Takeaway'
                    })
                });
                
                const result = await response.json();
                
                if(result.status === 'success') {
                    const orderId = result.order_id;
                    const orderDate = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

                    const cooking = document.getElementById('cooking-overlay');
                    cooking.classList.add('active');

                    const receiptItems = document.getElementById('receipt-items');
                    receiptItems.innerHTML = '';
                    
                    cart.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'receipt-item-row';
                        row.innerHTML = `
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-medium text-sm capitalize">${item.name.toLowerCase()}</p>
                                    <p class="text-[10px] text-muted-foreground">
                                        ${item.spice > 0 && !item.category.includes('Minuman') ? `Level ${item.spice}` : ''} 
                                        ${item.notes ? `• ${item.notes}` : ''}
                                    </p>
                                    <p class="text-[10px]">${item.quantity} x ${formatPrice(item.price)}</p>
                                </div>
                                <p class="font-bold text-sm ml-2">${formatPrice(item.price * item.quantity)}</p>
                            </div>
                        `;
                        receiptItems.appendChild(row);
                    });
                    
                    document.getElementById('receipt-total').innerText = formatPrice(totalBayar);
                    document.getElementById('receipt-date').innerText = new Date().toLocaleDateString('id-ID') + ', ' + orderDate;
                    document.getElementById('receipt-id').innerText = '#CM-' + orderId;

                    setTimeout(() => {
                        cooking.classList.remove('active');
                        
                        const receiptOverlay = document.getElementById('receipt-overlay');
                        receiptOverlay.classList.add('open');
                        lucide.createIcons();
                        
                        cart = [];
                        document.getElementById('customer-name').value = '';
                        localStorage.removeItem('cart');
                        updateCartUI();
                    }, 3000);

                } else {
                    alert('Gagal membuat pesanan: ' + result.message);
                    document.body.style.overflow = 'auto';
                }

            } catch(err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi ke server!');
                document.body.style.overflow = 'auto';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateCartUI();
        });
    </script>
</body>
</html>
