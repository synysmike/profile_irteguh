<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\JournalEntry;
use App\Models\CashTransaction;
use App\Models\Tax;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** 1. Dashboard — angka diselaraskan dengan daftar faktur & transaksi keuangan */
    public function dashboard()
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $salesMonth = Sale::whereBetween('sale_date', [$monthStart, $monthEnd]);
        $purchasesMonth = Purchase::whereBetween('purchase_date', [$monthStart, $monthEnd]);
        $kasMasukMonth = CashTransaction::where('transaction_type', 'debit')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
        $kasKeluarMonth = CashTransaction::where('transaction_type', 'credit')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);

        // Omzet = DPP (subtotal) faktur penjualan — sama sumber dengan daftar faktur.
        $omzetBulan = (float) (clone $salesMonth)->sum('subtotal');
        $totalFakturBulan = (float) (clone $salesMonth)->sum('total');
        $jumlahFakturBulan = (clone $salesMonth)->count();
        $fakturTerkaitKasBulan = (clone $salesMonth)->whereHas('cashTransaction')->count();
        $fakturBelumKasBulan = max(0, $jumlahFakturBulan - $fakturTerkaitKasBulan);

        $grosirBulanSubtotal = (float) (clone $purchasesMonth)->sum('subtotal');
        $grosirBulanTotal = (float) (clone $purchasesMonth)->sum('total');
        $jumlahGrosirBulan = (clone $purchasesMonth)->count();

        $kasMasukBulan = (float) (clone $kasMasukMonth)->sum('amount');
        $kasKeluarBulan = (float) (clone $kasKeluarMonth)->sum('amount');

        // Pajak: bedakan tambahan (PPN-like) vs potongan (PPh-like) dari snapshot faktur.
        $pajakTambahanBulan = (float) Sale::whereBetween('sale_date', [$monthStart, $monthEnd])
            ->where(function ($q) {
                $q->where('tax_calculation_type', 'addition')->orWhereNull('tax_calculation_type');
            })
            ->sum('ppn_amount');
        $pajakPotonganBulan = (float) Sale::whereBetween('sale_date', [$monthStart, $monthEnd])
            ->where('tax_calculation_type', 'deduction')
            ->sum('ppn_amount');
        $pajakMasukanBulan = (float) (clone $purchasesMonth)->sum('ppn_amount');

        // Ringkasan seluruh periode (sinkron dengan journal/daftar faktur penuh).
        $omzetSemua = (float) Sale::sum('subtotal');
        $totalFakturSemua = (float) Sale::sum('total');
        $jumlahFakturSemua = Sale::count();
        $grosirSemuaTotal = (float) Purchase::sum('total');
        $kasMasukSemua = (float) CashTransaction::where('transaction_type', 'debit')->sum('amount');
        $kasKeluarSemua = (float) CashTransaction::where('transaction_type', 'credit')->sum('amount');

        $stats = [
            'periode_label' => now()->locale('id')->translatedFormat('F Y'),
            'kas_masuk_bulan' => $kasMasukBulan,
            'kas_keluar_bulan' => $kasKeluarBulan,
            'arus_kas_bersih_bulan' => $kasMasukBulan - $kasKeluarBulan,
            'omzet_bulan' => $omzetBulan,
            'total_faktur_bulan' => $totalFakturBulan,
            'jumlah_faktur_bulan' => $jumlahFakturBulan,
            'faktur_terkait_kas_bulan' => $fakturTerkaitKasBulan,
            'faktur_belum_kas_bulan' => $fakturBelumKasBulan,
            'grosir_bulan_subtotal' => $grosirBulanSubtotal,
            'grosir_bulan_total' => $grosirBulanTotal,
            'jumlah_grosir_bulan' => $jumlahGrosirBulan,
            'pajak_tambahan_bulan' => $pajakTambahanBulan,
            'pajak_potongan_bulan' => $pajakPotonganBulan,
            'pajak_masukan_bulan' => $pajakMasukanBulan,
            'omzet_semua' => $omzetSemua,
            'total_faktur_semua' => $totalFakturSemua,
            'jumlah_faktur_semua' => $jumlahFakturSemua,
            'grosir_semua_total' => $grosirSemuaTotal,
            'kas_masuk_semua' => $kasMasukSemua,
            'kas_keluar_semua' => $kasKeluarSemua,
            'arus_kas_bersih_semua' => $kasMasukSemua - $kasKeluarSemua,
        ];

        // Grafik 6 bulan: omzet DPP + total faktur + arus kas (sumber sama dengan transaksi).
        $grafikBulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $mStart = $date->format('Y-m-01');
            $mEnd = $date->format('Y-m-t');

            $omzet = (float) Sale::whereBetween('sale_date', [$mStart, $mEnd])->sum('subtotal');
            $totalFaktur = (float) Sale::whereBetween('sale_date', [$mStart, $mEnd])->sum('total');
            $jumlahFaktur = Sale::whereBetween('sale_date', [$mStart, $mEnd])->count();
            $kasMasuk = (float) CashTransaction::where('transaction_type', 'debit')
                ->whereBetween('transaction_date', [$mStart, $mEnd])
                ->sum('amount');
            $kasKeluar = (float) CashTransaction::where('transaction_type', 'credit')
                ->whereBetween('transaction_date', [$mStart, $mEnd])
                ->sum('amount');

            $grafikBulanan[] = [
                'bulan' => $date->locale('id')->translatedFormat('M Y'),
                'omzet' => $omzet,
                'total_faktur' => $totalFaktur,
                'jumlah_faktur' => $jumlahFaktur,
                'kas_masuk' => $kasMasuk,
                'kas_keluar' => $kasKeluar,
                'arus_kas_bersih' => $kasMasuk - $kasKeluar,
            ];
        }

        // Daftar faktur terbaru — query yang sama dengan halaman penjualan.
        $recentSales = Sale::with(['customer', 'cashTransaction', 'project'])
            ->latestFirst()
            ->limit(10)
            ->get();

        $recentPurchases = Purchase::with(['supplier', 'cashTransaction'])
            ->latestFirst()
            ->limit(5)
            ->get();

        $recentKas = CashTransaction::with(['chartOfAccount', 'sale', 'purchase', 'project'])
            ->latestFirst()
            ->limit(8)
            ->get();

        return view('admin.keuangan.dashboard', compact(
            'stats',
            'grafikBulanan',
            'recentSales',
            'recentPurchases',
            'recentKas'
        ));
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
        $customers = Customer::active()->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        $nextInvoiceNumber = Sale::generateInvoiceNumber();

        return view('admin.keuangan.transaksi.penjualan', compact(
            'sales',
            'customers',
            'taxes',
            'nextInvoiceNumber'
        ));
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = trim((string) $request->input('search', ''));
        $type = $request->input('type'); // debit|credit|null

        $applyFilters = function ($query) use ($startDate, $endDate, $search, $type) {
            if ($startDate) {
                $query->whereDate('transaction_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('transaction_date', '<=', $endDate);
            }
            if (in_array($type, ['debit', 'credit'], true)) {
                $query->where('transaction_type', $type);
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('chartOfAccount', function ($coa) use ($search) {
                            $coa->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('sale', function ($sale) use ($search) {
                            $sale->where('invoice_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('purchase', function ($purchase) use ($search) {
                            $purchase->where('invoice_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('project', function ($project) use ($search) {
                            $project->where('code', 'like', "%{$search}%")
                                ->orWhere('title', 'like', "%{$search}%");
                        });
                });
            }

            return $query;
        };

        $kasMasuk = (float) $applyFilters(CashTransaction::query())->where('transaction_type', 'debit')->sum('amount');
        $kasKeluar = (float) $applyFilters(CashTransaction::query())->where('transaction_type', 'credit')->sum('amount');
        $arusKasBersih = $kasMasuk - $kasKeluar;
        $jumlahTransaksi = $applyFilters(CashTransaction::query())->count();

        $transactions = $applyFilters(
            CashTransaction::with(['chartOfAccount', 'sale', 'purchase', 'project'])->latestFirst()
        )->paginate(25)->withQueryString();

        return view('admin.keuangan.laporan.arus-kas', compact(
            'transactions',
            'startDate',
            'endDate',
            'search',
            'type',
            'kasMasuk',
            'kasKeluar',
            'arusKasBersih',
            'jumlahTransaksi'
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
