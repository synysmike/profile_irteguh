# Checklist Modul Keuangan

Daftar pekerjaan perbaikan & peningkatan berdasarkan review sistem. Centang saat selesai.

Lihat juga: [README.md](README.md) (alur) · [prd.md](prd.md) (requirement)

---

## Master jasa (2026-08-02)

- [x] CRUD Master Jasa (Keuangan → Master Jasa)
- [x] Baris jasa pada surat tugas (qty awal + harga)
- [x] Sesuaikan qty di detail project → sync `base_subtotal` + recalc invoice/termin unpaid
- [x] Kunci ubah nilai jasa jika ada termin lunas
- [x] Hint UX di form/detail project

---

## Prioritas tinggi (keamanan & integritas data)

- [x] Invoice publik pakai verifikasi nomor HP (bukan signed URL saja)
- [x] GET invoice publik tidak mengubah `sale_items` di database
- [x] Normalisasi nomor HP saat simpan customer (paste dari WhatsApp)
- [ ] Pastikan path admin invoice selalu auth / hanya redirect ke publik (audit ulang akses tanpa login)
- [x] Hapus/batal sale mengembalikan alokasi stok + hapus kas terkait + reset status termin (satu transaksi DB)
- [x] `lockForUpdate` pada stok/alokasi saat checkout POS & buat alokasi (hindari race dua user)
- [x] Aturan termin setelah ada yang lunas: nominal paid freeze; unpaid = sisa dari total baru

---

## Prioritas sedang (konsistensi angka)

- [x] Samakan hitung diskon POS: preview keranjang = backend (`PosCheckoutService`) — net + diskon di UI
- [x] Tampilkan diskon di UI keranjang POS
- [ ] Opsional: pilih diskon saat jual langsung dari grosir (tanpa lewat alokasi)
- [x] Edit termin/DP yang sudah lunas tidak boleh hitung ulang nominal invoice yang sudah diposting
- [x] Setelah ubah stok/diskon project: unpaid terms ikut recalc; paid tetap frozen; progress % tetap konsisten dengan total
- [ ] Hapus master pajak diblokir jika masih dipakai project/sale/purchase (atau soft-deactivate saja)
- [ ] Jika akun tipe `kas` belum ada: blokir posting / tampilkan error jelas (jangan sale tanpa cash diam-diam)

---

## Prioritas laporan & akuntansi

- [ ] Laba rugi pakai HPP dari stok terjual (alokasi terinvoice × harga beli), bukan “semua pembelian periode”
- [ ] Implementasi **Neraca** (bukan placeholder)
- [ ] Implementasi **Buku besar** dari kas + jurnal
- [ ] Implementasi **Laporan pajak / SPT** ringkas dari snapshot pajak di sale/purchase
- [ ] Auto-jurnal saat sale/purchase/kas (Kas / Pendapatan / Beban / PPN) — opsional bertahap

---

## Prioritas produk / UX

- [ ] Role & permission khusus modul keuangan (bukan semua admin sama)
- [ ] Audit log untuk unpay termin, hapus sale, ubah diskon project
- [ ] Tombol regenerate / salin ulang link invoice + petunjuk HP di admin
- [ ] Expiry session verifikasi invoice (opsional, mis. 24 jam)
- [ ] Validasi ketat: total % termin selalu 100% termasuk paid (sudah ada di form — uji edge case AJAX)
- [ ] Unit/feature test untuk: `ProjectFinanceService`, `PosCheckoutService`, verifikasi invoice HP, normalisasi HP

---

## Tema Beranda (publik)

- [x] Halaman admin Konten → **Tema Beranda** (`/admin/home-theme`)
- [x] Copy hero (kicker/tagline/deskripsi/CTA) dari Setting
- [x] Token warna aksen + gradient fallback + overlay slide
- [x] Toggle section kategori / unggulan / layanan / tim
- [x] Preview layanan dari master `services` aktif
- [x] Footer tagline + alamat dari Setting kontak

## SEO publik

- [x] Partial meta bersama (description, canonical, OG, Twitter, robots)
- [x] Meta per halaman dari konten terkait (layanan, portfolio, penjualan, berita, kontak, tentang)
- [x] Admin Konten → **SEO Beranda** (`/admin/home-seo`) title/description/keywords/robots/OG image
- [x] `robots.txt` + `sitemap.xml` (static + portfolio/penjualan/berita)

---

## Layanan Kami / WAstore (publik)

Lihat requirement: [prd.md](prd.md) §4.

### Fase A — UI static (marketplace)

- [x] Nav/label publik tetap **Layanan Kami** (bukan “Toko”)
- [x] Halaman `/services` layout marketplace (search, filter kategori dari master `services`, grid kartu)
- [x] Kartu item demo: harga, satuan, tipe produk/jasa, rating dummy, +keranjang
- [x] Keranjang (drawer) + qty + total; persist `localStorage`
- [x] Form checkout static: nama, WA, alamat/catatan wajib; metode bayar display
- [x] Tombol “Kirim ke WhatsApp” masih demo (alert / belum API)
- [x] Tema mengikuti glass site yang ada; hook/TODO untuk tahap berikutnya

### Fase B — Katalog & master

