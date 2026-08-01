# Checklist Modul Keuangan

Daftar pekerjaan perbaikan & peningkatan berdasarkan review sistem. Centang saat selesai.

Lihat juga: [README.md](README.md) (alur) · [prd.md](prd.md) (requirement)

---

## Prioritas tinggi (keamanan & integritas data)

- [x] Invoice publik pakai verifikasi nomor HP (bukan signed URL saja)
- [x] GET invoice publik tidak mengubah `sale_items` di database
- [x] Normalisasi nomor HP saat simpan customer (paste dari WhatsApp)
- [ ] Pastikan path admin invoice selalu auth / hanya redirect ke publik (audit ulang akses tanpa login)
- [ ] Hapus/batal sale mengembalikan alokasi stok + hapus kas terkait + reset status termin (satu transaksi DB)
- [ ] `lockForUpdate` pada stok/alokasi saat checkout POS & buat alokasi (hindari race dua user)
- [ ] Aturan termin setelah ada yang lunas: nominal paid freeze; unpaid = sisa dari total baru (atau kunci total setelah posting pertama)

---

## Prioritas sedang (konsistensi angka)

- [ ] Samakan hitung diskon POS: preview keranjang = backend (`PosCheckoutService`) — satu helper shared
- [ ] Tampilkan diskon di UI keranjang POS
- [ ] Opsional: pilih diskon saat jual langsung dari grosir (tanpa lewat alokasi)
- [ ] Edit termin/DP yang sudah lunas tidak boleh hitung ulang nominal invoice yang sudah diposting
- [ ] Setelah ubah stok/diskon project: unpaid terms ikut recalc; paid tetap frozen; progress % tetap konsisten dengan total
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

---

*Terakhir diperbarui: 2026-08-01*
