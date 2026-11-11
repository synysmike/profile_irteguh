# PRD — Modul Keuangan Ir Teguh Solution

**Status:** Living document  
**Versi:** 1.0  
**Tanggal:** 2026-08-01  
**Stack:** Laravel 11, Blade admin, MySQL, Vite/JS  

Dokumen terkait: [README.md](README.md) (alur) · [checklist.md](checklist.md) (backlog)

---

## 1. Ringkasan produk

Modul keuangan memungkinkan operasional bisnis mencatat:

1. Pembelian stok (grosir)  
2. Alokasi stok untuk dijual atau ditempel ke project  
3. Penjualan kasir (POS) menjadi invoice  
4. Project jasa + stok + termin pembayaran  
5. Pajak & diskon  
6. Kas masuk/keluar  
7. Laporan ringkas  

Tujuan: satu sumber angka untuk invoice, kas, dan progress pembayaran project, dengan akses invoice publik yang aman via verifikasi nomor HP.

---

## 2. Pengguna & persona

| Persona | Kebutuhan utama |
|---------|-----------------|
| **Admin / owner** | Lihat omzet, kas, progress project, kirim invoice WA |
| **Staff operasional** | Input grosir, alokasi, kasir POS, update status project |
| **Customer (publik)** | Buka invoice lewat link; verifikasi dengan nomor HP sendiri |

Asumsi saat ini: semua user login admin punya akses penuh. Role terpisah = backlog ([checklist.md](checklist.md)).

---

## 3. Cakupan (in scope)

### 3.1 Master

| Fitur | Acceptance criteria |
|-------|---------------------|
| COA | CRUD akun; minimal satu akun tipe kas untuk auto-post |
| Pajak | CRUD; rate %; `addition` atau `deduction`; status aktif |
| Diskon | CRUD; tipe persen atau nominal; status aktif |
| Customer | CRUD; HP dinormalisasi otomatis; dipakai invoice & WA |
| Supplier / Karyawan | CRUD pendukung transaksi |

### 3.2 Grosir (Purchase)

- Input pembelian dengan pajak opsional.  
- Mengurangi kas (pengeluaran).  
- Menyediakan **sisa stok** untuk alokasi.  
- Snapshot pajak tersimpan di dokumen (aman jika master berubah).

### 3.3 Alokasi stok (SaleTransaction)

- Ambil qty dari sisa purchase.  
- Harga jual + diskon baris → subtotal net.  
- Status: tersedia untuk POS, atau terikat project.  
- Tidak boleh melebihi sisa stok (validasi server).

### 3.4 Kasir POS / Sale

- Keranjang dari purchase sisa atau alokasi siap.  
- Checkout atomik: `Sale` + `SaleItem` + update stok/alokasi + kas.  
- Pajak dari master.  
- Daftar faktur; kirim WhatsApp.  
- Link publik `/invoice/{id}` dengan verifikasi HP.

### 3.5 Invoice publik

| Langkah | Perilaku yang diharapkan |
|---------|--------------------------|
| Buka link | Form verifikasi HP (kecuali admin login) |
| HP cocok | Tampilkan invoice; simpan flag di session |
| HP salah | Tolak; throttle percobaan |
| GET invoice | Read-only — tidak menulis ulang item DB |

### 3.6 Project

- Tiket project: kode, customer, status, progress %.  
- Nilai = jasa (− diskon) + stok alokasi + pajak.  
- Metode bayar: lunas sekaligus atau termin (DP + cicilan, total 100%).  
- Edit termin khusus; sync % ↔ nominal dari total.  
- Posting termin → invoice `Sale` + kas; status termin `paid`.  
- Unpay (admin) mengembalikan status sesuai aturan bisnis.  
- Surat tugas & WA termin.

### 3.7 Kas & laporan (saat ini)

