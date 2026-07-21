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
Route::get('/berita', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
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
    Route::put('/site-logo/letterhead', [\App\Http\Controllers\Admin\SiteLogoController::class, 'updateLetterhead'])->name('site-logo.update-letterhead');
    Route::patch('/site-logo/size', [\App\Http\Controllers\Admin\SiteLogoController::class, 'updateSize'])->name('site-logo.update-size');
    Route::delete('/site-logo', [\App\Http\Controllers\Admin\SiteLogoController::class, 'destroy'])->name('site-logo.destroy');
    
    // Resource routes
    Route::resource('case-studies', AdminCaseStudyController::class);
    Route::resource('slides', \App\Http\Controllers\Admin\SlideController::class);
    Route::resource('hero-texts', \App\Http\Controllers\Admin\HeroTextController::class);
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('contributors', \App\Http\Controllers\Admin\ContributorController::class);
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->except(['show']);
    
    // Accounting/Bookkeeping routes
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class);
    Route::post('/customer-types', [\App\Http\Controllers\Admin\CustomerTypeController::class, 'store'])->name('customer-types.store');
    Route::put('/customer-types/{customerType}', [\App\Http\Controllers\Admin\CustomerTypeController::class, 'update'])->name('customer-types.update');
    Route::delete('/customer-types/{customerType}', [\App\Http\Controllers\Admin\CustomerTypeController::class, 'destroy'])->name('customer-types.destroy');
    Route::post('/customer-categories', [\App\Http\Controllers\Admin\CustomerCategoryController::class, 'store'])->name('customer-categories.store');
    Route::put('/customer-categories/{customerCategory}', [\App\Http\Controllers\Admin\CustomerCategoryController::class, 'update'])->name('customer-categories.update');
    Route::delete('/customer-categories/{customerCategory}', [\App\Http\Controllers\Admin\CustomerCategoryController::class, 'destroy'])->name('customer-categories.destroy');
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);
    Route::patch('/projects/{project}/status', [\App\Http\Controllers\Admin\ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::post('/projects/{project}/terms/{term}/pay', [\App\Http\Controllers\Admin\ProjectController::class, 'payTerm'])->name('projects.pay-term');
    Route::post('/projects/{project}/terms/{term}/unpay', [\App\Http\Controllers\Admin\ProjectController::class, 'unpayTerm'])->name('projects.unpay-term');
    Route::post('/projects/{project}/sale-transactions', [\App\Http\Controllers\Admin\ProjectController::class, 'attachSaleTransaction'])->name('projects.sale-transactions.attach');
    Route::delete('/projects/{project}/sale-transactions/{transaction}', [\App\Http\Controllers\Admin\ProjectController::class, 'detachSaleTransaction'])->name('projects.sale-transactions.detach');
    Route::resource('projects.assignment-letters', \App\Http\Controllers\Admin\AssignmentLetterController::class)
        ->parameters(['assignment-letters' => 'letter']);
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::get('/sales/{id}/invoice', [\App\Http\Controllers\Admin\SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('/sales/{id}/whatsapp', [\App\Http\Controllers\Admin\SaleController::class, 'whatsapp'])->name('sales.whatsapp');
    Route::get('/sales/pos/catalog', [\App\Http\Controllers\Admin\SaleController::class, 'posCatalog'])->name('sales.pos.catalog');
    Route::post('/sales/pos/checkout', [\App\Http\Controllers\Admin\SaleController::class, 'posCheckout'])->name('sales.pos.checkout');
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
        Route::get('/sale-transactions/purchase/{purchaseId}', [\App\Http\Controllers\Admin\SaleTransactionController::class, 'purchaseDetails'])->name('sale-transactions.purchase-details');
        Route::resource('sale-transactions', \App\Http\Controllers\Admin\SaleTransactionController::class);
        Route::get('/transaksi/pembelian', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiPembelian'])->name('transaksi.pembelian');
        Route::get('/transaksi/project', fn () => redirect()->route('admin.projects.index'))->name('transaksi.project');
        Route::get('/transaksi/kas-bank', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiKasBank'])->name('transaksi.kas-bank');
        Route::get('/transaksi/gaji', [\App\Http\Controllers\Admin\KeuanganController::class, 'transaksiGaji'])->name('transaksi.gaji');
        Route::get('/jurnal', [\App\Http\Controllers\Admin\KeuanganController::class, 'jurnal'])->name('jurnal.index');
        Route::get('/laporan/neraca', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanNeraca'])->name('laporan.neraca');
        Route::get('/laporan/laba-rugi', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanLabaRugi'])->name('laporan.laba-rugi');
        Route::get('/laporan/arus-kas', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanArusKas'])->name('laporan.arus-kas');
        Route::get('/laporan/buku-besar', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanBukuBesar'])->name('laporan.buku-besar');
        Route::get('/pajak', [\App\Http\Controllers\Admin\TaxController::class, 'index'])->name('pajak.index');
        Route::post('/pajak', [\App\Http\Controllers\Admin\TaxController::class, 'store'])->name('pajak.store');
        Route::put('/pajak/{tax}', [\App\Http\Controllers\Admin\TaxController::class, 'update'])->name('pajak.update');
        Route::delete('/pajak/{tax}', [\App\Http\Controllers\Admin\TaxController::class, 'destroy'])->name('pajak.destroy');
        Route::get('/laporan-pajak', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanPajak'])->name('laporan-pajak.index');
        Route::get('/utility', [\App\Http\Controllers\Admin\KeuanganController::class, 'utility'])->name('utility.index');
    });
    
    // Visitor statistics
    Route::get('/visitors', [\App\Http\Controllers\Admin\VisitorController::class, 'index'])->name('visitors.index');
    
    // Halaman Kontak (publik)
    Route::get('/contact', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contact.index');
    Route::put('/contact/settings', [\App\Http\Controllers\Admin\ContactController::class, 'updateSettings'])->name('contact.settings.update');
    Route::get('/contact/messages/{id}', [\App\Http\Controllers\Admin\ContactController::class, 'showMessage'])->name('contact.messages.show');
    Route::post('/contact/messages/{id}/respond', [\App\Http\Controllers\Admin\ContactController::class, 'respond'])->name('contact.messages.respond');
    Route::post('/contact/messages/{id}/mark-read', [\App\Http\Controllers\Admin\ContactController::class, 'markAsRead'])->name('contact.messages.mark-read');
    Route::post('/contact/messages/{id}/mark-unread', [\App\Http\Controllers\Admin\ContactController::class, 'markAsUnread'])->name('contact.messages.mark-unread');
    Route::delete('/contact/messages/{id}', [\App\Http\Controllers\Admin\ContactController::class, 'destroyMessage'])->name('contact.messages.destroy');

    // Legacy redirects
    Route::redirect('/contact-messages', '/admin/contact')->name('contact-messages.index');
    Route::get('/contact-messages/{id}', fn (string $id) => redirect()->route('admin.contact.messages.show', $id))->name('contact-messages.show');
    Route::post('/contact-messages/{id}/mark-read', [\App\Http\Controllers\Admin\ContactController::class, 'markAsRead'])->name('contact-messages.mark-read');
    Route::post('/contact-messages/{id}/mark-unread', [\App\Http\Controllers\Admin\ContactController::class, 'markAsUnread'])->name('contact-messages.mark-unread');
    Route::delete('/contact-messages/{id}', [\App\Http\Controllers\Admin\ContactController::class, 'destroyMessage'])->name('contact-messages.destroy');
    
    // User management (only for super admin and admin)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});
