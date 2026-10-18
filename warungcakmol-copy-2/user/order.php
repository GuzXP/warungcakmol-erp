<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: order.php (USER ORDERING SYSTEM)
 * FITUR UTAMA:
 * 1. Menampilkan Menu dari Database
 * 2. Sistem Keranjang Belanja (Client-Side Javascript)
 * 3. Kirim Pesanan ke Backend (AJAX Fetch)
 * 4. Animasi Memasak & Cetak Struk Digital
 */

// 1. KONEKSI & AMBIL DATA
require 'koneksi.php';

$result = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY id_menu ASC");

$menuData = [];
while($row = mysqli_fetch_assoc($result)) {
    // Normalisasi Nama Kategori (Hapus spasi aneh/hidden chars)
    $kategori = trim(html_entity_decode($row['kategori']));
    $kategori = str_replace("\xc2\xa0", " ", $kategori);

    // Casting harga ke integer agar mudah dijumlahkan di JS
    $menuData[] = [
        "id" => $row['id_menu'], 
        "name" => $row['nama_menu'],
        "price" => (int)$row['harga'], 
        "description" => $row['deskripsi'],
        "category" => $kategori, 
        "badge" => $row['badge'],       
        "badgeColor" => $row['badge_color'],
        "image" => "../img/" . $row['gambar'] 
    ];
}

