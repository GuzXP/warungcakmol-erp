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
// 2. PROTEKSI AKSES KASIR (LOGIN REQUIRED)
confirm_kasir_login();

// --------------------------------------------------------------------------
// 1. BACKEND LOGIC: UPDATE STATUS & METODE BAYAR SECURE
// --------------------------------------------------------------------------
if (isset($_POST['selesaikan_order'])) {
    // Validasi CSRF Token
    if (!isset($_POST["csrf_token"]) || !check_csrf_token($_POST["csrf_token"])) {
        die("CSRF Token validation failed.");
    }
    
    $id_pesanan = (int)$_POST['id_pesanan'];
    $metode_bayar = isset($_POST['metode_bayar']) ? sanitize($_POST['metode_bayar']) : 'Cash';
    if (!in_array($metode_bayar, ['Cash', 'QRIS'])) {
        $metode_bayar = 'Cash';
    }
    
    // Update status pesanan, metode bayar, dan waktu selesai (NOW())
    $stmt = mysqli_prepare($koneksi, "UPDATE pesanan SET status_pesanan = 'Selesai', metode_bayar = ?, waktu_selesai = NOW() WHERE id_pesanan = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $metode_bayar, $id_pesanan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Dapatkan id_meja dari pesanan untuk mengosongkan status meja
    $stmt_info = mysqli_prepare($koneksi, "SELECT id_meja FROM pesanan WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_info, "i", $id_pesanan);
    mysqli_stmt_execute($stmt_info);
    $res_info = mysqli_stmt_get_result($stmt_info);
    $order_info = mysqli_fetch_assoc($res_info);
    mysqli_stmt_close($stmt_info);
    
    if ($order_info && $order_info['id_meja'] > 0) {
        // Update status meja kembali menjadi 'Kosong'
        $stmt_meja = mysqli_prepare($koneksi, "UPDATE meja SET status_meja = 'Kosong' WHERE id_meja = ?");
        mysqli_stmt_bind_param($stmt_meja, "i", $order_info['id_meja']);
        mysqli_stmt_execute($stmt_meja);
        mysqli_stmt_close($stmt_meja);
    }
    
    // Redirect ke halaman cetak struk agar langsung print
    header("Location: cetak_struk.php?id=" . $id_pesanan);
    exit;
}

// --------------------------------------------------------------------------
// 2. BACKEND LOGIC: FETCH DATA ANTRIAN (LEFT JOIN UNTUK TAKEAWAY)
// --------------------------------------------------------------------------
$query_pending = "SELECT p.*, m.nama_meja 
                  FROM pesanan p 
                  LEFT JOIN meja m ON p.id_meja = m.id_meja 
                  WHERE p.status_pesanan = 'Pending' 
                  ORDER BY p.waktu_pesan DESC";

$orders = query($query_pending);

// Hitung hash status dapur awal untuk dibandingkan saat polling (disamakan dengan poll_orders.php)
$res_hash_init = mysqli_query($koneksi, "SELECT GROUP_CONCAT(CONCAT(id_pesanan, '-', status_dapur) ORDER BY id_pesanan ASC) AS status_string FROM pesanan WHERE status_pesanan = 'Pending'");
$row_hash_init = mysqli_fetch_assoc($res_hash_init);
$initial_status_hash = md5($row_hash_init['status_string'] ?? '');
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
            <!-- STATUS INDICATOR POLLING -->
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
        @keyframes pulse-green {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            50% { transform: scale(1.03); box-shadow: 0 0 8px 2px rgba(34, 197, 94, 0.2); }
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
                <?php 
                $status_dapur = $order['status_dapur'] ?? 'Menunggu';
                $is_ready = ($status_dapur === 'Siap');
                // Beri border khusus hijau jika pesanan sudah siap saji dari dapur
                $card_border_style = $is_ready ? 'border: 2px solid #22c55e; animation: pulse-green 2s infinite;' : '';
                ?>
                <div class="order-card" id="card-<?php echo $order['id_pesanan']; ?>" style="<?= $card_border_style; ?>">
                    <!-- Header Card: Meja & Waktu & Tipe Pesanan -->
                    <div class="card-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
                        <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                            <span class="table-badge"><?php echo $order['nama_meja'] ? strtoupper($order['nama_meja']) : 'TAKEAWAY'; ?></span>
                            <span class="time-badge"><?php echo date('H:i', strtotime($order['waktu_pesan'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; width: 100%; align-items: center; flex-wrap: wrap; gap: 4px;">
                            <?php if (!empty($order['nama_pelanggan'])): ?>
                                <span class="cust-name-badge" style="font-size: 0.8rem; font-weight: 600; color: #4b5563;">
                                    Cust: <?php echo sanitize($order['nama_pelanggan']); ?>
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; font-weight: 600; color: #9ca3af; font-style: italic;">Dine In</span>
                            <?php endif; ?>
                            
                            <?php 
                            $status_class = '';
                            if ($status_dapur === 'Menunggu') $status_class = 'background: #fef3c7; color: #d97706; border: 1px solid #fde68a;';
                            elseif ($status_dapur === 'Dimasak') $status_class = 'background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;';
                            elseif ($status_dapur === 'Siap') $status_class = 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-weight: bold;';
                            ?>
                            <span class="status-dapur-badge" style="font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; <?= $status_class; ?>">
                                Dapur: <?= $status_dapur; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Body Card: Daftar Menu -->
                    <div class="card-body">
                        <ul class="item-list">
                            <?php 
                            // NESTED QUERY SECURE: Ambil detail item untuk pesanan ini dengan prepared statement
                            $id_ord = $order['id_pesanan'];
                            $items = query("SELECT * FROM detail_pesanan WHERE id_pesanan = ?", [$id_ord], "i");
                            
                            foreach($items as $item):
                            ?>
                                <li class="item-row">
                                     <div class="item-name">
                                         <!-- Jumlah Item -->
                                         <span class="item-qty"><?php echo $item['jumlah']; ?>x</span>
                                         <!-- Nama Menu (Snapshot DB) dengan sanitasi XSS -->
                                         <?php echo sanitize($item['nama_menu_snapshot']); ?>
                                         
                                         <!-- Detail Tambahan (Level Pedas / Catatan) -->
                                         <div class="item-detail-meta" style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">
                                             <?php if (isset($item['level_pedas']) && $item['level_pedas'] !== 'Tidak Pedas'): ?>
                                                 <span class="badge-pedas" style="background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-right: 4px; display: inline-block;">
                                                     <?php echo sanitize($item['level_pedas']); ?>
                                                 </span>
                                             <?php endif; ?>
                                             <?php if (!empty($item['catatan'])): ?>
                                                 <span class="item-note" style="font-style: italic;">
                                                     • "<?php echo sanitize($item['catatan']); ?>"
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
                        
                        <!-- Tombol Bayar & Selesaikan -->
                        <button type="button" class="btn-done" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onclick="openPaymentModal(<?php echo $order['id_pesanan']; ?>, '<?php echo $order['nama_meja'] ? $order['nama_meja'] : 'Takeaway'; ?>', <?php echo $order['total_bayar']; ?>)">
                            <i data-lucide="credit-card"></i> Proses Pembayaran
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- PAYMENT MODAL (Premium Design, Dark Accent Blur) -->
    <div id="payment-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.025em;" id="modal-meja-title-wrapper">Pembayaran - <span id="modal-meja-title">-</span></h3>
                <button type="button" onclick="closePaymentModal()" style="background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s;">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            
            <form action="" method="post" id="payment-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="id_pesanan" id="modal-id-pesanan" value="">
                <input type="hidden" name="selesaikan_order" value="1">
                
                <!-- Total Section -->
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: center;">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; letter-spacing: 0.05em; uppercase;">TOTAL TAGIHAN</span>
                    <h2 style="font-size: 2.25rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; letter-spacing: -0.025em;">Rp <span id="modal-total-text">0</span></h2>
                </div>
                
                <!-- Payment Method Toggle -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.875rem; font-weight: 700; display: block; margin-bottom: 0.5rem; color: #334155;">Pilih Metode Pembayaran</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label class="pay-method-btn" style="border: 2px solid #cbd5e1; border-radius: 10px; padding: 0.85rem; text-align: center; cursor: pointer; display: block; font-weight: 700; font-size: 0.95rem; transition: all 0.2s;">
                            <input type="radio" name="metode_bayar" value="Cash" checked onclick="selectPaymentMethod('Cash')" style="display: none;">
                            Cash / Tunai
                        </label>
                        <label class="pay-method-btn" style="border: 2px solid #cbd5e1; border-radius: 10px; padding: 0.85rem; text-align: center; cursor: pointer; display: block; font-weight: 700; font-size: 0.95rem; transition: all 0.2s;">
                            <input type="radio" name="metode_bayar" value="QRIS" onclick="selectPaymentMethod('QRIS')" style="display: none;">
                            QRIS Nontunai
                        </label>
                    </div>
                </div>
                
                <!-- Tunai Area -->
                <div id="cash-payment-area" style="margin-bottom: 1.75rem;">
                    <label for="cash-amount" style="font-size: 0.875rem; font-weight: 700; display: block; margin-bottom: 0.5rem; color: #334155;">Uang Diterima (Rp)</label>
                    <input type="number" id="cash-amount" name="uang_diterima" style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 1.25rem; font-weight: 800; color: #0f172a; outline: none; margin-bottom: 0.75rem; transition: border-color 0.2s;" placeholder="Nominal pembayaran..." required>
                    
                    <!-- Presets -->
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                        <button type="button" onclick="setCashPreset('pas')" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; color: #475569; cursor: pointer; transition: all 0.2s;">Uang Pas</button>
                        <button type="button" onclick="setCashPreset(20000)" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; color: #475569; cursor: pointer; transition: all 0.2s;">20k</button>
                        <button type="button" onclick="setCashPreset(50000)" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; color: #475569; cursor: pointer; transition: all 0.2s;">50k</button>
                        <button type="button" onclick="setCashPreset(100000)" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; color: #475569; cursor: pointer; transition: all 0.2s;">100k</button>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 1.125rem; font-weight: 800; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px dashed #f1f5f9;">
                        <span>KEMBALIAN:</span>
                        <span id="cash-change" style="color: #ef4444;">Rp 0</span>
                    </div>
                </div>
                
                <!-- QRIS Area -->
                <div id="qris-payment-area" style="display: none; text-align: center; margin-bottom: 1.75rem;">
                    <div style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 1rem; border-radius: 12px; display: inline-block;">
                        <img src="../assets/qris.jpg" alt="QRIS Code" style="width: 190px; height: 190px; object-fit: contain; display: block; border-radius: 8px;">
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Scan QRIS Untuk Pembayaran</p>
                </div>
                
                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" class="btn-done" style="background: #f1f5f9; color: #475569; width: 35%;" onclick="closePaymentModal()">
                        Batal
                    </button>
                    <button type="submit" id="confirm-payment-btn" class="btn-done" style="width: 65%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i data-lucide="check-check"></i> Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AUDIO ALERTS -->
    <audio id="notif-sound" src="../assets/notification.mp3" preload="auto"></audio>

    <!-- POLLING SYSTEM - AUTO REFRESH PESANAN BARU -->
    <script>
        lucide.createIcons();

        // --- AUDIO NOTIFICATION SYSTEM ---
        function playSuccessBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
            } catch (e) {}
        }

        function playNotification() {
            const sound = document.getElementById('notif-sound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(e => {
                    playSuccessBeep();
                });
            } else {
                playSuccessBeep();
            }
        }

        // Cek apakah reload dipicu oleh adanya pesanan/update baru
        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('play_new_order_sound') === 'true') {
                sessionStorage.removeItem('play_new_order_sound');
                // Play notification sound
                setTimeout(playNotification, 500);
            }
        });

        // --- POLLING CONFIGURATION ---
        const POLL_INTERVAL_MS = 8000; 
        let lastKnownCount = <?php echo count($orders); ?>; 
        let lastKnownHash = '<?php echo $initial_status_hash; ?>'; 
        let pollTimer = null;
        let isPolling = false;

        // --- UI ELEMENTS ---
        const statusDot  = document.getElementById('poll-status-dot');
        const statusText = document.getElementById('poll-status-text');

        async function pollOrders() {
            if (isPolling) return; 
            isPolling = true;

            try {
                const response = await fetch('poll_orders.php', {
                    method: 'GET',
                    headers: { 'Cache-Control': 'no-cache' }
                });

                if (!response.ok) throw new Error('Server error ' + response.status);

                const data = await response.json();
                const currentCount = data.pending_count;
                const currentHash = data.status_hash;

                // Jangan refresh jika modal pembayaran sedang terbuka (agar tidak mengganggu kasir saat input tunai)
                const isModalOpen = document.getElementById('payment-modal').style.display === 'flex';

                if ((currentCount !== lastKnownCount || currentHash !== lastKnownHash) && !isModalOpen) {
                    console.log(`[Polling] Perubahan terdeteksi. Reloading...`);
                    sessionStorage.setItem('play_new_order_sound', 'true');
                    location.reload();
                } else {
                    setStatusOnline();
                }

            } catch (err) {
                console.warn('[Polling] Gagal koneksi ke server:', err.message);
                setStatusOffline();
            } finally {
                isPolling = false;
            }
        }

        function setStatusOnline() {
            if (statusDot)  statusDot.style.background  = '#22c55e';
            if (statusText) statusText.textContent = 'Monitor Aktif';
        }

        function setStatusOffline() {
            if (statusDot)  statusDot.style.background  = '#f59e0b';
            if (statusText) statusText.textContent = 'Mencoba Ulang...';
        }

        pollOrders();
        pollTimer = setInterval(pollOrders, POLL_INTERVAL_MS);

        // --- PAYMENT MODAL LOGIC ---
        let currentTotal = 0;

        function openPaymentModal(orderId, tableName, total) {
            currentTotal = total;
            document.getElementById('modal-id-pesanan').value = orderId;
            document.getElementById('modal-meja-title').innerText = tableName;
            document.getElementById('modal-total-text').innerText = formatPriceOnly(total);
            document.getElementById('cash-amount').value = '';
            document.getElementById('cash-change').innerText = 'Rp 0';
            document.getElementById('cash-change').style.color = '#ef4444';
            
            selectPaymentMethod('Cash');
            
            const modal = document.getElementById('payment-modal');
            modal.style.display = 'flex';
            
            // Focus on cash input field immediately
            setTimeout(() => {
                document.getElementById('cash-amount').focus();
            }, 100);
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').style.display = 'none';
        }

        function selectPaymentMethod(method) {
            const btns = document.querySelectorAll('.pay-method-btn');
            btns.forEach(btn => {
                const input = btn.querySelector('input');
                if (input.value === method) {
                    input.checked = true;
                    btn.style.borderColor = '#22c55e';
                    btn.style.backgroundColor = '#f0fdf4';
                    btn.style.color = '#16a34a';
                } else {
                    btn.style.borderColor = '#cbd5e1';
                    btn.style.backgroundColor = '#ffffff';
                    btn.style.color = '#1e293b';
                }
            });

            if (method === 'Cash') {
                document.getElementById('cash-payment-area').style.display = 'block';
                document.getElementById('qris-payment-area').style.display = 'none';
                document.getElementById('cash-amount').setAttribute('required', 'required');
            } else {
                document.getElementById('cash-payment-area').style.display = 'none';
                document.getElementById('qris-payment-area').style.display = 'block';
                document.getElementById('cash-amount').removeAttribute('required');
            }
        }

        function formatPriceOnly(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            }).format(num);
        }

        function setCashPreset(val) {
            const input = document.getElementById('cash-amount');
            if (val === 'pas') {
                input.value = currentTotal;
            } else {
                input.value = val;
            }
            calculateChange();
        }

        function calculateChange() {
            const amountInput = document.getElementById('cash-amount');
            const changeSpan = document.getElementById('cash-change');
            const entered = parseInt(amountInput.value) || 0;
            
            const change = entered - currentTotal;
            if (change < 0) {
                changeSpan.innerText = 'Kurang Rp ' + formatPriceOnly(Math.abs(change));
                changeSpan.style.color = '#ef4444';
            } else {
                changeSpan.innerText = 'Rp ' + formatPriceOnly(change);
                changeSpan.style.color = '#22c55e';
            }
        }

        document.getElementById('cash-amount').addEventListener('input', calculateChange);

        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const method = document.querySelector('input[name="metode_bayar"]:checked').value;
            if (method === 'Cash') {
                const entered = parseInt(document.getElementById('cash-amount').value) || 0;
                if (entered < currentTotal) {
                    e.preventDefault();
                    alert('Uang tunai yang diterima kurang dari total tagihan!');
                }
            }
        });
    </script>
</body>
</html>