- [x] Putuskan sumber **produk** etalase: dari **alokasi stok** (`sale_transactions`), bukan grosir mentah
- [x] Putuskan **`purchase_id` wajib** untuk etalase produk (disiplin stok/HPP); UI tampilkan “Perlu grosir” bila kosong
- [x] Putuskan harga etalase: **ikuti harga alokasi stok** saja (tidak ada harga etalase terpisah)
- [x] Putuskan sumber **jasa** etalase: dari daftar **Project** + toggle tampil di kolom aksi halaman project
- [x] Putuskan master kategori = **`services`** (CRUD existing; tidak buat master kategori baru)
- [x] Putuskan index services: **tidak wajib overhaul** — cukup title/icon/order/aktif; rapikan label/copy
- [x] Putuskan default: harga jasa=total project; `service_id` wajib saat tampil; qty parsial max sisa alokasi; project etalase boleh tanpa customer; draft Sale=pending
- [x] Rapikan copy admin Services (subtitle/form): tegaskan peran sebagai master kategori etalase
- [x] Migration `show_in_layanan` + `service_id` pada `sale_transactions` & `projects`
- [x] Tambah flag tampil di Layanan Kami pada alokasi stok + checklist/toggle + pilih kategori di tabel Alokasi Stok
- [x] Tambah flag/tombol tampil di Layanan Kami pada daftar Project (kolom aksi) + pilih kategori
- [x] Katalog publik: produk = alokasi di-flag (`unit_price`); jasa = project di-flag (harga = total project)
- [x] Filter chip Layanan Kami memakai `services` aktif + `service_id` item
- [x] Validasi qty keranjang produk ≤ sisa qty alokasi (UI keranjang)
- [x] Putuskan checkout jasa: **jangan** pakai project etalase; **buat draft Project baru** yang serupa (salinan template) + `customer_id` dari pemesan
- [x] Implementasi clone project etalase → draft baru (field relevan disalin; customer = pemesan checkout)
- [x] Pastikan project etalase tetap tampil/utuh setelah order (tidak berubah jadi milik customer)
- [x] Rating kartu dari akumulasi `project_reviews` / `sale_reviews` (+ sold count)
- [x] Kolom jumlah produk/jasa tertaut di index services
- [x] Samakan/link `portfolio_category` project ke master `services` (opsi = title services)
- [x] Antrean Order Layanan terpusat (`/admin/keuangan/order-layanan`)

### Fase C — Checkout nyata (ketiganya sekaligus)

- [x] Putuskan alur checkout: **WA + Customer + draft Sale/Project** (bukan hanya salah satu)
- [x] Create/update `Customer` dari form checkout (name, phone, address/catatan) by nomor WA
- [x] Generate & buka WhatsApp ke admin — default `081332444088` (`6281332444088`); nomor dari `Setting::contactWhatsapp()`
- [x] Template pesan WA: identitas + daftar item + total + metode bayar + kode draft
- [x] Item **produk** (alokasi) → buat draft **Sale** `is_layanan_pending` (belum potong stok/kas; item tanpa `sale_transaction_id`)
- [x] Item **jasa** → buat draft **Project baru** (clone template), `status=pending`, `customer_id` = pemesan, `layanan_template_project_id` terisi
- [x] Keranjang campuran → boleh 1 draft Sale + N draft Project; tetap 1 pesan WA
- [x] Endpoint `POST /services/checkout` + wire UI keranjang
- [x] Badge “Order Layanan” di daftar POS & Project (antrean ringan)
- [x] Konfirmasi admin draft → potong stok/kas / jalankan project (Fase D)

### Fase D — Fulfillment (setelah draft ada)

- [x] Admin konfirmasi draft produk → link/split alokasi + posting kas (`LayananFulfillmentService::confirmSale`)
- [x] Admin konfirmasi draft jasa → `in_progress` + termin default jika kosong
- [x] Tolak draft Sale → hapus faktur (tanpa stok/kas); tolak draft Project → `cancelled`
- [x] Tombol Konfirmasi/Tolak di Kasir POS, daftar Project, dan detail Project
- [x] Draft pending tidak bisa dipajang ulang ke etalase
- [x] Finalisasi field draft Project: salinan nilai template + `layanan_template_project_id` + termin on confirm

---

## Sudah ada (referensi — jangan dikerjakan ulang)

- [x] CRUD Master Pajak dinamis (addition/deduction)
- [x] CRUD Master Diskon + terapkan ke jasa project & alokasi stok
- [x] Halaman Edit Termin project (`projects.terms.edit`) + sync % ↔ nominal
- [x] Kasir POS (katalog + checkout)
- [x] Posting termin project → Sale + CashTransaction
- [x] WhatsApp invoice sale & termin project
- [x] Dashboard keuangan + arus kas + laba rugi sederhana

---

## Urutan saran pengerjaan berikutnya

1. Amankan & audit ulang akses invoice admin/publik  
2. Freeze termin lunas + recalc unpaid yang aman  
3. Samakan diskon POS (UI = backend)  
4. Restore stok saat hapus/batal sale + lock stok  
5. Perbaiki laba rugi (HPP) lalu neraca/buku besar  
6. **Layanan Kami:** Fase A–D ✓ (etalase → checkout → konfirmasi admin)  

---

*Terakhir diperbarui: 2026-11-11*
