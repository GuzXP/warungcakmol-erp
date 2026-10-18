<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: index.php (USER HOMEPAGE)
 * FITUR: 
 * 1. Menampilkan Profil Warung (Sejarah, Gallery, Lokasi)
 * 2. Menampilkan Menu Dinamis dari Database (Grouping by Category)
 * 3. Navigasi Cepat ke Halaman Order
 */

// 1. KONEKSI DATABASE
// Memanggil file helper koneksi untuk akses ke MySQL
require 'koneksi.php';
/** @var mysqli $koneksi */

// 2. DATA FETCHING & GROUPING
// Tujuan: Mengubah data mentah database (baris per baris) menjadi struktur array bertingkat
// berdasarkan Kategori, agar mudah ditampilkan per tab/section di frontend.

// Query: Ambil semua data menu diurutkan ID
$result = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY id_menu ASC");

$menuDataGrouped = []; // Wadah sementara (Associative Array)

while($row = mysqli_fetch_assoc($result)) {
    // A. Normalisasi Teks Kategori
    // Kadang data dari Admin/Excel mengandung hidden characters atau spasi ganda.
    // Kita bersihkan agar key array konsisten (misal: "Nasi Goreng" !== "Nasi Goreng ").
    $kategori = trim(html_entity_decode($row['kategori']));
    $kategori = str_replace("\xc2\xa0", " ", $kategori);
    
    // B. Formatting Harga
    // Ubah angka integer 15000 jadi "Rp 15.000"
    $harga_formatted = "Rp " . number_format($row['harga'], 0, ',', '.');
    
    // C. Siapkan Objek Item Menu (Clean Data)
    $menuItem = [
        "name" => $row['nama_menu'],
        "price" => $harga_formatted,
        "desc" => $row['deskripsi'],
        "badge" => $row['badge'],           // Label opsional (misal: "Best Seller")
        "badgeColor" => $row['badge_color'], // Warna label (badge-red/yellow)
        "image" => "../img/" . $row['gambar'] // Path gambar relatif
    ];
    
    // D. Logika Grouping (Algoritma Pemeriksaan Key)
    // Jika wadah kategori ini belum ada, buat wadah baru.
    if(!isset($menuDataGrouped[$kategori])) {
        $menuDataGrouped[$kategori] = [
            "name" => $kategori, // Nama Kategori (Judul Tab)
            "items" => []        // Array kosong untuk menampung item
        ];
    }
    // Masukkan item ke dalam kategori yang sesuai
    $menuDataGrouped[$kategori]['items'][] = $menuItem;
}