| Laporan | Requirement minimum v1 |
|---------|------------------------|
| Kas/Bank | List + CRUD manual + auto dari sale/purchase |
| Dashboard | Omzet, kas in/out, ringkasan pajak |
| Arus kas | Berdasarkan `CashTransaction` periode |
| Laba rugi | Sederhana (penjualan vs pembelian) — **akan diganti HPP** |
| Neraca / buku besar / SPT | Placeholder → backlog |

---

## 4. Modul publik — Layanan Kami (WAstore)

**Status tahap:** Fase A–D jalan (katalog → checkout → konfirmasi/tolak admin).

### 4.1 Ringkasan

Menu publik **Layanan Kami** (`/services`) menjadi etalase marketplace (familiar seperti Shopee/Tokopedia) dengan tema glass site yang ada. Master **`services`** dipakai sebagai **kategori induk** katalog.

Checkout publik wajib identitas yang merujuk ke master **Customer** (admin). Satu order = 1 Customer + 1 pesan WA; di backend boleh dipecah menjadi draft **Sale (POS/produk)** dan/atau **Project (jasa)** — keputusan split & auto-create diputuskan setelah UI stabil.

### 4.2 Persona tambahan

| Persona | Kebutuhan |
|---------|-----------|
| **Pengunjung / pembeli** | Browse katalog, keranjang, checkout tanpa login, kirim order via WhatsApp |
| **Admin** | Kelola master `services` (kategori); nanti kelola item produk/jasa & proses order masuk |

### 4.3 Acceptance criteria (bertahap)

| Fase | Kriteria |
|------|----------|
| **Static (sekarang)** | Nav “Layanan Kami”; halaman marketplace: search, filter kategori dari `services`, grid kartu (harga, rating dummy, +keranjang); drawer keranjang + form checkout (nama, WA, alamat); tema mengikuti site; hook/TODO jelas untuk tahap berikutnya |
| **Katalog nyata** | Item produk/jasa terhubung ke `service_id`; produk merujuk stok/POS; jasa merujuk template/project; rating dari akumulasi `project_reviews` / `sale_reviews` |
| **Checkout nyata** | Sekaligus: (1) create/update `Customer` by phone, (2) kirim/buka WA ke nomor admin, (3) buat **draft** Sale (produk) dan/atau Project (jasa) status pending |
| **Fulfillment** | Admin konfirmasi draft → proses POS (potong stok/kas) dan/atau jalankan project; campuran boleh 1 Sale + 1 Project |

### 4.4 Aturan bisnis (draft — boleh difinalisasi nanti)

1. `services` = **master kategori** etalase Layanan Kami (bukan diganti nama “Toko”; bukan master kategori tabel baru). Pengelolaan tetap di CRUD admin Services yang sudah ada.  
   - Index/form services **tidak wajib dirombak besar** — field title, icon, order, is_active sudah cukup untuk filter.  
   - Perluasan ringan: rapikan label/copy (mis. “master kategori etalase”), opsional kolom jumlah item tertaut **setelah** relasi jalan.  
   - Field `features` boleh tetap (deskripsi kategori) atau dikurangi nanti jika tidak dipakai kartu marketplace.  
   - Alokasi stok & project yang tampil di etalase menunjuk `service_id` agar sorting/filter kategori konsisten.
2. Identitas checkout wajib: nama → `customers.name`, WA → `customers.phone`, alamat/catatan → `customers.address` (+ optional city/notes).  
3. Satu order publik = satu ringkasan WA; backend boleh 1 Sale + 1 Project jika ada keduanya.  
4. Rating kartu katalog = akumulasi ulasan customer terkait (bukan input manual utama).  
5. **Checkout publik (diputuskan 2026-11-11):** berjalan **ketiganya** sekaligus — bukan pilih salah satu:
   1. Kirim ringkasan order ke WhatsApp admin (default `081332444088` / `6281332444088`; sebaiknya lewat Setting).  
   2. Create/update baris di tabel **Customer** (by nomor WA: name, phone, address/catatan).  
   3. Buat **draft pesanan**: item produk → draft **Sale/POS** (pending); item jasa → draft **Project** (pending); keranjang campuran boleh keduanya.  
   Draft belum memotong stok / posting kas sampai admin konfirmasi.  
