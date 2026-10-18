# Instruksi Agent — Cak Mol POS

- Source aplikasi aktif adalah root workspace; `warungcakmol-copy-2/` adalah salinan terpisah dan `vendor/` adalah dependency. Jangan membaca atau mengedit keduanya untuk tugas aplikasi biasa.
- Ini aplikasi PHP prosedural dengan `mysqli`, MySQL/MariaDB, HTML/CSS, dan JavaScript; jangan mengasumsikan framework, inventori, atau build/test pipeline yang tidak ada.
- Mulai dari [README](../README.md), lalu baca [arsitektur](../docs/ARCHITECTURE.md), [alur bisnis](../docs/WORKFLOWS.md), [model data](../docs/DATA_MODEL.md), atau [panduan development](../docs/DEVELOPMENT.md) sesuai dampak tugas. Source aktif adalah acuan bila berbeda dari dokumentasi.
- Sebelum perubahan, telusuri pemanggil dan penerima endpoint serta seluruh modul terkait. Pertahankan kontrak request/response, status domain, keamanan sesi/CSRF, dan konsistensi transaksi.
- Gunakan prepared statements untuk nilai input, validasi di server, harga dari database, transaksi untuk operasi multi-row yang harus atomik, dan escaping sesuai konteks output.
- Perubahan alur checkout/status perlu mempertimbangkan pelanggan, KDS, kasir, meja, dan struk. Perubahan schema perlu meninjau semua pemakai dan memperbarui dokumentasi; dump SQL bukan migration runner.
- Buat perubahan sesempit mungkin mengikuti pola yang ada. Jalankan `php -l` pada file PHP yang diubah bila CLI tersedia dan uji alur yang terdampak; laporkan pemeriksaan yang benar-benar dijalankan.