// DEFINISI TAB KATEGORI (Hardcoded untuk UI Icon mapping)
$categories = [
  ["id" => "all", "name" => "Semua Menu", "icon" => "utensils-crossed"],
  ["id" => "Nasi Goreng Series", "name" => "Nasi Goreng", "icon" => "chef-hat"],
  ["id" => "Mie & Noodle Series", "name" => "Mie & Noodle", "icon" => "wheat"],
  ["id" => "Capcay (Special)", "name" => "Capcay", "icon" => "salad"],
  ["id" => "Minuman & Pelengkap", "name" => "Minuman", "icon" => "coffee"]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - Nasi Goreng Cak Mol</title>
    
    <!-- LOAD STYLE CUSTOM -->
    <link rel="stylesheet" href="../css/style-user-order.css">
    
    <!-- ICON LIBRARY (Lucide) -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <!-- FLOATING NAV (Mobile Only) -->
    <div class="floating-nav">
        <a href="index.php" class="floating-nav-btn">Home</a>
        <a href="#" class="floating-nav-btn active">Dine In</a>
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
                    <p class="text-xs text-muted-foreground">Meja <span id="table-display">1</span> • Pulomas</p>
                </div>
            </div>
            
            <!-- Tombol Buka Keranjang -->
            <button class="btn btn-outline btn-lg relative cart-trigger-btn" onclick="openDrawer()">
                <i data-lucide="shopping-cart" class="icon-md"></i>
                <span class="cart-text font-semibold" style="margin-left: 0.5rem;">Keranjang</span>
                <!-- Badge Merah Jumlah Item (Hidden by default) -->
                <span id="cart-count-badge" class="badge badge-primary cart-badge hidden">0</span>
            </button>
        </div>
    </header>

    <!-- TABS KATEGORI -->
    <section class="category-section">
        <div class="container tabs-scroll-area" id="category-tabs">
            <?php foreach($categories as $index => $cat): ?>
                <!-- Render Tombol Kategori -->
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
                <!-- CARD MENU ITEM -->
                <!-- Atribut data-category dipakai untuk filtering via JS -->
                <div class="card card-animate visible" 
                     data-category="<?php echo $item['category']; ?>" 
                     style="transition-delay: <?php echo $index * 0.05; ?>s">
                    
                    <div class="card-horizontal-wrapper">
                        <!-- Gambar Menu -->
                        <?php if(isset($item['image']) && $item['image']): ?>
                            <div class="card-img-container">
                                <img src="<?php echo $item['image']; ?>" class="card-img-order" alt="<?php echo $item['name']; ?>">
                            </div>
                        <?php else: ?>
                            <div class="card-img-container flex items-center justify-center">
                                <i data-lucide="image" class="text-muted-foreground icon-lg"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Konten Menu -->
                        <div class="card-content-v3">
                            <div>
                                <div class="flex justify-between items-start mb-1">
                                    <h3 class="card-title-v2 flex-1"><?php echo $item['name']; ?></h3>
                                    <p class="font-bold text-sm md:text-base ml-2 whitespace-nowrap">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <?php if($item['badge']): ?>
                                        <span class="badge <?php echo $item['badgeColor'] ?? 'badge-secondary'; ?>" style="font-size: 8px;">
                                            <?php echo $item['badge']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-desc-v2 mb-1"><?php echo $item['description']; ?></p>
                            </div>
                            
                            <!-- Tombol Trigger Modal Add -->
                            <!-- Mengirim seluruh objek $item ke fungsi JS via JSON encode -->
                            <button class="btn btn-default btn-sm w-full mt-2" 
                                    onclick="openCustomizeModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                <i data-lucide="plus" style="width: 0.8rem;"></i>
                                Tambah
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="order-footer">
        <div class="container footer-content">
            <div class="flex items-center justify-center gap-2">
                <i data-lucide="chef-hat" class="icon-md"></i>
                <p class="font-semibold">Nasi Goreng Cak Mol Pulomas</p>
            </div>
            <p class="text-sm text-muted-foreground">Melayani dengan sepenuh hati sejak 1998</p>
        </div>
    </footer>

    <!-- MODAL POPUP: CUSTOMIZE PESANAN -->
    <!-- (Pilih Pedas & Tambah Catatan) -->
    <div id="customize-overlay" class="overlay" onclick="closeModal('customize-overlay')">
        <div class="modal-card" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="modal-header">
                <div>
                    <h2 id="modal-title" class="text-xl font-bold"></h2>
                    <p id="modal-desc" class="text-sm text-muted-foreground mt-2"></p>
                </div>
                <button class="btn btn-ghost btn-icon" onclick="closeModal('customize-overlay')">
                    <i data-lucide="x"></i>
                </button>
            </div>
            
            <div class="modal-content">
                <!-- Selector Level Pedas -->
                <div id="spice-selector-div" class="flex flex-col gap-3">
                    <label class="text-sm font-medium flex items-center gap-2">
                        <i data-lucide="flame" class="icon-sm" style="color: #f97316;"></i>
                        Tingkat Kepedasan
                    </label>
                    <div class="spice-grid">
                        <?php for($i=0; $i<=4; $i++): ?>
                            <button class="spice-btn-v2 <?php echo $i===1 ? 'active' : ''; ?>" 
                                    onclick="setSpice(<?php echo $i; ?>, this)">
                                <?php echo $i === 0 ? 'Tidak' : $i; ?>
                                <?php if($i > 0): ?>
                                    <div class="spice-mini-flames">
                                        <?php for($j=0; $j<$i; $j++): ?>
                                            <i data-lucide="flame" class="spice-mini-flame"></i>
                                        <?php endfor; ?>
                                    </div>
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
            <!-- Items akan diinject oleh JS -->
            <div id="cart-empty-state" class="h-full flex flex-col items-center justify-center text-center gap-4">
                <i data-lucide="shopping-cart" style="width: 4rem; height: 4rem;" class="text-muted-foreground"></i>
                <div>
                    <p class="font-semibold">Keranjang Kosong</p>
                    <p class="text-sm text-muted-foreground">Tambahkan menu favorit Anda</p>
                </div>
            </div>
        </div>

        <div class="drawer-footer" id="cart-footer">
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
        // Inisialisasi Icon Lucide saat load
        lucide.createIcons();

        // --- STATE VARIABLES ---
        let cart = []; // Array penampung pesanan belanja
        let currentlyCustomizing = null; // Menyimpan item yg sedang dibuka di modal
        let selectedSpice = 1; // Default pedas level 1

        /**
         * FUNGSI 1: FILTER KATEGORI MENU
         * Menyembunyikan/menampilkan kartu menu sesuai ID kategori yang dipilih.
         */
        function filterCategory(catId, btn) {
            // Update UI Tombol Category (Active/Inactive state)
            const tabContainer = document.getElementById('category-tabs');
            tabContainer.querySelectorAll('.btn').forEach(b => {
                b.classList.remove('btn-default');
                b.classList.add('btn-outline');
            });
            btn.classList.add('btn-default');
            btn.classList.remove('btn-outline');

            // Logic Filtering Card
            const cards = document.querySelectorAll('.card[data-category]');
            cards.forEach(card => {
                const cat = card.getAttribute('data-category').trim();
                
                // Cek kecocokan (All atau Specific)
                if (catId === 'all' || cat === catId.trim()) {
                    card.classList.remove('hidden');
                    // Trik Animation Frame agar transisi CSS berjalan mulus
                    requestAnimationFrame(() => {
                        card.classList.add('visible');
                    });
                } else {
                    card.classList.remove('visible');
                    card.classList.add('hidden');
                }
            });
            
            // Scroll ke atas agar user tidak bingung
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        /**
         * FUNGSI 2: MODAL CUSTOMIZE ITEM
         * Membuka popup untuk memilih level pedas & catatan.
         */
        function openCustomizeModal(item) {
            currentlyCustomizing = item;
            selectedSpice = 1; // Reset ke level 1
            
            // Isi data modal
            document.getElementById('modal-title').innerText = item.name;
            document.getElementById('modal-desc').innerText = item.description;
            // Format Rupiah
            document.getElementById('modal-total-price').innerText = formatPrice(item.price);
            document.getElementById('modal-notes').value = '';
            
            // Logic Sembunyikan Pedas untuk Minuman
            const spiceDiv = document.getElementById('spice-selector-div');
            if(item.category.includes('Minuman') || item.category.includes('Pelengkap')) {
                spiceDiv.classList.add('hidden');
            } else {
                spiceDiv.classList.remove('hidden');
                resetSpiceButtons();
            }

            // Tampilkan Modal
            document.getElementById('customize-overlay').classList.add('open');
        }

        // Helper set level pedas (visual button update)
        function setSpice(level, btn) {
            selectedSpice = level;
            const btns = document.querySelectorAll('.spice-btn-v2');
            btns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const labels = ["Tanpa cabai", "Sedikit pedas", "Pedas sedang", "Pedas mantap", "Extra pedas!"];
            document.getElementById('spice-label-text').innerText = labels[level];
        }

        // Helper reset tampilan tombol pedas
        function resetSpiceButtons() {
            const btns = document.querySelectorAll('.spice-btn-v2');
            btns.forEach(b => b.classList.remove('active'));
            // Default level 1 yg aktif
            if(btns[1]) btns[1].classList.add('active');
            document.getElementById('spice-label-text').innerText = "Sedikit pedas";
        }

        // Menutup modal apapun berdasarkan ID
        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
            document.body.style.overflow = 'auto'; // Enable scroll kembali
        }

        /**
         * FUNGSI 3: CART MANAGEMENT (KERANJANG BELANJA)
         * Menambah, Mengurangi, dan Menghapus item dari array `cart`.
         */
        function addToCartAction() {
            const notes = document.getElementById('modal-notes').value;
            
            // Cek duplikasi: Item sama jika ID menu, level pedas, dan catatan sama persis.
            const existingIndex = cart.findIndex(c => 
                c.id === currentlyCustomizing.id && 
                c.spice === selectedSpice && 
                c.notes === notes
            );
            
            if (existingIndex > -1) {
                // Jika sudah ada, tambah qty saja
                cart[existingIndex].quantity += 1;
            } else {
                // Jika belum, push object baru
                cart.push({
                    ...currentlyCustomizing,
                    quantity: 1,
                    spice: selectedSpice,
                    notes: notes,
                    cartId: Date.now() // Unique ID untuk manipulasi array lokal
                });
            }
            
            updateCartUI(); // Refresh Tampilan
            closeModal('customize-overlay');
            openDrawer(); // Auto buka keranjang untuk feedback user
        }

        // Update Qty (+ / -) di Drawer
        function updateQuantity(cartId, delta) {
            const idx = cart.findIndex(c => c.cartId === cartId);
            if(idx > -1) {
                cart[idx].quantity += delta;
                // Hapus jika qty jadi 0
                if(cart[idx].quantity <= 0) cart.splice(idx, 1);
            }
            updateCartUI();
        }

        // Hapus item dari icon Tong Sampah/Silang
        function removeFromCart(cartId) {
            cart = cart.filter(c => c.cartId !== cartId);
            updateCartUI();
        }

        // RENDER ULANG TAMPILAN KERANJANG
        function updateCartUI() {
            // Hitung total item & harga
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            
            // Badge Merah di header
            const badge = document.getElementById('cart-count-badge');
            badge.innerText = count;
            badge.classList.toggle('hidden', count === 0);
            
            // Text di header drawer
            document.getElementById('cart-items-count-text').innerText = count + ' item';
            
            const list = document.getElementById('cart-items-list');
            const empty = document.getElementById('cart-empty-state');
            const footer = document.getElementById('cart-footer');
            
            // Toggle Empty State vs List
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
                    
                    // Create elemen HTML item keranjang
                    const el = document.createElement('div');
                    el.className = 'cart-item-v2';
                    // Menggunakan Class CSS baru (.btn-qty-control, .text-qty-display)
                    // Tidak ada lagi style inline hardcode
                    el.innerHTML = `
                        <div class="flex flex-col gap-2">
                            <div class="flex items-start justify-between">
                                <h3 class="font-semibold text-sm">${item.name}</h3>
                                <button onclick="removeFromCart(${item.cartId})" class="cart-remove-btn">
                                    <i data-lucide="x" class="icon-sm"></i>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                ${item.spice > 0 && !item.category.includes('Minuman') 
                                    ? `<div class="flex items-center gap-1"><i data-lucide="flame" class="icon-level-flame"></i><span>Level ${item.spice}</span></div>` 
                                    : ''}
                                ${item.notes ? `<span>• ${item.notes}</span>` : ''}
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-2">
                                    <button class="btn btn-outline btn-qty-control" onclick="updateQuantity(${item.cartId}, -1)">-</button>
                                    <span class="text-qty-display">${item.quantity}</span>
                                    <button class="btn btn-outline btn-qty-control" onclick="updateQuantity(${item.cartId}, 1)">+</button>
                                </div>
                                <span class="font-bold text-sm">${formatPrice(item.price * item.quantity)}</span>
                            </div>
                        </div>
                    `;
                    wrapper.appendChild(el);
                });
                
                // Update Total Footer
                document.getElementById('cart-subtotal').innerText = formatPrice(total);
                document.getElementById('cart-total-footer').innerText = formatPrice(total);
                footer.classList.remove('hidden');
            }
            // Re-init icon untuk elemen baru
            lucide.createIcons();
        }

        // Helper Buka/Tutup Drawer
        function openDrawer() {
            document.getElementById('drawer-overlay').classList.add('open');
            document.getElementById('cart-drawer').classList.add('open');
            document.body.style.overflow = 'hidden'; // Lock main scroll
        }
        function closeDrawer() {
            document.getElementById('drawer-overlay').classList.remove('open');
            document.getElementById('cart-drawer').classList.remove('open');
            document.body.style.overflow = 'auto'; // Unlock main scroll
        }

        // Util format uang
        function formatPrice(num) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        }

        /**
         * FUNGSI 4: CHECKOUT / KONFIRMASI PESANAN
         * Mengirim data keranjang ke backend API (process_order.php).
         */
        async function confirmOrder() {
            if (cart.length === 0) return;
            
            const tableId = 1; // ID Meja (Sementara Hardcoded, bisa diambil dari URL/Session)
            const totalBayar = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            closeDrawer();
            
            // Kunci layar agar user tidak klik sana sini saat proses
            document.body.style.overflow = 'hidden';

            // Mapping struktur data sesuai kebutuhan API
            const payloadItems = cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: item.quantity,
                spice: item.spice,
                notes: item.notes
            }));

            try {
                // REQUEST AJAX KE BACKEND
                const response = await fetch('process_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        items: payloadItems,
                        total_bayar: totalBayar,
                        meja_id: tableId
                    })
                });
                
                const result = await response.json();
                
                if(result.status === 'success') {
                    // JIKA SUKSES:
                    const orderId = result.order_id;
                    const orderDate = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}); // Waktu lokal browser saat ini

                    // 1. Tampilkan Animasi Memasak (Cooking Overlay)
                    const cooking = document.getElementById('cooking-overlay');
                    cooking.classList.add('active');

                    // 2. Render Struk Digital di Background
                    const receiptItems = document.getElementById('receipt-items');
                    receiptItems.innerHTML = ''; // Reset struk lama
                    
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

                    // 3. Delay 3 detik agar animasi memasak dinikmati, lalu tampilkan struk
                    setTimeout(() => {
                        cooking.classList.remove('active'); // Hapus animasi
                        
                        const receiptOverlay = document.getElementById('receipt-overlay');
                        receiptOverlay.classList.add('open'); // Munculkan struk
                        lucide.createIcons();
                        
                        // Kosongkan Keranjang Lokal
                        cart = [];
                        updateCartUI(); // Reset badge jadi 0
                    }, 3000);

                } else {
                    alert('Gagal membuat pesanan: ' + result.message);
                    document.body.style.overflow = 'auto'; // Buka kunci layar
                }

            } catch(err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi ke server!');
                document.body.style.overflow = 'auto';
            }
        }
    </script>
</body>
</html>