6. **Sumber produk etalase (diputuskan 2026-11-11):** dari **alokasi stok** (`sale_transactions` siap jual), bukan daftar grosir mentah. Di tabel Alokasi Stok, kolom aksi punya kontrol tampil/sembunyi ke **Layanan Kami** (checklist/toggle). Hanya alokasi yang di-flag yang muncul di katalog publik. **`purchase_id` wajib** (terhubung grosir) agar stok/HPP disiplin — alokasi manual tanpa grosir tidak bisa dipajang.  
7. **Harga produk etalase (diputuskan 2026-11-11):** mengikuti **harga jual alokasi stok** (`sale_transactions.unit_price` / subtotal terkait) saja — tidak ada harga etalase terpisah.  
8. **Sumber jasa etalase (diputuskan 2026-11-11):** dari daftar **Project**. Admin membuat project (termasuk draft) di halaman manajemen project yang sudah ada; di kolom aksi ada tombol/toggle **tampilkan / tidak di etalase Layanan Kami**. Hanya project yang di-flag yang muncul sebagai kartu jasa di katalog publik. Tidak perlu master etalase jasa terpisah.  
9. **Harga jasa etalase (default):** **total project**.  
10. **Checkout jasa (diputuskan 2026-11-11):** **jangan** memakai / mengalihkan project etalase yang sedang dipajang. Sistem **membuat draft Project baru** yang **serupa** (salin field relevan: judul/deskripsi/nilai jasa/kategori/dll. dari project etalase sebagai template), lalu **customer_id** diisi dari Customer pemesan (hasil lookup/create saat checkout). Project etalase tetap utuh di katalog.  
11. **Default implementasi (diputuskan 2026-11-11):**  
    - Harga kartu **jasa** = **total project** (tampil etalase).  
    - `service_id` **wajib** jika item di-flag tampil di etalase.  
    - Draft Sale dari checkout = status/flag **pending** (detail skema saat Fase C).  
    - Qty produk di toko: **boleh sebagian**, maksimum sisa qty alokasi.  
    - Project etalase **boleh tanpa customer** sampai di-clone saat order.

### 4.5 Out of scope modul ini (untuk sekarang)

- Payment gateway di checkout publik  
- Login pembeli / akun member  
- Stok real-time penuh di etalase (menyusul saat item terhubung POS)

---

## 5. Di luar cakupan (out of scope v1 — keuangan inti)

- Integrasi payment gateway / e-wallet  
- Multi-cabang / multi-currency  
- E-Faktur DJP otomatis  
- Aplikasi mobile native  
- Inventory multi-gudang lengkap  

---

## 6. Aturan bisnis penting

1. **Snapshot pajak/diskon** pada dokumen transaksi tidak berubah otomatis saat master diubah (kecuali ada aksi recalc eksplisit di project).  
2. **Termin paid** tidak boleh diedit ulang semena-mena; nominal yang sudah di-invoice harus frozen.  
3. **Sisa stok** tidak boleh negatif.  
4. **HP customer** wajib valid untuk invoice publik & WA.  
5. **Posting termin** hanya untuk termin unpaid; menghasilkan satu `Sale` per posting.  
6. **Admin login** bypass verifikasi HP invoice publik.

---

## 7. Model data (konseptual)

```
customers, suppliers, employees
chart_of_accounts
taxes, discounts
purchases (+ tax snapshot)
sale_transactions (alokasi dari purchase; optional project_id, discount)
projects, project_payment_terms
sales, sale_items (link sale_transaction_id)
cash_transactions (link sale/purchase/project optional)
journal_entries (manual)
```

Service utama:

- `App\Services\ProjectFinanceService` — hitung DPP/total/termin project  
- `App\Services\PosCheckoutService` — checkout kasir  
- `App\Support\PhoneNumber` — normalisasi HP  
- `App\Support\SaleWhatsAppInvoice` / `ProjectTermWhatsAppInvoice` — pesan WA  

---

## 8. Non-functional

