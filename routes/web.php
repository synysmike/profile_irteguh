<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\CaseStudyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CaseStudyController as AdminCaseStudyController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/case-study/{slug}', [CaseStudyController::class, 'show'])->name('case-study.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes (Protected)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Site logo & app name (edit/update/destroy/size)
    Route::get('/site-logo', [\App\Http\Controllers\Admin\SiteLogoController::class, 'edit'])->name('site-logo.edit');
    Route::put('/site-logo', [\App\Http\Controllers\Admin\SiteLogoController::class, 'update'])->name('site-logo.update');
    Route::put('/site-logo/app-name', [\App\Http\Controllers\Admin\SiteLogoController::class, 'updateAppName'])->name('site-logo.update-app-name');
    Route::patch('/site-logo/size', [\App\Http\Controllers\Admin\SiteLogoController::class, 'updateSize'])->name('site-logo.update-size');
    Route::delete('/site-logo', [\App\Http\Controllers\Admin\SiteLogoController::class, 'destroy'])->name('site-logo.destroy');
    
    // Resource routes
    Route::resource('case-studies', AdminCaseStudyController::class);
    Route::resource('slides', \App\Http\Controllers\Admin\SlideController::class);
    Route::resource('hero-texts', \App\Http\Controllers\Admin\HeroTextController::class);
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('contributors', \App\Http\Controllers\Admin\ContributorController::class);
    
    // Accounting/Bookkeeping routes
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class);
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::get('/sales/{id}/invoice', [\App\Http\Controllers\Admin\SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('/sales/pending-transactions/list', [\App\Http\Controllers\Admin\SaleController::class, 'pendingTransactionsList'])->name('sales.pending-transactions.list');
    Route::post('/sales/pending-transactions', [\App\Http\Controllers\Admin\SaleController::class, 'addPendingTransaction'])->name('sales.pending-transactions.add');
    Route::delete('/sales/pending-transactions/clear', [\App\Http\Controllers\Admin\SaleController::class, 'clearPendingTransactions'])->name('sales.pending-transactions.clear');
    Route::delete('/sales/pending-transactions/{id}', [\App\Http\Controllers\Admin\SaleController::class, 'removePendingTransaction'])->name('sales.pending-transactions.remove');
    Route::resource('sales', \App\Http\Controllers\Admin\SaleController::class);
    Route::get('/purchases/{id}/invoice', [\App\Http\Controllers\Admin\PurchaseController::class, 'invoice'])->name('purchases.invoice');
    Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class);
    Route::resource('journal-entries', \App\Http\Controllers\Admin\JournalEntryController::class);
    Route::resource('cash-transactions', \App\Http\Controllers\Admin\CashTransactionController::class);
    Route::get('/cash-transactions/{id}/download', [\App\Http\Controllers\Admin\CashTransactionController::class, 'download'])->name('cash-transactions.download');

    // Keuangan (Pembukuan & Pajak) - menu lengkap
    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\KeuanganController::class, 'dashboard'])->name('dashboard');
        Route::get('/master/akun', fn () => redirect()->route('admin.keuangan.chart-of-accounts.index'))->name('master.akun');
        Route::resource('chart-of-accounts', \App\Http\Controllers\Admin\ChartOfAccountController::class);
        Route::get('/master/klien-vendor', [\App\Http\Controllers\Admin\KeuanganController::class, 'masterKlienVendor'])->name('master.klien-vendor');
        Route::get('/master/karyawan', [\App\Http\Controllers\Admin\KeuanganController::class, 'masterKaryawan'])->name('master.karyawan');
        Route::get('/transaksi/penjualan', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiPenjualan'])->name('transaksi.penjualan');
        Route::resource('sale-transactions', \App\Http\Controllers\Admin\SaleTransactionController::class);
        Route::get('/transaksi/pembelian', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiPembelian'])->name('transaksi.pembelian');
        Route::get('/transaksi/kas-bank', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiKasBank'])->name('transaksi.kas-bank');
        Route::get('/transaksi/gaji', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiGaji'])->name('transaksi.gaji');
        // Sale Transactions (CRUD transaksi template)
        Route::resource('sale-transactions', \App\Http\Controllers\Admin\SaleTransactionController::class);
        Route::get('/jurnal', [\App\Http\Controllers\Admin\KeuanganController::class, 'jurnal'])->name('jurnal.index');
        Route::get('/laporan/neraca', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanNeraca'])->name('laporan.neraca');
        Route::get('/laporan/laba-rugi', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanLabaRugi'])->name('laporan.laba-rugi');
        Route::get('/laporan/arus-kas', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanArusKas'])->name('laporan.arus-kas');
        Route::get('/laporan/buku-besar', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanBukuBesar'])->name('laporan.buku-besar');
        Route::get('/pajak/pph-badan', [\App\Http\Controllers\Admin\KeuanganController::class, 'pajakPphBadan'])->name('pajak.pph-badan');
        Route::get('/pajak/pph-21', [\App\Http\Controllers\Admin\KeuanganController::class, 'pajakPph21'])->name('pajak.pph-21');
        Route::get('/pajak/ppn', [\App\Http\Controllers\Admin\KeuanganController::class, 'pajakPpn'])->name('pajak.ppn');
        Route::get('/laporan-pajak', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanPajak'])->name('laporan-pajak.index');
        Route::get('/utility', [\App\Http\Controllers\Admin\KeuanganController::class, 'utility'])->name('utility.index');
    });
    
    // Visitor statistics
    Route::get('/visitors', [\App\Http\Controllers\Admin\VisitorController::class, 'index'])->name('visitors.index');
    
    // Contact messages
    Route::get('/contact-messages', [\App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{id}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::post('/contact-messages/{id}/mark-read', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsRead'])->name('contact-messages.mark-read');
    Route::post('/contact-messages/{id}/mark-unread', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsUnread'])->name('contact-messages.mark-unread');
    Route::delete('/contact-messages/{id}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    
    // User management (only for super admin and admin)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});