// E. Finalisasi Data
// Mengubah Associative Array menjadi Indexed Array biasa supaya mudah di-loop dengan index [0], [1] di HTML.
$menuData = array_values($menuDataGrouped);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- META TAGS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nasi Goreng Cak Mol Pulomas</title>
    
    <!-- CSS UTAMA -->
    <link rel="stylesheet" href="../css/style-user-index.css">
    
    <!-- ICON LIBRARY (Lucide) - Ringan & Modern -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-white">

    <!-- FLOATING NAV (Mobile Friendly) -->
    <!-- Tombol melayang di bawah layar untuk akses cepat Order -->
    <div class="floating-nav">
        <a href="#" class="floating-nav-btn active">Home</a>
        <a href="order.php" class="floating-nav-btn">Dine In</a>
    </div>

    <!-- MAIN NAVBAR (Glassmorphism Effect) -->
    <!-- Navbar menempel di atas (sticky) dengan efek blur -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white-glass backdrop-blur-sm border-b border-black-10">
        <div class="container py-4">
            <div class="flex items-center justify-between">
                <!-- Brand Identity -->
                <div class="flex items-center gap-3">
                    <i data-lucide="chef-hat" class="icon-lg"></i>
                    <div>
                        <h1 class="font-bold text-xl leading-none">Nasi Goreng Cak Mol</h1>
                        <p class="text-xs text-black-60">Pulomas - Sejak 1998</p>
                    </div>
                </div>

                <!-- Desktop Menu Links -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#menu" class="nav-link text-sm">Menu</a>
                    <a href="#about" class="nav-link text-sm">Tentang</a>
                    <a href="#location" class="nav-link text-sm">Lokasi</a>
                    <!-- CTA Button Header -->
                    <a href="order.php" class="btn btn-default btn-sm">
                        <i data-lucide="phone" class="icon-sm mr-2"></i>
                        Pesan Sekarang
                    </a>
                </div>

                <!-- Mobile Trigger (Hamburger Menu) -->
                <button class="md:hidden btn btn-ghost btn-icon" id="menu-trigger">
                    <i data-lucide="menu" class="icon-md"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- MOBILE DRAWER MENU -->
    <!-- Panel samping yang muncul saat tombol hamburger diklik -->
    <div id="mobile-sheet" class="sheet-overlay">
        <div class="sheet-content">
             <div class="flex justify-between items-center mb-6">
                <span class="font-bold text-lg">Menu</span>
                <button class="btn btn-ghost btn-icon" onclick="closeSheet()">
                    <i data-lucide="x" class="icon-md"></i>
                </button>
            </div>
            <!-- Vertical Navigation Links -->
            <div class="flex flex-col gap-6">
                <a href="#menu" class="text-lg font-medium nav-link" onclick="closeSheet()">Menu</a>
                <a href="#about" class="text-lg font-medium nav-link" onclick="closeSheet()">Tentang</a>
                <a href="#location" class="text-lg font-medium nav-link" onclick="closeSheet()">Lokasi</a>
                <div class="border-t"></div>
                 <a href="order.php" class="btn btn-default w-full">
                    <i data-lucide="phone" class="icon-sm mr-2"></i>
                    Pesan Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- SECTION 1: HERO (Tampilan Utama) -->
    <section class="pt-24 pb-16 px-4">
        <div class="container mx-auto">
            <div class="reveal text-center max-w-4xl mx-auto">
                <span class="badge badge-outline mb-4">Legendaris Sejak 1998</span>
                
                <!-- Headline dengan Animasi Reveal per Baris -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                    <span class="hero-title-line"><span class="hero-title-reveal">Nasi Goreng Cak Mol</span></span>
                    <span class="hero-title-line"><span class="hero-title-reveal text-black-60 delay-hero-1">Pulomas</span></span>
                </h1>
                
                <p class="text-lg md:text-xl text-black-70 mb-8 max-w-2xl mx-auto leading-relaxed">
                    Warung legendaris yang sudah menemani warga Pulomas sejak tahun 1998. 
                    Dari anak sekolah sampai berkeluarga, semua jadi langganan!
                </p>

                <!-- Statistik Toko -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <!-- Stat 1: Rating GoFood (Class 'stat-value' dipakai JS untuk animasi angka) -->
                    <div class="card p-4 reveal delay-100">
                        <div class="text-2xl font-bold mb-1 text-center stat-value" data-target="4.9">0</div>
                        <div class="text-xs text-black-60 text-center">Rating GoFood</div>
                        <div class="flex justify-center mt-1 gap-1">
                             <?php for($i=0;$i<5;$i++): ?><i data-lucide="star" class="icon-sm icon-star-yellow"></i><?php endfor; ?>
                        </div>
                    </div>
                    <!-- Stat 2: Rating Maps -->
                    <div class="card p-4 reveal delay-200">
                        <div class="text-2xl font-bold mb-1 text-center stat-value" data-target="4.6">0</div>
                        <div class="text-xs text-black-60 text-center">Rating Maps</div>
                        <div class="text-xs text-black-50 text-center">(45 reviews)</div>
                    </div>
                    <!-- Stat 3: Tahun Berdiri -->
                    <div class="card p-4 reveal delay-100">
                        <div class="text-2xl font-bold mb-1 text-center stat-value" data-target="27+">0</div>
                        <div class="text-xs text-black-60 text-center">Tahun Berdiri</div>
                    </div>
                    <!-- Stat 4: Harga Mulai -->
                    <div class="card p-4 reveal delay-200">
                        <div class="text-2xl font-bold mb-1 text-center stat-value" data-target="20K">0</div>
                        <div class="text-xs text-black-60 text-center">Harga Mulai</div>
                    </div>
                </div>

                <!-- Call To Action -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="order.php" class="btn btn-default btn-lg">
                        <i data-lucide="shopping-basket" class="icon-sm mr-2"></i>
                        Pesan Dine In
                    </a>
                    <a href="#menu" class="btn btn-outline btn-lg">
                        <i data-lucide="utensils-crossed" class="icon-sm mr-2"></i>
                        Lihat Menu
                    </a>
                </div>
                
                <!-- Link Eksternal Ojol -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-4">
                    <a href="https://gofood.link/u/rQLWn" target="_blank" class="btn btn-outline btn-sm opacity-70 hover:opacity-100">
                        <i data-lucide="external-link" class="icon-sm mr-2"></i>
                        GoFood
                    </a>
                    <a href="https://r.grab.com/g/6-20251213_211355_02ef38511f604c68b500ee0d57290923_MEXMPS-6-CZLBE3AYECM3LJ" target="_blank" class="btn btn-outline btn-sm opacity-70 hover:opacity-100">
                        <i data-lucide="external-link" class="icon-sm mr-2"></i>
                        GrabFood
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2: ABOUT (Sejarah) -->
    <section id="about" class="py-16 px-4 bg-black-5">
        <div class="container max-w-4xl mx-auto">
             <div class="reveal delay-100">
                <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center">Cerita Cak Mol</h2>
                <div class="text-black-80 text-lg leading-relaxed space-y-4">
                    <p>
                        Perjalanan dimulai dari kampung halaman Solo, Karanganyar. Cak Mol yang saat itu masih bujangan, 
                        mulai berjualan di sekolahan SLTA. Tahun 1995, beliau merantau ke Kalimalang, Jakarta, 
                        dan akhirnya menetap di Pulomas sejak tahun 1998.
                    </p>
                    <p>
                        Bertahun-tahun melayani warga Pulomas, banyak pelanggan setia yang sudah makan sejak masih 
                        anak SMA, kini sudah menikah dan punya anak. Nasi Goreng Cak Mol sudah jadi ciri khas 
                        tersendiri di komplek Pulomas.
                    </p>
                    <p class="font-medium">
                        Semua menu harga serba Rp 20.000, kecuali Capcay Rp 27.000. Porsi kenyang, rasa mantap!
                    </p>
                </div>
             </div>
        </div>
    </section>

    <!-- SECTION 3: MENU DINAMIS -->
    <!-- Menampilkan menu berdasarkan data dari PHP ($menuData) -->
    <section id="menu" class="py-16 px-4">
        <div class="container mx-auto">
            <div class="text-center mb-12 reveal delay-200">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Menu Kami</h2>
                <p class="text-black-70 max-w-2xl mx-auto leading-relaxed">
                    Semua menu dibuat dengan resep rahasia dan bahan berkualitas. 
                    Harga bersahabat, rasa juara!
                </p>
            </div>

            <!-- TAB NAVIGASI KATEGORI -->
            <!-- Looping nama kategori untuk membuat tombol tab -->
            <div class="flex flex-wrap justify-center gap-2 mb-8 reveal delay-300" id="menu-tabs">
                <?php foreach($menuData as $index => $category): ?>
                    <!-- Logika Ternary: Item pertama ($index 0) otomatis aktif ('btn-default') -->
                    <button class="btn <?php echo $index === 0 ? 'btn-default' : 'btn-outline'; ?> menu-tab text-xs md:text-sm" 
                            onclick="switchTab(<?php echo $index; ?>, this)">
                        <?php echo $category['name']; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- GRID CONTAINER ITEM -->
            <div class="relative max-w-5xl mx-auto">
                <?php foreach($menuData as $index => $category): ?>
                    <!-- Looping Kategori untuk membuat Container Grid per Kategori -->
                    <!-- Kategori selain pertama disembunyikan (class 'hidden') -->
                    <div class="menu-item-grid <?php echo $index === 0 ? '' : 'hidden'; ?>" id="grid-<?php echo $index; ?>">
                        <div class="grid md:grid-cols-2 gap-6">
                            
                            <!-- Looping Item di dalam Kategori -->
                            <?php foreach($category['items'] as $item): ?>
                                <div class="card hover:shadow-lg transition-shadow">
                                    <div class="card-menu-horizontal">
                                        <!-- Foto Menu -->
                                        <?php if(isset($item['image']) && $item['image']): ?>
                                            <div class="card-menu-img-container">
                                                <img src="<?php echo $item['image']; ?>" class="card-menu-img-horizontal" alt="<?php echo $item['name']; ?>">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Detail Text -->
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start mb-1">
                                                <h3 class="font-bold text-base md:text-lg leading-snug"><?php echo $item['name']; ?></h3>
                                                <div class="font-bold whitespace-nowrap ml-2 text-sm md:text-base"><?php echo $item['price']; ?></div>
                                            </div>
                                            
                                            <!-- Badge (Opsional, jika ada data badge) -->
                                            <?php if($item['badge']): ?>
                                                <span class="badge <?php echo $item['badgeColor']; ?> text-[10px] text-white py-0 px-2 mb-2">
                                                    <?php echo $item['badge']; ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <!-- Deskripsi Pendek -->
                                            <?php if($item['desc']): ?>
                                                <!-- line-clamp-2 membuat teks terpotong ... jika lebih dari 2 baris -->
                                                <p class="text-xs text-black-70 leading-normal line-clamp-2"><?php echo $item['desc']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECTION 4: GALERI (Bento Grid Style) -->
    <section class="py-16 px-4 bg-white">
        <div class="container mx-auto">
            <div class="text-center mb-10 reveal">
                <h2 class="text-3xl font-bold mb-4">Suasana Warung</h2>
                <p class="text-black-60">Nyaman buat makan di tempat, asik buat kumpul bareng.</p>
            </div>
            
            <!-- Grid Layout Bento -->
            <div class="bento-grid reveal">
                <div class="bento-item bento-big">
                    <img src="../img/tempat-luar.png" class="bento-img" alt="Tampilan Luar">
                </div>
                <div class="bento-item">
                    <img src="../img/tempat-dalam.png" class="bento-img" alt="Tampilan Dalam">
                </div>
                <div class="bento-item">
                    <img src="../img/tempat-menu.png" class="bento-img" alt="Menu Spanduk">
                </div>
                <div class="bento-item bento-tall">
                    <img src="../img/tempat-luar (2).png" class="bento-img" alt="Suasana Malam">
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: TESTIMONI PELANGGAN (Statis) -->
    <section class="py-16 px-4 bg-black-5">
        <div class="container mx-auto">
            <div class="text-center mb-12 reveal delay-300">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Kata Pelanggan Kami</h2>
                <p class="text-black-70 leading-loose">Testimoni asli dari pelanggan setia</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Testimoni 1 -->
                <div class="card h-full reveal">
                    <div class="p-6">
                         <div class="flex items-center gap-1 mb-3">
                             <?php for($i=0;$i<5;$i++): ?><i data-lucide="star" class="icon-sm icon-star-yellow"></i><?php endfor; ?>
                        </div>
                        <p class="text-sm text-black-80 mb-4 leading-relaxed">"Rasa nasi gorengnya enak banget & porsinya juga banyak.. kenyamg poll.. Harga bersahabat.."</p>
                        <div class="border-t pt-4">
                            <p class="font-semibold text-sm leading-tight">Deswati Gani</p>
                            <p class="text-xs text-black-60 leading-tight mt-1">13 Desember 2025</p>
                            <div class="badge badge-outline mt-2 text-xs">GoFood</div>
                        </div>
                    </div>
                </div>
                 
                 <!-- Testimoni 2 -->
                 <div class="card h-full reveal delay-100">
                    <div class="p-6">
                         <div class="flex items-center gap-1 mb-3">
                             <?php for($i=0;$i<5;$i++): ?><i data-lucide="star" class="icon-sm icon-star-yellow"></i><?php endfor; ?>
                        </div>
                        <p class="text-sm text-black-80 mb-4 leading-relaxed">"Favorit disini nasgor gilanya. Kebetulan saya gabisa makan pedes, jadi nasgor gilanya bisa pesen ga pedes."</p>
                        <div class="border-t pt-4">
                            <p class="font-semibold text-sm leading-tight">Jovan Jodiputra</p>
                            <p class="text-xs text-black-60 leading-tight mt-1">1 year ago</p>
                            <div class="badge badge-outline mt-2 text-xs">Google Maps</div>
                        </div>
                    </div>
                </div>
                
                 <!-- Testimoni 3 -->
                 <div class="card h-full reveal delay-200">
                    <div class="p-6">
                         <div class="flex items-center gap-1 mb-3">
                             <?php for($i=0;$i<5;$i++): ?><i data-lucide="star" class="icon-sm icon-star-yellow"></i><?php endfor; ?>
                        </div>
                        <p class="text-sm text-black-80 mb-4 leading-relaxed">"Nasi Gorengnya Enak. Sudah 2x pesan di GoFood. Tetap Pertahankan kualitasnya"</p>
                        <div class="border-t pt-4">
                            <p class="font-semibold text-sm leading-tight">Hendry Hartono</p>
                            <p class="text-xs text-black-60 leading-tight mt-1">1 year ago</p>
                            <div class="badge badge-outline mt-2 text-xs">Google Maps</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 6: LOKASI & KONTAK -->
    <section id="location" class="py-16 px-4">
        <div class="container max-w-5xl mx-auto">
            <div class="text-center mb-12 reveal delay-200">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Lokasi & Jam Buka</h2>
            </div>
            
             <div class="grid md:grid-cols-2 gap-8">
                <!-- Info Alamat & Map Widget -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
                            <i data-lucide="map-pin" class="icon-md"></i> Alamat
                        </h3>
                        <p class="text-black-80 mb-4 leading-relaxed">
                            Jl. Pulo Mas Barat V, No.4<br>
                            Daerah Khusus Ibukota Jakarta
                        </p>
                        <p class="text-sm text-black-60 mb-4 leading-relaxed">Samping Indomaret, depan SMP Don Bosco</p>
                        
                        <!-- Embed Google Maps -->
                        <div class="map-container">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.568411993245!2d106.88339597475017!3d-6.18846349380237!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f49352670e9f%3A0x6aab01a7f0521e1d!2sJl.%20Pulo%20Mas%20Barat%20V%20No.4%2C%20RT.4%2FRW.12%2C%20Kayu%20Putih%2C%20Kec.%20Pulo%20Gadung%2C%20Kota%20Jakarta%20Timur%2C%20Daerah%20Khusus%20Ibukota%20Jakarta%2013210!5e0!3m2!1sid!2sid!4v1715610000000!5m2!1sid!2sid" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>

                        <a href="https://maps.app.goo.gl/Jw8VgP5Vr1GuEsyS6" target="_blank" class="btn btn-outline btn-map">
                            <i data-lucide="map-pin" class="icon-sm"></i>
                            Buka di Google Maps
                        </a>
                    </div>
                </div>

                <!-- Info Jam Buka -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
                            <i data-lucide="clock" class="icon-md"></i> Jam Operasional
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between py-2 border-b"><span>Senin</span><span>06:30-14:00</span></div>
                            <div class="flex justify-between py-2 border-b"><span>Selasa</span><span>06:30-14:00</span></div>
                            <div class="flex justify-between py-2 border-b"><span>Rabu</span><span>06:30-14:00</span></div>
                            <div class="flex justify-between py-2 border-b"><span>Kamis</span><span>06:30-14:00</span></div>
                            <div class="flex justify-between py-2 border-b"><span>Jumat</span><span>06:30-14:00</span></div>
                            <div class="flex justify-between py-2 border-b"><span>Sabtu</span><span>06:30-14:00</span></div>
                             <div class="flex justify-between py-2 border-b text-black-40"><span>Minggu</span><span class="font-bold">TUTUP</span></div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </section>

    <!-- FOOTER Call-to-Action -->
    <section class="py-16 px-4 bg-black text-white">
        <div class="container text-center max-w-4xl mx-auto">
             <div class="reveal">
                 <!-- Class Utility text-white-force untuk memaksa warna -->
                <h2 class="text-3xl md:text-4xl font-bold mb-6 text-white text-white-force">Siap Memesan?</h2>
                <p class="text-white-80 mb-8 text-lg leading-relaxed">Hubungi kami untuk pemesanan atau pertanyaan</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <!-- Tombol Kontak -->
                    <a href="tel:081932314589" class="btn btn-lg bg-white text-black hover:opacity-90 transition-opacity btn-white-force">
                        <i data-lucide="phone" class="icon-sm mr-2"></i>
                        081932314589
                    </a>
                     <a href="https://wa.me/6281932314589" target="_blank" class="btn btn-lg bg-white text-black hover:opacity-90 transition-opacity btn-white-force">
                        <i data-lucide="message-circle" class="icon-sm mr-2"></i>
                        WhatsApp
                    </a>
                </div>
             </div>
        </div>
    </section>

    <!-- COPYRIGHT SECTION -->
    <footer class="py-8 px-4 border-t mt-8">
        <div class="container text-center mx-auto flex flex-col items-center">
             <div class="flex items-center justify-center gap-2 mb-4">
                <i data-lucide="chef-hat" class="icon-lg"></i>
                <span class="font-bold text-lg">Nasi Goreng Cak Mol Pulomas</span>
            </div>
            <p class="text-sm text-black-60 mb-4">Legendaris sejak 1998 • Pulomas, Jakarta</p>
             <div class="flex justify-center gap-4 text-sm mb-4">
                 <a href="https://gofood.link/u/rQLWn" target="_blank" class="hover:underline text-black">GoFood</a>
                 <span class="text-black-30">•</span>
                 <a href="https://r.grab.com/g/6-20251213_211355_02ef38511f604c68b500ee0d57290923_MEXMPS-6-CZLBE3AYECM3LJ" target="_blank" class="hover:underline text-black">GrabFood</a>
                 <span class="text-black-30">•</span>
                 <a href="https://maps.app.goo.gl/Jw8VgP5Vr1GuEsyS6" target="_blank" class="hover:underline text-black">Google Maps</a>
             </div>
             <p class="text-xs text-black-40 mt-4">© 2024 Nasi Goreng Cak Mol. All rights reserved.</p>
        </div>
    </footer>

    <!-- INTERACTIVITY JAVASCRIPT -->
    <script>
        // Init Lucide Icons (Mengubah tag <i data-lucide> jadi SVG)
        lucide.createIcons();

        // ----------------------------------------------------
        // FITUR 1: LOGIKA TAB MENU FILTERING
        // ----------------------------------------------------
        function switchTab(index, btnElem) {
            // Step 1: Update Tampilan Tabs Button
            document.querySelectorAll('.menu-tab').forEach(btn => {
                btn.classList.remove('btn-default'); // Nonaktifkan tombol lain
                btn.classList.add('btn-outline');
            });
            // Aktifkan tombol yang diklik
            btnElem.classList.remove('btn-outline');
            btnElem.classList.add('btn-default');
            
            // Step 2: Mengelola Transisi Grid Item
            const allGrids = document.querySelectorAll('.menu-item-grid');
            let currentActive = null;
            
            // Cari grid yang sedang aktif
            allGrids.forEach(grid => {
                if (!grid.classList.contains('hidden')) {
                    currentActive = grid;
                }
            });

            // Jika grid tujuan berbeda dengan grid sekarang, lakukan animasi swap
            if (currentActive && currentActive.id !== 'grid-' + index) {
                
                // A. Animasi KELUAR (Fade Out items lama)
                const currentCards = currentActive.querySelectorAll('.card');
                currentCards.forEach((card, i) => {
                    card.style.transitionDelay = `${i * 0.05}s`; // Stagger effect
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';
                });
                
                // Tunggu animasi keluar selesai (400ms), baru ganti konten
                setTimeout(() => {
                    // Sembunyikan Grid Lama
                    currentActive.classList.add('hidden');
                    currentActive.classList.remove('active-grid');
                    
                    // Tampilkan Grid Baru
                    const selectedGrid = document.getElementById('grid-' + index);
                    selectedGrid.classList.remove('hidden');
                    
                    // Reset posisi items baru (siap untuk animasi masuk)
                    const newCards = selectedGrid.querySelectorAll('.card');
                    newCards.forEach(card => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(10px)';
                        card.style.transition = 'none'; // Matikan transisi sesaat untuk reset
                    });
                    
                    selectedGrid.offsetHeight; // Force Reflow (Browser Paint trigger)
                    selectedGrid.classList.add('active-grid');
                    
                    // B. Animasi MASUK (Fade In items baru)
                    newCards.forEach((card, i) => {
                        card.style.transition = 'all 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
                        card.style.transitionDelay = `${i * 0.1}s`; // Stagger effect
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    });
                }, 400);
            }
        }

        // ----------------------------------------------------
        // FITUR 2: MOBILE DRAWER (Menu Kiri)
        // ----------------------------------------------------
        const sheet = document.getElementById('mobile-sheet');
        const trigger = document.getElementById('menu-trigger');
        
        // Tutup saat link diklik
        document.querySelectorAll('.sheet-content .nav-link').forEach(link => {
            link.addEventListener('click', closeSheet);
        });

        // Buka Sheet
        trigger.addEventListener('click', () => {
            sheet.classList.add('open');
            document.body.style.overflow = 'hidden'; // Kunci scroll halaman belakang
        });

        // Tutup Sheet
        function closeSheet() {
            sheet.classList.remove('open');
            document.body.style.overflow = ''; // Lepas kunci scroll
        }
        
        // Tutup saat klik di area gelap (overlay)
        sheet.addEventListener('click', (e) => {
            if (e.target === sheet) closeSheet();
        });

        // ----------------------------------------------------
        // FITUR 3: SCROLL REVEAL ANIMATION (Intersection Observer)
        // ----------------------------------------------------
        const revealCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { // Jika elemen masuk viewport
                    entry.target.classList.add('active'); // Trigger animasi CSS
                    
                    // Jika elemen berisi statistik angka, jalankan counter
                    const counters = entry.target.querySelectorAll('.stat-value');
                    counters.forEach(animateCounter);
                    
                    observer.unobserve(entry.target); // Stop observe agar tidak animasi ulang
                }
            });
        };

        // Helper: Animasi Angka Naik (0 -> 4.9)
        function animateCounter(el) {
            const targetStr = el.getAttribute('data-target');
            const isK = targetStr.includes('K');
            const isPlus = targetStr.includes('+');
            // Bersihkan string non-angka
            const target = parseFloat(targetStr.replace('K', '').replace('+', ''));
            
            const duration = 2000; // 2 detik
            const startTime = performance.now();

            const update = (now) => {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Funsi Easing agar animasi halus di akhir
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                
                const current = target * easeProgress;
                // Format decimal atau integer
                let display = (target % 1 === 0) ? Math.floor(current) : current.toFixed(1);
                
                if (isPlus) display += '+';
                if (isK) display += 'K';
                
                el.innerText = display;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    el.innerText = targetStr; // Pastikan angka akhir tepat
                }
            };
            requestAnimationFrame(update);
        }

        // Jalankan Observer
        const revealObserver = new IntersectionObserver(revealCallback, {
            threshold: 0.15 // Trigger saat 15% elemen terlihat
        });

        document.querySelectorAll('.reveal').forEach(el => {
            revealObserver.observe(el);
        });
    </script>
</body>
</html>
