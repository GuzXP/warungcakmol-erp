<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: kasir.php (DASHBOARD KASIR)
 * FITUR UTAMA: 
 * 1. Monitoring Pesanan Masuk (Realtime via Reload / FCM)
 * 2. Menandai Pesanan Selesai (Update Database)
 * 3. Menerima Notifikasi Suara/Pop-up dari Firebase
 */

require 'fungsi.php';
require_once 'koneksi.php';
/** @var mysqli $koneksi */

// --------------------------------------------------------------------------
// 1. BACKEND LOGIC: UPDATE STATUS
// --------------------------------------------------------------------------
// Jika tombol "Tandai Selesai" diklik, ubah status pesanan di database.
if (isset($_POST['selesaikan_order'])) {
    $id_pesanan = (int)$_POST['id_pesanan'];
    
    // Update data: Ubah 'Pending' menjadi 'Selesai'
    // Pesanan yang sudah selesai tidak akan muncul lagi di daftar antrian.
    mysqli_query($koneksi, "UPDATE pesanan SET status_pesanan = 'Selesai' WHERE id_pesanan = $id_pesanan");
    
    // Refresh halaman agar list terupdate (Metode Post-Redirect-Get)
    header("Location: kasir.php");
    exit;
}

// --------------------------------------------------------------------------
// 2. BACKEND LOGIC: FETCH DATA ANTRIAN
// --------------------------------------------------------------------------
// Mengambil semua pesanan yang belum diselesaikan kasir.
// Menggunakan JOIN TABLE untuk mendapatkan nama meja dari tabel 'meja'.
$query_pending = "SELECT p.*, m.nama_meja 
                  FROM pesanan p 
                  JOIN meja m ON p.id_meja = m.id_meja 
                  WHERE p.status_pesanan = 'Pending' 
                  ORDER BY p.waktu_pesan DESC"; // Yang terbaru paling atas