| Aspek | Target |
|-------|--------|
| Keamanan invoice | Verifikasi HP + throttle; tidak leak via path tebak-ID tanpa verify |
| Konsistensi | Checkout & posting dalam DB transaction |
| UX admin | Form AJAX modal tetap init JS (`admin-ajax.js`) |
| Audit | Backlog: log aksi sensitif |
| Performa | Lock stok saat concurrent write (backlog) |

---

## 9. Metrik sukses

- Staf bisa menyelesaikan alur Grosir → Alokasi → POS → Invoice WA tanpa inkonsistensi angka.  
- Customer membuka invoice hanya dengan HP yang cocok.  
- Project termin yang sudah dibayar tidak bergeser nominalnya saat edit project.  
- Hapus/batal invoice tidak meninggalkan stok “nyangkut” (setelah checklist #restore selesai).  
- Laporan kas & omzet dashboard sesuai transaksi periode.

---

## 10. Roadmap singkat

| Fase | Fokus |
|------|--------|
| **Done** | Master pajak/diskon, project+termin, POS, invoice HP verify, normalisasi HP |
| **Done (UI)** | Layanan Kami marketplace static (search/filter/keranjang/checkout demo) |
| **Done (Fase B)** | Flag etalase alokasi+project, `service_id`, katalog publik dari data nyata, qty max |
| **Done (Fase C)** | Checkout: Customer upsert + draft Sale/Project + WA admin (`Setting::contactWhatsapp`) |
| **Done (Fase D)** | Konfirmasi/tolak draft: Sale potong alokasi+kas; Project aktif+termin; reject aman |
| **Next** | Freeze termin lunas, restore stok hapus sale, samakan diskon POS, lock stok |
| **Done (Layanan polish)** | Antrean Order Layanan, rating/sold kartu, counts services, portfolio_category←services |
| **Later** | HPP & laba rugi akurat, neraca, buku besar, auto-jurnal, role/permission |

Detail item: [checklist.md](checklist.md).

---

## 11. Catatan perubahan dokumen

| Tanggal | Perubahan |
|---------|-----------|
| 2026-08-01 | PRD awal dari review alur keuangan + fitur invoice HP & diskon |
| 2026-11-11 | Tambah modul publik Layanan Kami (WAstore): `services` sebagai master kategori; UI static; alur Sale/Project & rating ditunda |
| 2026-11-11 | Produk etalase dari alokasi stok; toggle tampil di tabel Alokasi Stok → Layanan Kami |
| 2026-11-11 | Checkout publik: WA admin + simpan Customer + draft Sale/Project (pending) sekaligus |
| 2026-11-11 | Harga etalase Layanan Kami = harga alokasi stok (tanpa harga jual terpisah) |
| 2026-11-11 | Etalase jasa dari daftar Project + toggle tampil di kolom aksi project |
| 2026-11-11 | Checkout jasa: buat draft Project baru (salinan template) + customer pemesan; jangan pakai project etalase |
| 2026-11-11 | Konfirmasi: `services` = master kategori; index services cukup + rapikan label; `service_id` di alokasi & project etalase |
| 2026-11-11 | Default: harga jasa=total project; service_id wajib saat tampil; qty parsial; project etalase boleh tanpa customer; draft Sale pending |
| 2026-11-11 | Fase B selesai: migration flag etalase, toggle admin, katalog publik dari alokasi/project |
| 2026-11-11 | Fase C: `POST /services/checkout` → Customer + draft Sale/Project + buka WA admin |
| 2026-11-11 | Fase D: konfirmasi/tolak draft Sale & Project (`LayananFulfillmentService`) |
| 2026-11-11 | Keputusan: etalase produk wajib `purchase_id` (grosir); UI menolak alokasi tanpa grosir |
| 2026-11-11 | Polish Layanan + prioritas keuangan: antrean order, rating/sold, restore sale, lock stok, freeze termin, diskon POS |
| 2026-11-11 | Tema Beranda v1: admin `home-theme` (copy/warna/overlay/toggle section) via Setting |
