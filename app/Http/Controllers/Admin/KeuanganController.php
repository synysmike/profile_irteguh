<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\JournalEntry;
use App\Models\CashTransaction;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** 1. Dashboard */
    public function dashboard()
    {
        $currentMonth = date('Y-m');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        
        // Kas masuk/keluar bulan ini dari cash transactions
        $kasMasukBulan = CashTransaction::where('transaction_type', 'debit')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');
        
        $kasKeluarBulan = CashTransaction::where('transaction_type', 'credit')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');
        
        // Omzet bulan ini dari penjualan (subtotal, bukan total karena total sudah termasuk PPN)
        $omzetBulan = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->sum('subtotal');
        
        // Status PPN
        $ppnKeluaran = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->sum('ppn_amount');
        $ppnMasukan = Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->sum('ppn_amount');
        $ppnTerutang = $ppnKeluaran - $ppnMasukan;
        $ppnStatus = $ppnTerutang > 0 ? 'Terutang: Rp ' . number_format($ppnTerutang, 0, ',', '.') : 'Lebih Bayar';
        
        // Status PPh (sederhana, bisa dikembangkan)
        $labaKotor = $omzetBulan - Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->sum('subtotal');
        $pphStatus = $labaKotor > 0 ? 'Estimasi: Rp ' . number_format($labaKotor * 0.22, 0, ',', '.') : 'Belum ada laba';
        
        $stats = [
            'kas_masuk_bulan' => $kasMasukBulan,
            'kas_keluar_bulan' => $kasKeluarBulan,
            'omzet_bulan' => $omzetBulan,
            'pph_status' => $pphStatus,
            'ppn_status' => $ppnStatus,
        ];
        
        // Grafik omzet dan arus kas 6 bulan terakhir
        $grafikBulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $mStart = $date->format('Y-m-01');
            $mEnd = $date->format('Y-m-t');
            
            // Omzet dari penjualan
            $omzet = Sale::whereBetween('sale_date', [$mStart, $mEnd])->sum('subtotal');
            
            // Arus kas dari transaksi kas
            $kasMasuk = CashTransaction::where('transaction_type', 'debit')
                ->whereBetween('transaction_date', [$mStart, $mEnd])
                ->sum('amount');
            $kasKeluar = CashTransaction::where('transaction_type', 'credit')
                ->whereBetween('transaction_date', [$mStart, $mEnd])
                ->sum('amount');
            $arusKasBersih = $kasMasuk - $kasKeluar;
            
            $grafikBulanan[] = [
                'bulan' => $date->locale('id')->translatedFormat('M Y'),
                'omzet' => $omzet,
                'kas_masuk' => $kasMasuk,
                'kas_keluar' => $kasKeluar,
                'arus_kas_bersih' => $arusKasBersih,
            ];
        }
        
        return view('admin.keuangan.dashboard', compact('stats', 'grafikBulanan'));
    }

    /** 2. Master Data - Akun Perkiraan (Chart of Accounts) */
    public function masterAkun()
    {
        $accounts = ChartOfAccount::ordered()->get();
        return view('admin.keuangan.master.akun', compact('accounts'));
    }

    /** 2. Master Data - Data Klien & Vendor (link ke Suppliers & Customers) */
    public function masterKlienVendor()
    {
        return view('admin.keuangan.master.klien-vendor');
    }

    /** 2. Master Data - Data Karyawan */
    public function masterKaryawan()
    {
        $employees = Employee::ordered()->get();
        return view('admin.keuangan.master.karyawan', compact('employees'));
    }

    /** 3. Transaksi - Penjualan */
    public function transaksiPenjualan()
    {
        $sales = Sale::with('customer', 'cashTransaction', 'project')->latestFirst()->get();
        return view('admin.keuangan.transaksi.penjualan', compact('sales'));
    }

    /** 3. Transaksi - Pembelian */
    public function transaksiPembelian()
    {
        $purchases = Purchase::with('supplier', 'cashTransaction', 'saleTransactions')->latestFirst()->get();
        return view('admin.keuangan.transaksi.pembelian', compact('purchases'));
    }

    /** 3. Transaksi - Kas/Bank */
    public function transaksiKasBank()
    {
        $cashTransactions = CashTransaction::with('chartOfAccount', 'sale', 'purchase', 'project')->latestFirst()->get();
        return view('admin.keuangan.transaksi.kas-bank', compact('cashTransactions'));
    }

    /** 3. Transaksi - Gaji & Payroll */
    public function transaksiGaji()
    {
        return view('admin.keuangan.transaksi.gaji');
    }

    /** 4. Jurnal Umum */
    public function jurnal()
    {
        $journalEntries = JournalEntry::with('lines.chartOfAccount')->latestFirst()->get();
        return view('admin.keuangan.jurnal.index', compact('journalEntries'));
    }

    /** 5. Laporan Keuangan */
    public function laporanNeraca()
    {
        return view('admin.keuangan.laporan.neraca');
    }

    public function laporanLabaRugi(Request $request)
    {
        // Filter periode (default: bulan ini)
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Pendapatan dari penjualan
        $pendapatanPenjualan = Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('subtotal');
        
        // Beban dari pembelian
        $bebanPembelian = Purchase::whereBetween('purchase_date', [$startDate, $endDate])->sum('subtotal');
        
        // Beban lainnya dari jurnal (jika ada akun beban)
        $bebanLainnya = 0; // Bisa dikembangkan dengan mengambil dari journal entries dengan akun tipe 'beban'
        
        // Total beban
        $totalBeban = $bebanPembelian + $bebanLainnya;
        
        // Laba Kotor
        $labaKotor = $pendapatanPenjualan - $totalBeban;
        
        // PPN (dari penjualan dan pembelian)
        $ppnKeluaran = Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('ppn_amount');
        $ppnMasukan = Purchase::whereBetween('purchase_date', [$startDate, $endDate])->sum('ppn_amount');
        $ppnTerutang = $ppnKeluaran - $ppnMasukan;
        
        // Laba Bersih (sebelum pajak)
        $labaBersih = $labaKotor;
        
        // Detail penjualan per bulan dalam periode
        $penjualanBulanan = [];
        // Detail pembelian per bulan dalam periode
        $pembelianBulanan = [];
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $current = $start->copy();
        
        while ($current->lte($end)) {
            $monthStart = $current->format('Y-m-01');
            $monthEnd = $current->format('Y-m-t');
            $monthKey = $current->format('Y-m');
            
            // Penjualan bulanan
            $penjualanBulanan[$monthKey] = [
                'bulan' => $current->locale('id')->translatedFormat('F Y'),
                'jumlah' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->count(),
                'subtotal' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->sum('subtotal'),
                'total' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->sum('total'),
            ];
            
            // Pembelian bulanan
            $pembelianBulanan[$monthKey] = [
                'bulan' => $current->locale('id')->translatedFormat('F Y'),
                'jumlah' => Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->count(),
                'subtotal' => Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->sum('subtotal'),
                'total' => Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->sum('total'),
            ];
            
            $current->addMonth();
        }
        
        return view('admin.keuangan.laporan.laba-rugi', compact(
            'startDate', 'endDate',
            'pendapatanPenjualan', 'bebanPembelian', 'bebanLainnya', 'totalBeban',
            'labaKotor', 'labaBersih',
            'ppnKeluaran', 'ppnMasukan', 'ppnTerutang',
            'penjualanBulanan', 'pembelianBulanan'
        ));
    }

    public function laporanArusKas(Request $request)
    {
        // Filter periode (default: bulan ini)
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Arus Kas Operasi dari transaksi kas
        $kasMasukOperasi = CashTransaction::where('transaction_type', 'debit')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $kasKeluarOperasi = CashTransaction::where('transaction_type', 'credit')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Detail dari penjualan dan pembelian
        $kasDariPenjualan = CashTransaction::where('transaction_type', 'debit')
            ->whereNotNull('sale_id')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $kasUntukPembelian = CashTransaction::where('transaction_type', 'credit')
            ->whereNotNull('purchase_id')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $kasManual = CashTransaction::whereNull('sale_id')
            ->whereNull('purchase_id')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();
        
        $kasMasukManual = $kasManual->where('transaction_type', 'debit')->sum('amount');
        $kasKeluarManual = $kasManual->where('transaction_type', 'credit')->sum('amount');
        
        // Arus Kas Bersih
        $arusKasBersih = $kasMasukOperasi - $kasKeluarOperasi;
        
        return view('admin.keuangan.laporan.arus-kas', compact(
            'startDate', 'endDate',
            'kasMasukOperasi', 'kasKeluarOperasi', 'arusKasBersih',
            'kasDariPenjualan', 'kasUntukPembelian',
            'kasMasukManual', 'kasKeluarManual'
        ));
    }

    public function laporanBukuBesar()
    {
        return view('admin.keuangan.laporan.buku-besar');
    }

    /** 6. Menu Pajak */
    public function pajakPphBadan()
    {
        return view('admin.keuangan.pajak.pph-badan');
    }

    public function pajakPph21()
    {
        return view('admin.keuangan.pajak.pph-21');
    }

    public function pajakPpn()
    {
        $sales = Sale::latestFirst()->get();
        $purchases = Purchase::latestFirst()->get();
        
        $ppnKeluaran = $sales->sum('ppn_amount');
        $ppnMasukan = $purchases->sum('ppn_amount');
        $ppnTerutang = $ppnKeluaran - $ppnMasukan;
        
        // Rekap per bulan
        $ppnBulanan = [];
        $currentYear = date('Y');
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = "$currentYear-$month-01";
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            $salesMonth = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->get();
            $purchasesMonth = Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->get();
            $ppnBulanan[$month] = [
                'keluaran' => $salesMonth->sum('ppn_amount'),
                'masukan' => $purchasesMonth->sum('ppn_amount'),
                'terutang' => $salesMonth->sum('ppn_amount') - $purchasesMonth->sum('ppn_amount'),
            ];
        }
        
        return view('admin.keuangan.pajak.ppn', compact('sales', 'purchases', 'ppnKeluaran', 'ppnMasukan', 'ppnTerutang', 'ppnBulanan'));
    }

    /** 7. Laporan Pajak */
    public function laporanPajak()
    {
        return view('admin.keuangan.laporan-pajak.index');
    }

    /** 8. Utility */
    public function utility()
    {
        return view('admin.keuangan.utility.index');
    }
}