$orders = query($query_pending);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Monitor | Cak Mol POS</title>
    <!-- CUSTOM STYLE -->
    <link rel="stylesheet" href="../css/style-user-kasir.css">
    <!-- ICON LIBRARY -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <!-- HEADER DASHBOARD -->
    <header class="kasir-header">
        <div class="container header-content">
            <div class="title-group">
                <h1>KASIR MONITOR</h1>
                <p>Nasi Goreng Cak Mol</p>
            </div>
            <!-- STATUS INDICATOR POLLING (menggantikan tombol Firebase) -->
            <div class="btn-notif" style="cursor:default; display:flex; align-items:center; gap:0.5rem;">
                <span id="poll-status-dot" style="
                    display: inline-block;
                    width: 10px; height: 10px;
                    border-radius: 50%;
                    background: #22c55e;
                    box-shadow: 0 0 0 3px rgba(34,197,94,0.25);
                    animation: pulse-dot 2s infinite;
                "></span>
                <span id="poll-status-text">Menghubungkan...</span>
            </div>
        </div>
    </header>
    <style>
        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 0 3px rgba(34,197,94,0.25); }
            50%       { box-shadow: 0 0 0 6px rgba(34,197,94,0.10); }
        }
    </style>

    <main class="container">
        
        <!-- STATISTIK RINGKAS -->
        <div class="stat-box">
            <span class="stat-label">Pesanan Pending</span>
            <div class="stat-value"><?php echo count($orders); ?></div>
        </div>

        <h2 class="section-title">Antrian Pesanan</h2>

        <div class="orders-grid">
            <!-- LOGIC EMPTY STATE: Jika tidak ada pesanan pending -->
            <?php if (empty($orders)) : ?>
                <div class="empty-state" style="display: block;">
                    <p class="text-title-empty">Belum ada pesanan baru</p>
                    <p class="text-subtitle-empty">Monitor sedang dalam mode siaga menunggu orderan masuk.</p>
                </div>
            <?php endif; ?>

            <!-- LOOPING ORDER CARD -->
            <?php foreach ($orders as $order) : ?>
                <div class="order-card" id="card-<?php echo $order['id_pesanan']; ?>">
                    <!-- Header Card: Meja & Waktu -->
                    <div class="card-header">
                        <span class="table-badge"><?php echo strtoupper($order['nama_meja']); ?></span>
                        <span class="time-badge"><?php echo date('H:i', strtotime($order['waktu_pesan'])); ?></span>
                    </div>

                    <!-- Body Card: Daftar Menu -->
                    <div class="card-body">
                        <ul class="item-list">
                            <?php 
                            // NESTED QUERY: Ambil detail item untuk pesanan ini
                            $id_ord = $order['id_pesanan'];
                            $items = query("SELECT * FROM detail_pesanan WHERE id_pesanan = $id_ord");
                            
                            foreach($items as $item):
                            ?>
                                <li class="item-row">
                                    <div class="item-name">
                                        <!-- Jumlah Item -->
                                        <span class="item-qty"><?php echo $item['jumlah']; ?>x</span>
                                        <!-- Nama Menu (Snapshot DB) -->
                                        <?php echo $item['nama_menu_snapshot']; ?>
                                        
                                        <!-- Detail Tambahan (Level Pedas / Catatan) -->
                                        <div class="item-detail-meta" style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">
                                            <?php if (isset($item['level_pedas']) && $item['level_pedas'] !== 'Tidak Pedas'): ?>
                                                <span class="badge-pedas" style="background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-right: 4px; display: inline-block;">
                                                    <?php echo $item['level_pedas']; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['catatan'])): ?>
                                                <span class="item-note" style="font-style: italic;">
                                                    • "<?php echo htmlspecialchars($item['catatan']); ?>"
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Harga Subtotal per Item -->
                                    <div class="item-price-text">
                                        Rp <?php echo number_format($item['harga_snapshot'] * $item['jumlah'], 0, ',', '.'); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Footer Card: Total & Aksi -->
                    <div class="card-footer">
                        <div class="total-row">
                            <span class="total-label">Total Pembayaran</span>
                            <span class="total-value">Rp <?php echo number_format($order['total_bayar'], 0, ',', '.'); ?></span>
                        </div>
                        
                        <!-- Form Button Selesai -->
                        <form action="" method="post" onsubmit="return confirm('Pesanan SUDAH DIBAYAR dan SELESAI?')" class="w-full">
                            <input type="hidden" name="id_pesanan" value="<?php echo $order['id_pesanan']; ?>">
                            <button type="submit" name="selesaikan_order" class="btn-done">
                                <i data-lucide="check"></i> Tandai Selesai
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- POLLING SYSTEM - AUTO REFRESH PESANAN BARU -->
    <!-- Menggantikan Firebase FCM. Setiap 8 detik cek jumlah pesanan pending di server. -->
    <!-- Jika ada pesanan baru masuk, halaman otomatis reload untuk memperbarui antrian. -->
    <script>
        // Initialize Icons Lucide
        lucide.createIcons();

        // --- POLLING CONFIGURATION ---
        const POLL_INTERVAL_MS = 8000; // Cek setiap 8 detik
        let lastKnownCount = <?php echo count($orders); ?>; // Jumlah pending saat halaman dimuat
        let pollTimer = null;
        let isPolling = false;

        // --- UI ELEMENTS ---
        const statusDot  = document.getElementById('poll-status-dot');
        const statusText = document.getElementById('poll-status-text');

        /**
         * FUNGSI POLLING UTAMA
         * Mengirim request GET ringan ke poll_orders.php
         * Jika jumlah pending berubah (naik/turun) → reload halaman.
         */
        async function pollOrders() {
            if (isPolling) return; // Hindari request bertumpuk
            isPolling = true;

            try {
                const response = await fetch('poll_orders.php', {
                    method: 'GET',
                    headers: { 'Cache-Control': 'no-cache' }
                });

                if (!response.ok) throw new Error('Server error ' + response.status);

                const data = await response.json();
                const currentCount = data.pending_count;

                if (currentCount !== lastKnownCount) {
                    // Ada perubahan antrian — pesanan baru masuk atau ada yang diselesaikan
                    console.log(`[Polling] Perubahan antrian: ${lastKnownCount} → ${currentCount}. Reloading...`);
                    location.reload();
                } else {
                    // Tidak ada perubahan — update status indicator saja
                    setStatusOnline();
                }

            } catch (err) {
                console.warn('[Polling] Gagal koneksi ke server:', err.message);
                setStatusOffline();
            } finally {
                isPolling = false;
            }
        }

        /** Set indikator status: hijau = online & monitoring */
        function setStatusOnline() {
            if (statusDot)  statusDot.style.background  = '#22c55e';
            if (statusText) statusText.textContent = 'Monitor Aktif';
        }

        /** Set indikator status: kuning = koneksi bermasalah */
        function setStatusOffline() {
            if (statusDot)  statusDot.style.background  = '#f59e0b';
            if (statusText) statusText.textContent = 'Mencoba Ulang...';
        }

        // Mulai polling segera setelah halaman dimuat, lalu ulangi tiap POLL_INTERVAL_MS
        pollOrders();
        pollTimer = setInterval(pollOrders, POLL_INTERVAL_MS);

        console.log(`[Polling] Sistem monitoring aktif. Interval: ${POLL_INTERVAL_MS / 1000} detik.`);
    </script>
</body>
</html>
