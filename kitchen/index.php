<?php
/**
 * FILE: kitchen/index.php (KITCHEN DISPLAY SYSTEM)
 * UNTUK: Memantau pesanan masuk secara real-time di dapur dengan notifikasi suara.
 * AKSES: Langsung tanpa login sesuai keputusan design user.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) | Cak Mol</title>
    <!-- Tailwind CSS (via CDN) jika dibutuhkan untuk struktur cepat, namun kita pakai CSS Vanilla Premium Custom -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #f59e0b;      /* Amber / Orange */
            --info: #0ea5e9;         /* Sky Blue */
            --success: #22c55e;      /* Emerald Green */
            --danger: #ef4444;       /* Red */
            --font-family: 'Outfit', sans-serif;
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* HEADER */
        .kds-header {
            background-color: #0b0f19;
            padding: 1rem 2rem;
            border-b: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
        }

        .kds-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .kds-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #f59e0b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .kds-stats {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .stat-badge {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-count {
            color: var(--primary);
            font-size: 1rem;
            font-weight: 800;
        }

        .sound-toggle {
            cursor: pointer;
            background: none;
            border: none;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 50%;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .sound-toggle:hover {
            background-color: var(--border-color);
        }

        /* BOARD LAYOUT */
        .kds-board {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: calc(100vh - 73px);
            overflow: hidden;
        }

        .board-column {
            display: flex;
            flex-direction: column;
            height: 100%;
            border-right: 1px solid var(--border-color);
        }

        .board-column:last-child {
            border-right: none;
        }

        .column-header {
            padding: 1.25rem 2rem;
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid var(--border-color);
            background-color: #0d1321;
        }

        .column-header.waiting {
            border-bottom-color: var(--primary);
        }

        .column-header.cooking {
            border-bottom-color: var(--info);
        }

        .column-count {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
        }

        .column-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
            align-content: start;
        }

        /* KDS CARDS */
        .order-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, border-color 0.2s;
            position: relative;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .order-card.waiting-card {
            border-left: 6px solid var(--primary);
        }

        .order-card.cooking-card {
            border-left: 6px solid var(--info);
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 0.75rem;
        }

        .meja-label {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .cust-name {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.125rem;
            font-weight: 500;
        }

        .time-badge {
            font-size: 0.75rem;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .card-items {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .item-row {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .item-qty {
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--primary);
            background-color: rgba(245, 158, 11, 0.1);
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            line-height: 1.2;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .item-meta {
            margin-top: 0.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .meta-spice {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.35rem;
            border-radius: 4px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .meta-note {
            background-color: rgba(234, 179, 8, 0.15);
            color: #facc15;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.35rem;
            border-radius: 4px;
            border: 1px solid rgba(234, 179, 8, 0.2);
        }

        .card-action {
            margin-top: auto;
        }

        .btn-kds {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-waiting {
            background-color: var(--primary);
            color: #000;
        }

        .btn-waiting:hover {
            background-color: #d97706;
        }

        .btn-cooking {
            background-color: var(--info);
            color: #fff;
        }

        .btn-cooking:hover {
            background-color: #0284c7;
        }

        /* AUDIO BANNER */
        .audio-banner {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(to right, #ea580c, #d97706);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 100;
            cursor: pointer;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translate(-50%, 0); }
            50% { transform: translate(-50%, -10px); }
        }

        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- AUDIO PLAYER -->
    <audio id="notif-sound" src="../assets/notification.mp3" preload="auto"></audio>

    <!-- AUTOPLAY NOTIFICATION ACTIVATOR BANNER -->
    <div id="audio-activator" class="audio-banner hidden" onclick="enableAudio()">
        <i data-lucide="volume-2"></i>
        <span>Klik untuk Mengaktifkan Notifikasi Suara Pesanan Baru</span>
    </div>

    <!-- HEADER -->
    <header class="kds-header">
        <div class="kds-brand">
            <i data-lucide="chef-hat" style="color: var(--primary); width: 2rem; height: 2rem;"></i>
            <h1 class="kds-title">KITCHEN DISPLAY SYSTEM</h1>
        </div>
        
        <div class="kds-stats">
            <div class="stat-badge">
                <span>Total Aktif:</span>
                <span id="total-stat" class="stat-count">0</span>
            </div>
            
            <button id="sound-btn" class="sound-toggle" onclick="toggleMute()" title="Mute/Unmute Suara">
                <i data-lucide="volume-2" id="sound-icon"></i>
            </button>
        </div>
    </header>

    <!-- BOARD -->
    <main class="kds-board">
        <!-- COLUMN 1: MENUNGGU -->
        <section class="board-column">
            <div class="column-header waiting">
                <span>MENUNGGU (QUEUE)</span>
                <span id="waiting-count" class="column-count">0</span>
            </div>
            <div class="column-content" id="column-waiting">
                <!-- Cards will be populated here -->
            </div>
        </section>

        <!-- COLUMN 2: DIMASAK -->
        <section class="board-column">
            <div class="column-header cooking">
                <span>SEDANG DIMASAK</span>
                <span id="cooking-count" class="column-count">0</span>
            </div>
            <div class="column-content" id="column-cooking">
                <!-- Cards will be populated here -->
            </div>
        </section>
    </main>

    <script>
        lucide.createIcons();

        let ordersState = [];
        let isMuted = false;
        let isAudioEnabled = false;
        let waitingCountPrev = 0;

        // Cek autoplay permission
        document.addEventListener('DOMContentLoaded', () => {
            const notifSound = document.getElementById('notif-sound');
            
            // Tes play suara
            notifSound.play().then(() => {
                isAudioEnabled = true;
            }).catch(err => {
                // Autoplay dicegah oleh browser, munculkan banner activator
                document.getElementById('audio-activator').classList.remove('hidden');
            });
            
            // Start Polling
            pollKitchen();
            setInterval(pollKitchen, 4000);
        });

        function enableAudio() {
            const notifSound = document.getElementById('notif-sound');
            notifSound.play().then(() => {
                isAudioEnabled = true;
                document.getElementById('audio-activator').classList.add('hidden');
                // Play a brief success tone
                playSuccessBeep();
            });
        }

        function toggleMute() {
            isMuted = !isMuted;
            const icon = document.getElementById('sound-icon');
            icon.setAttribute('data-lucide', isMuted ? 'volume-x' : 'volume-2');
            lucide.createIcons();
        }

        // Web Audio API beep generator as fallback
        function playSuccessBeep() {
            if (isMuted) return;
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
            if (isMuted) return;
            
            const sound = document.getElementById('notif-sound');
            sound.currentTime = 0;
            sound.play().catch(e => {
                // Fallback ke Web Audio API synth jika audio fail
                playSuccessBeep();
            });
        }

        async function pollKitchen() {
            try {
                const response = await fetch('poll_kitchen.php');
                const data = await response.json();
                
                // Hitung jumlah antrean 'Menunggu' yang baru
                const waitingCountNow = data.filter(o => o.status_dapur === 'Menunggu').length;
                
                // Jika ada pertambahan pesanan di antrean 'Menunggu', bunyikan notifikasi!
                if (waitingCountNow > waitingCountPrev) {
                    playNotification();
                }
                
                waitingCountPrev = waitingCountNow;
                ordersState = data;
                
                renderBoard();
            } catch (err) {
                console.error('KDS Polling Error:', err);
            }
        }

        function renderBoard() {
            const colWaiting = document.getElementById('column-waiting');
            const colCooking = document.getElementById('column-cooking');
            
            colWaiting.innerHTML = '';
            colCooking.innerHTML = '';
            
            let waitCount = 0;
            let cookCount = 0;
            
            ordersState.forEach(order => {
                const card = document.createElement('div');
                card.className = `order-card ${order.status_dapur === 'Menunggu' ? 'waiting-card' : 'cooking-card'}`;
                card.id = `order-card-${order.id}`;
                
                // Render items list
                let itemsHtml = '';
                order.items.forEach(item => {
                    let metaHtml = '';
                    if (item.level_pedas && item.level_pedas !== 'Tidak Pedas') {
                        metaHtml += `<span class="meta-spice">${item.level_pedas}</span>`;
                    }
                    if (item.catatan) {
                        metaHtml += `<span class="meta-note">Notes: ${item.catatan}</span>`;
                    }
                    
                    itemsHtml += `
                        <div class="item-row">
                            <span class="item-qty">${item.jumlah}x</span>
                            <div class="item-info">
                                <span class="item-name">${item.nama}</span>
                                ${metaHtml ? `<div class="item-meta">${metaHtml}</div>` : ''}
                            </div>
                        </div>
                    `;
                });
                
                // Action Button
                let actionBtnHtml = '';
                if (order.status_dapur === 'Menunggu') {
                    actionBtnHtml = `
                        <button class="btn-kds btn-waiting" onclick="updateOrderStatus(${order.id}, 'Dimasak')">
                            <i data-lucide="play"></i> Mulai Masak
                        </button>
                    `;
                    waitCount++;
                } else if (order.status_dapur === 'Dimasak') {
                    actionBtnHtml = `
                        <button class="btn-kds btn-cooking" onclick="updateOrderStatus(${order.id}, 'Siap')">
                            <i data-lucide="check-check"></i> Siap Saji
                        </button>
                    `;
                    cookCount++;
                }
                
                const custDisplay = order.nama_pelanggan ? `${order.nama_pelanggan} (${order.tipe_pesanan})` : order.tipe_pesanan;
                
                card.innerHTML = `
                    <div class="card-header">
                        <div>
                            <div class="meja-label">${order.nama_meja}</div>
                            <div class="cust-name">${custDisplay}</div>
                        </div>
                        <div class="time-badge">
                            <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                            <span>${order.waktu}</span>
                        </div>
                    </div>
                    
                    <div class="card-items">
                        ${itemsHtml}
                    </div>
                    
                    <div class="card-action">
                        ${actionBtnHtml}
                    </div>
                `;
                
                if (order.status_dapur === 'Menunggu') {
                    colWaiting.appendChild(card);
                } else {
                    colCooking.appendChild(card);
                }
            });
            
            // Update Stats Display
            document.getElementById('waiting-count').innerText = waitCount;
            document.getElementById('cooking-count').innerText = cookCount;
            document.getElementById('total-stat').innerText = waitCount + cookCount;
            
            lucide.createIcons();
        }

        async function updateOrderStatus(orderId, nextStatus) {
            try {
                // Ubah status visual secara optimis terlebih dahulu
                const card = document.getElementById(`order-card-${orderId}`);
                if (card) {
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                }
                
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_pesanan: orderId,
                        status_dapur: nextStatus
                    })
                });
                
                const result = await response.json();
                if (result.status === 'success') {
                    // Update state lokal dan render ulang
                    pollKitchen();
                } else {
                    alert('Gagal update status: ' + result.message);
                    pollKitchen(); // rollback ke state asli
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan!');
                pollKitchen();
            }
        }
    </script>
</body>
</html>
