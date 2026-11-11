<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PublicSaleController;
use App\Http\Controllers\PublicProjectReviewController;
use App\Http\Controllers\PublicSaleReviewController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::post('/services/checkout', [ServicesController::class, 'checkout'])
    ->middleware('throttle:10,1')
    ->name('services.checkout');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/penjualan', [PublicSaleController::class, 'index'])->name('sales.public.index');
Route::get('/penjualan/{sale}', [PublicSaleController::class, 'show'])->name('sales.public.show');

// Ulasan project oleh customer (verifikasi HP seperti invoice)
Route::get('/ulasan/project/{project}', [PublicProjectReviewController::class, 'show'])
    ->name('project.review.show');
Route::post('/ulasan/project/{project}/verify', [PublicProjectReviewController::class, 'verify'])
    ->middleware('throttle:8,1')
    ->name('project.review.verify');
Route::post('/ulasan/project/{project}', [PublicProjectReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('project.review.store');
Route::delete('/ulasan/project/{project}/media/{media}', [PublicProjectReviewController::class, 'destroyMedia'])
    ->middleware('throttle:20,1')
    ->name('project.review.media.destroy');

// Ulasan faktur penjualan non-project
Route::get('/ulasan/faktur/{sale}', [PublicSaleReviewController::class, 'show'])
    ->name('sale.review.show');
Route::post('/ulasan/faktur/{sale}/verify', [PublicSaleReviewController::class, 'verify'])
    ->middleware('throttle:8,1')
    ->name('sale.review.verify');
Route::post('/ulasan/faktur/{sale}', [PublicSaleReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('sale.review.store');
Route::delete('/ulasan/faktur/{sale}/media/{media}', [PublicSaleReviewController::class, 'destroyMedia'])
    ->middleware('throttle:20,1')
    ->name('sale.review.media.destroy');

Route::get('/berita', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Public project term billing invoice (belum lunas) — harus di atas /invoice/{sale}
Route::get('/invoice/term/{term}', [\App\Http\Controllers\PublicProjectTermInvoiceController::class, 'show'])
    ->name('invoice.project-terms.show');
Route::post('/invoice/term/{term}/verify', [\App\Http\Controllers\PublicProjectTermInvoiceController::class, 'verify'])
    ->middleware('throttle:8,1')
    ->name('invoice.project-terms.verify');

// Invoice modal investor (harus sebelum /invoice/{sale})
Route::get('/invoice/investor/{injection}', [\App\Http\Controllers\PublicInvestorInvoiceController::class, 'show'])
    ->middleware('signed')
    ->name('invoice.investor.show');
Route::post('/invoice/investor/{injection}/verify', [\App\Http\Controllers\PublicInvestorInvoiceController::class, 'verify'])
    ->middleware(['signed', 'throttle:8,1'])
    ->name('invoice.investor.verify');
Route::post('/invoice/investor/{injection}/approve', [\App\Http\Controllers\PublicInvestorInvoiceController::class, 'approve'])
    ->middleware(['signed', 'throttle:8,1'])
    ->name('invoice.investor.approve');
Route::post('/invoice/investor/{injection}/pay', [\App\Http\Controllers\PublicInvestorInvoiceController::class, 'pay'])
    ->middleware(['signed', 'throttle:8,1'])
    ->name('invoice.investor.pay');
Route::post('/invoice/investor/{injection}/reject', [\App\Http\Controllers\PublicInvestorInvoiceController::class, 'reject'])
    ->middleware(['signed', 'throttle:8,1'])
    ->name('invoice.investor.reject');

// Public sales invoice — akses lewat verifikasi nomor HP customer
Route::get('/invoice/{sale}', [\App\Http\Controllers\PublicSaleInvoiceController::class, 'show'])
    ->name('invoice.sales.show');
Route::post('/invoice/{sale}/verify', [\App\Http\Controllers\PublicSaleInvoiceController::class, 'verify'])
    ->middleware('throttle:8,1')
    ->name('invoice.sales.verify');

Route::get('/investor-portal', [\App\Http\Controllers\InvestorPortalController::class, 'loginForm'])->name('investor.portal.login');
Route::post('/investor-portal/login', [\App\Http\Controllers\InvestorPortalController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('investor.portal.login.submit');
Route::post('/investor-portal/logout', [\App\Http\Controllers\InvestorPortalController::class, 'logout'])->name('investor.portal.logout');
Route::get('/investor-portal/dashboard', [\App\Http\Controllers\InvestorPortalController::class, 'dashboard'])->name('investor.portal.dashboard');
Route::get('/investor-portal/documents/{document}/download', [\App\Http\Controllers\InvestorPortalController::class, 'downloadDocument'])
    ->name('investor.portal.documents.download');

// Path lama tetap di luar grup auth supaya link yang sudah dikirim ke customer tidak jatuh ke login.
Route::get('/admin/sales/{id}/invoice', [\App\Http\Controllers\Admin\SaleController::class, 'invoice'])
    ->name('admin.sales.invoice');

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
    Route::get('/home-theme', [\App\Http\Controllers\Admin\HomeThemeController::class, 'edit'])->name('home-theme.edit');
    Route::put('/home-theme', [\App\Http\Controllers\Admin\HomeThemeController::class, 'update'])->name('home-theme.update');
    
    // Resource routes
    Route::resource('slides', \App\Http\Controllers\Admin\SlideController::class);
    Route::resource('hero-texts', \App\Http\Controllers\Admin\HeroTextController::class);
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('contributors', \App\Http\Controllers\Admin\ContributorController::class);
    Route::resource('github-teams', \App\Http\Controllers\Admin\GithubTeamController::class)->except(['show']);
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
    Route::patch('/projects/{project}/layanan', [\App\Http\Controllers\Admin\ProjectController::class, 'updateLayanan'])->name('projects.update-layanan');
    Route::post('/projects/{project}/layanan/confirm', [\App\Http\Controllers\Admin\ProjectController::class, 'confirmLayanan'])->name('projects.layanan.confirm');
    Route::post('/projects/{project}/layanan/reject', [\App\Http\Controllers\Admin\ProjectController::class, 'rejectLayanan'])->name('projects.layanan.reject');
    Route::patch('/projects/{project}/status', [\App\Http\Controllers\Admin\ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::patch('/projects/{project}/progress-media/{media}/portfolio', [\App\Http\Controllers\Admin\ProjectController::class, 'toggleProgressMediaPortfolio'])->name('projects.progress-media.portfolio');
    Route::delete('/projects/{project}/progress-media/{media}', [\App\Http\Controllers\Admin\ProjectController::class, 'destroyProgressMedia'])->name('projects.progress-media.destroy');
    Route::patch('/projects/{project}/reviews/{review}/publish', [\App\Http\Controllers\Admin\ProjectController::class, 'toggleReviewPublish'])->name('projects.reviews.publish');
    Route::delete('/projects/{project}/reviews/{review}', [\App\Http\Controllers\Admin\ProjectController::class, 'destroyReview'])->name('projects.reviews.destroy');
    Route::patch('/projects/{project}/discount', [\App\Http\Controllers\Admin\ProjectController::class, 'updateDiscount'])->name('projects.update-discount');
    Route::patch('/projects/{project}/jasa-items', [\App\Http\Controllers\Admin\ProjectController::class, 'updateJasaItems'])->name('projects.jasa-items.update');
    Route::get('/projects/{project}/terms/edit', [\App\Http\Controllers\Admin\ProjectController::class, 'editTerms'])->name('projects.terms.edit');
    Route::patch('/projects/{project}/terms', [\App\Http\Controllers\Admin\ProjectController::class, 'updateTerms'])->name('projects.terms.update');
    Route::post('/projects/{project}/terms/{term}/pay', [\App\Http\Controllers\Admin\ProjectController::class, 'payTerm'])->name('projects.pay-term');
    Route::post('/projects/{project}/terms/{term}/unpay', [\App\Http\Controllers\Admin\ProjectController::class, 'unpayTerm'])->name('projects.unpay-term');
    Route::get('/projects/{project}/terms/{term}/invoice', [\App\Http\Controllers\Admin\ProjectController::class, 'termInvoice'])->name('projects.term-invoice');
    Route::get('/projects/{project}/terms/{term}/whatsapp', [\App\Http\Controllers\Admin\ProjectController::class, 'whatsappTerm'])->name('projects.whatsapp-term');
    Route::post('/projects/{project}/sale-transactions', [\App\Http\Controllers\Admin\ProjectController::class, 'attachSaleTransaction'])->name('projects.sale-transactions.attach');
    Route::delete('/projects/{project}/sale-transactions/{transaction}', [\App\Http\Controllers\Admin\ProjectController::class, 'detachSaleTransaction'])->name('projects.sale-transactions.detach');
    Route::resource('projects.assignment-letters', \App\Http\Controllers\Admin\AssignmentLetterController::class)
        ->parameters(['assignment-letters' => 'letter']);
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::get('/sales/{id}/whatsapp', [\App\Http\Controllers\Admin\SaleController::class, 'whatsapp'])->name('sales.whatsapp');
    Route::get('/sales/pos/catalog', [\App\Http\Controllers\Admin\SaleController::class, 'posCatalog'])->name('sales.pos.catalog');
    Route::post('/sales/pos/checkout', [\App\Http\Controllers\Admin\SaleController::class, 'posCheckout'])->name('sales.pos.checkout');
    Route::patch('/sales/{sale}/public', [\App\Http\Controllers\Admin\SaleController::class, 'togglePublic'])->name('sales.toggle-public');
    Route::post('/sales/{sale}/layanan/confirm', [\App\Http\Controllers\Admin\SaleController::class, 'confirmLayanan'])->name('sales.layanan.confirm');
    Route::post('/sales/{sale}/layanan/reject', [\App\Http\Controllers\Admin\SaleController::class, 'rejectLayanan'])->name('sales.layanan.reject');
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
        Route::get('/order-layanan', [\App\Http\Controllers\Admin\KeuanganController::class, 'orderLayanan'])->name('order-layanan');
        Route::get('/sale-transactions/purchase/{purchaseId}', [\App\Http\Controllers\Admin\SaleTransactionController::class, 'purchaseDetails'])->name('sale-transactions.purchase-details');
        Route::patch('/sale-transactions/{sale_transaction}/layanan', [\App\Http\Controllers\Admin\SaleTransactionController::class, 'updateLayanan'])->name('sale-transactions.update-layanan');
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
        Route::get('/diskon', [\App\Http\Controllers\Admin\DiscountController::class, 'index'])->name('diskon.index');
        Route::post('/diskon', [\App\Http\Controllers\Admin\DiscountController::class, 'store'])->name('diskon.store');
        Route::put('/diskon/{diskon}', [\App\Http\Controllers\Admin\DiscountController::class, 'update'])->name('diskon.update');
        Route::delete('/diskon/{diskon}', [\App\Http\Controllers\Admin\DiscountController::class, 'destroy'])->name('diskon.destroy');
        Route::get('/jasa', [\App\Http\Controllers\Admin\JasaController::class, 'index'])->name('jasa.index');
        Route::post('/jasa', [\App\Http\Controllers\Admin\JasaController::class, 'store'])->name('jasa.store');
        Route::put('/jasa/{jasa}', [\App\Http\Controllers\Admin\JasaController::class, 'update'])->name('jasa.update');
        Route::delete('/jasa/{jasa}', [\App\Http\Controllers\Admin\JasaController::class, 'destroy'])->name('jasa.destroy');
        Route::get('/sumber-dana', [\App\Http\Controllers\Admin\SumberDanaController::class, 'index'])->name('sumber-dana.index');
        Route::post('/sumber-dana', [\App\Http\Controllers\Admin\SumberDanaController::class, 'store'])->name('sumber-dana.store');
        Route::put('/sumber-dana/{sumberDana}', [\App\Http\Controllers\Admin\SumberDanaController::class, 'update'])->name('sumber-dana.update');
        Route::delete('/sumber-dana/{sumberDana}', [\App\Http\Controllers\Admin\SumberDanaController::class, 'destroy'])->name('sumber-dana.destroy');

        Route::get('/investor', [\App\Http\Controllers\Admin\InvestorController::class, 'index'])->name('investor.index');
        Route::post('/investor', [\App\Http\Controllers\Admin\InvestorController::class, 'store'])->name('investor.store');
        Route::put('/investor/{investor}', [\App\Http\Controllers\Admin\InvestorController::class, 'update'])->name('investor.update');
        Route::delete('/investor/{investor}', [\App\Http\Controllers\Admin\InvestorController::class, 'destroy'])->name('investor.destroy');
        Route::post('/investor/{investor}/documents', [\App\Http\Controllers\Admin\InvestorController::class, 'storeDocument'])->name('investor.documents.store');
        Route::delete('/investor-documents/{document}', [\App\Http\Controllers\Admin\InvestorController::class, 'destroyDocument'])->name('investor.documents.destroy');

        Route::get('/benefit-investor', [\App\Http\Controllers\Admin\InvestorBenefitPackageController::class, 'index'])->name('benefit-investor.index');
        Route::post('/benefit-investor', [\App\Http\Controllers\Admin\InvestorBenefitPackageController::class, 'store'])->name('benefit-investor.store');
        Route::put('/benefit-investor/{package}', [\App\Http\Controllers\Admin\InvestorBenefitPackageController::class, 'update'])->name('benefit-investor.update');
        Route::delete('/benefit-investor/{package}', [\App\Http\Controllers\Admin\InvestorBenefitPackageController::class, 'destroy'])->name('benefit-investor.destroy');

        Route::get('/modal-investor', [\App\Http\Controllers\Admin\InvestorCapitalInjectionController::class, 'index'])->name('modal-investor.index');
        Route::post('/modal-investor', [\App\Http\Controllers\Admin\InvestorCapitalInjectionController::class, 'store'])->name('modal-investor.store');
        Route::put('/modal-investor/payment-account', [\App\Http\Controllers\Admin\InvestorCapitalInjectionController::class, 'updatePaymentAccount'])->name('modal-investor.payment-account');
        Route::put('/modal-investor/{injection}', [\App\Http\Controllers\Admin\InvestorCapitalInjectionController::class, 'update'])->name('modal-investor.update');
        Route::delete('/modal-investor/{injection}', [\App\Http\Controllers\Admin\InvestorCapitalInjectionController::class, 'destroy'])->name('modal-investor.destroy');

        Route::get('/dividen-investor', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'index'])->name('dividen-investor.index');
        Route::post('/dividen-investor', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'store'])->name('dividen-investor.store');
        Route::get('/dividen-investor/{period}', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'show'])->name('dividen-investor.show');
        Route::post('/dividen-investor/{period}/calculate', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'calculate'])->name('dividen-investor.calculate');
        Route::post('/dividen-investor/{period}/pay-all', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'payAll'])->name('dividen-investor.pay-all');
        Route::put('/dividen-allocations/{allocation}', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'updateAllocation'])->name('dividen-investor.allocations.update');
        Route::post('/dividen-allocations/{allocation}/pay', [\App\Http\Controllers\Admin\InvestorDividendController::class, 'payAllocation'])->name('dividen-investor.allocations.pay');

        Route::get('/laporan/sumber-dana', [\App\Http\Controllers\Admin\KeuanganController::class, 'laporanSumberDana'])->name('laporan.sumber-dana');
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
