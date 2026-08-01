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

## 4. Di luar cakupan (out of scope v1)

- Integrasi payment gateway / e-wallet  
- Multi-cabang / multi-currency  
- E-Faktur DJP otomatis  
- Aplikasi mobile native  
- Inventory multi-gudang lengkap  

---

## 5. Aturan bisnis penting

1. **Snapshot pajak/diskon** pada dokumen transaksi tidak berubah otomatis saat master diubah (kecuali ada aksi recalc eksplisit di project).  
2. **Termin paid** tidak boleh diedit ulang semena-mena; nominal yang sudah di-invoice harus frozen.  
3. **Sisa stok** tidak boleh negatif.  
4. **HP customer** wajib valid untuk invoice publik & WA.  
5. **Posting termin** hanya untuk termin unpaid; menghasilkan satu `Sale` per posting.  
6. **Admin login** bypass verifikasi HP invoice publik.

---

## 6. Model data (konseptual)

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

## 7. Non-functional

| Aspek | Target |
|-------|--------|
| Keamanan invoice | Verifikasi HP + throttle; tidak leak via path tebak-ID tanpa verify |
| Konsistensi | Checkout & posting dalam DB transaction |
| UX admin | Form AJAX modal tetap init JS (`admin-ajax.js`) |
| Audit | Backlog: log aksi sensitif |
| Performa | Lock stok saat concurrent write (backlog) |

---

## 8. Metrik sukses

- Staf bisa menyelesaikan alur Grosir → Alokasi → POS → Invoice WA tanpa inkonsistensi angka.  
- Customer membuka invoice hanya dengan HP yang cocok.  
- Project termin yang sudah dibayar tidak bergeser nominalnya saat edit project.  
- Hapus/batal invoice tidak meninggalkan stok “nyangkut” (setelah checklist #restore selesai).  
- Laporan kas & omzet dashboard sesuai transaksi periode.

---

## 9. Roadmap singkat

| Fase | Fokus |
|------|--------|
| **Done** | Master pajak/diskon, project+termin, POS, invoice HP verify, normalisasi HP |
| **Next** | Freeze termin lunas, restore stok hapus sale, samakan diskon POS, lock stok |
| **Later** | HPP & laba rugi akurat, neraca, buku besar, auto-jurnal, role/permission |

Detail item: [checklist.md](checklist.md).

---

## 10. Catatan perubahan dokumen

| Tanggal | Perubahan |
|---------|-----------|
| 2026-08-01 | PRD awal dari review alur keuangan + fitur invoice HP & diskon |
