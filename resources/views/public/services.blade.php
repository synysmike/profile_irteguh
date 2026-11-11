@extends('public.layouts.app')

@section('title', 'Layanan Kami - Ir Teguh Solution')

@section('content')
<section class="pb-28 pt-6 md:pt-10" id="layanan-marketplace">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            {{-- Marketplace top bar --}}
            <div class="glass-card rounded-2xl p-4 md:p-5 mb-5 sticky top-2 z-30">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="min-w-0">
                        <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight">Layanan Kami</h1>
                        <p class="text-sm text-white/60 mt-1">Jelajahi produk & jasa — pesan mudah seperti marketplace</p>
                    </div>
                    <button type="button" id="btn-open-cart" class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.4.4-.1 1.1.4 1.1H19M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span class="text-sm font-medium">Keranjang</span>
                        <span id="cart-badge" class="absolute -top-1.5 -right-1.5 min-w-[1.25rem] h-5 px-1 rounded-md bg-purple-500 text-[11px] font-bold flex items-center justify-center hidden">0</span>
                    </button>
                </div>

                <div class="relative">
                    <svg class="w-4 h-4 text-white/40 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                    </svg>
                    <input type="search" id="catalog-search" placeholder="Cari produk atau jasa..."
                           class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/10 border border-white/15 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-purple-400/60">
                </div>
            </div>

            {{-- Category chips from services master --}}
            <div class="mb-5 overflow-x-auto pb-1 -mx-1 px-1">
                <div class="flex gap-2 min-w-max" id="category-chips">
                    <button type="button" data-service-id="all" class="cat-chip px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white border border-purple-400/40">
                        Semua
                    </button>
                    @foreach($services as $service)
                    <button type="button" data-service-id="{{ $service->id }}"
                            class="cat-chip px-4 py-2 rounded-xl text-sm font-medium bg-white/5 text-white/80 border border-white/10 hover:bg-white/10 transition">
                        @if($service->icon)<span class="mr-1">{{ $service->icon }}</span>@endif{{ $service->title }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Product grid --}}
            <div id="catalog-empty" class="{{ $catalog->isEmpty() ? '' : 'hidden' }} glass-card rounded-xl p-10 text-center text-white/60">
                @if($catalog->isEmpty())
                    Belum ada produk/jasa yang dipajang. Admin dapat menampilkan alokasi stok atau project dari panel admin.
                @else
                    Tidak ada item yang cocok dengan pencarian/filter.
                @endif
            </div>

            <div id="catalog-grid" class="{{ $catalog->isEmpty() ? 'hidden' : '' }} grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                @foreach($catalog as $item)
                <article class="catalog-card glass-card rounded-xl overflow-hidden flex flex-col group"
                         data-id="{{ $item['id'] }}"
                         data-service-id="{{ $item['service_id'] }}"
                         data-name="{{ $item['name'] }}"
                         data-price="{{ $item['price'] }}"
                         data-unit="{{ $item['unit'] }}"
                         data-type="{{ $item['source_type'] }}"
                         data-max-qty="{{ $item['max_qty'] }}"
                         data-service-title="{{ $item['service_title'] }}"
                         data-search="{{ strtolower($item['name'] . ' ' . $item['description'] . ' ' . $item['service_title'] . ' ' . $item['source_type']) }}">
                    <div class="relative aspect-square bg-gradient-to-br from-white/10 via-purple-500/10 to-white/5 flex items-center justify-center">
                        @if($item['service_icon'])
                        <div class="text-4xl md:text-5xl opacity-80 select-none">{{ $item['service_icon'] }}</div>
                        @else
                        <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center text-white/70 text-xl font-bold tracking-wide">
                            {{ strtoupper(mb_substr($item['name'], 0, 1)) }}
                        </div>
                        @endif
                        <span class="absolute top-2 left-2 text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-md bg-black/40 text-white/90 border border-white/10">
                            {{ $item['source_type'] }}
                        </span>
                    </div>
                    <div class="p-3 flex flex-col flex-1 min-w-0">
                        <div class="text-[11px] text-purple-200/80 truncate mb-1">{{ $item['service_title'] }}</div>
                        <h3 class="text-sm md:text-[15px] font-semibold text-white leading-snug mb-2 break-words overflow-hidden [display:-webkit-box] [-webkit-box-orient:vertical] [-webkit-line-clamp:2] min-h-[2.5rem]">
                            {{ $item['name'] }}
                        </h3>
                        <p class="text-[11px] text-white/45 mb-2 overflow-hidden [display:-webkit-box] [-webkit-box-orient:vertical] [-webkit-line-clamp:2] hidden md:block">
                            {{ $item['description'] }}
                        </p>
                        @if($item['rating'] !== null || $item['sold'] !== null)
                        <div class="flex items-center gap-1 text-[11px] text-white/55 mb-2">
                            @if($item['rating'] !== null)
                            <span class="text-yellow-300 tabular-nums">★ {{ number_format((float) $item['rating'], 1) }}</span>
                            @endif
                            @if($item['rating'] !== null && $item['sold'] !== null)
                            <span>·</span>
                            @endif
                            @if($item['sold'] !== null)
                            <span>{{ number_format((int) $item['sold'], 0, ',', '.') }} terjual</span>
                            @endif
                        </div>
                        @elseif($item['source_type'] === 'produk' && !empty($item['max_qty']))
                        <div class="text-[11px] text-white/45 mb-2">Stok {{ number_format((int) $item['max_qty'], 0, ',', '.') }}</div>
                        @endif
                        <div class="mt-auto">
                            <div class="text-purple-200 font-bold text-base md:text-lg tabular-nums leading-tight">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-white/40 mb-3">/ {{ $item['unit'] }}</div>
                            <button type="button"
                                    class="btn-add-cart w-full px-3 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold transition">
                                + Keranjang
                            </button>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <p class="mt-6 text-center text-xs text-white/35">
                Checkout menyimpan Customer + draft pesanan, lalu membuka WhatsApp admin.
            </p>
        </div>
    </div>
</section>

{{-- Mobile floating cart --}}
<button type="button" id="fab-cart" class="md:hidden fixed bottom-5 right-4 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-900/40 border border-purple-300/20">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.4.4-.1 1.1.4 1.1H19M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
    <span class="text-sm font-semibold">Keranjang</span>
    <span id="fab-cart-badge" class="min-w-[1.25rem] h-5 px-1 rounded-md bg-white text-purple-700 text-[11px] font-bold flex items-center justify-center">0</span>
</button>

{{-- Cart / Checkout drawer --}}
<div id="cart-overlay" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-close-cart></div>
    <div id="cart-panel" class="absolute right-0 top-0 h-full w-full max-w-md bg-[#1a1430]/95 border-l border-white/10 shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
        <div class="flex items-center justify-between gap-3 p-4 border-b border-white/10">
            <div>
                <h2 id="cart-panel-title" class="text-lg font-bold text-white">Keranjang</h2>
                <p class="text-xs text-white/50" id="cart-panel-subtitle">Kelola item sebelum checkout</p>
            </div>
            <button type="button" class="p-2 text-white/60 hover:text-white" data-close-cart aria-label="Tutup">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="cart-view" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div id="cart-items" class="space-y-3"></div>
            <p id="cart-empty" class="text-sm text-white/50 text-center py-10">Keranjang masih kosong.</p>
        </div>

        <div id="checkout-view" class="flex-1 overflow-y-auto p-4 space-y-4 hidden">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <div class="text-xs uppercase tracking-wide text-white/45 mb-2">Ringkasan</div>
                <div id="checkout-summary" class="text-sm text-white/80 space-y-1"></div>
            </div>

            <div>
                <label class="block text-xs font-medium text-white/70 mb-1" for="co-type">Tipe Customer *</label>
                <select id="co-type" required
                        class="w-full px-3 py-2.5 rounded-lg bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-purple-400/50">
                    <option value="" class="text-gray-900">Pilih tipe…</option>
                    @foreach($customerTypes as $type)
                    <option value="{{ $type->id }}" data-legacy="{{ $type->resolveLegacyKey() }}" class="text-gray-900">
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div id="co-company-wrap" class="hidden">
                <label class="block text-xs font-medium text-white/70 mb-1" for="co-company">Nama Perusahaan / Lembaga</label>
                <input id="co-company" type="text" placeholder="Nama perusahaan / organisasi / sekolah"
                       class="w-full px-3 py-2.5 rounded-lg bg-white/10 border border-white/15 text-white placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-purple-400/50">
            </div>
            <div>
                <label class="block text-xs font-medium text-white/70 mb-1" for="co-name">Nama Lengkap *</label>
                <input id="co-name" type="text" required placeholder="Nama sesuai identitas"
                       class="w-full px-3 py-2.5 rounded-lg bg-white/10 border border-white/15 text-white placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-purple-400/50">
            </div>
            <div>
                <label class="block text-xs font-medium text-white/70 mb-1" for="co-phone">No. WhatsApp *</label>
                <input id="co-phone" type="tel" required placeholder="08xxxxxxxxxx"
                       class="w-full px-3 py-2.5 rounded-lg bg-white/10 border border-white/15 text-white placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-purple-400/50">
            </div>
            <div>
                <label class="block text-xs font-medium text-white/70 mb-1" for="co-address">Alamat / Catatan *</label>
                <textarea id="co-address" rows="3" required placeholder="Alamat pengiriman atau catatan order"
                          class="w-full px-3 py-2.5 rounded-lg bg-white/10 border border-white/15 text-white placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-purple-400/50"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-white/70 mb-1" for="co-pay">Metode Pembayaran</label>
                <select id="co-pay" class="w-full px-3 py-2.5 rounded-lg bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-purple-400/50">
                    <option value="transfer">Transfer Bank</option>
                    <option value="cod">COD / Bayar di Tempat</option>
                </select>
                <p class="text-[11px] text-white/40 mt-1">Ringkasan dikirim ke WhatsApp admin setelah draft tersimpan.</p>
            </div>
            <p id="checkout-error" class="hidden text-sm text-red-300"></p>
        </div>

        <div class="p-4 border-t border-white/10 space-y-3 bg-black/20">
            <div class="flex items-center justify-between text-white">
                <span class="text-sm text-white/70">Total</span>
                <span id="cart-total" class="text-lg font-bold tabular-nums">Rp 0</span>
            </div>
            <div id="cart-actions" class="flex gap-2">
                <button type="button" id="btn-clear-cart" class="flex-1 px-3 py-2.5 rounded-lg bg-white/10 text-white/80 text-sm hover:bg-white/15">Kosongkan</button>
                <button type="button" id="btn-to-checkout" class="flex-[2] px-3 py-2.5 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-500 disabled:opacity-40" disabled>Checkout</button>
            </div>
            <div id="checkout-actions" class="hidden flex gap-2">
                <button type="button" id="btn-back-cart" class="flex-1 px-3 py-2.5 rounded-lg bg-white/10 text-white/80 text-sm hover:bg-white/15">Kembali</button>
                <button type="button" id="btn-submit-order" class="flex-[2] px-3 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 disabled:opacity-40">Kirim ke WhatsApp</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const STORAGE_KEY = 'layanan_kami_cart_v1';
    let cart = [];
    let activeServiceId = 'all';
    let mode = 'cart'; // cart | checkout

    const grid = document.getElementById('catalog-grid');
    const emptyEl = document.getElementById('catalog-empty');
    const searchInput = document.getElementById('catalog-search');
    const overlay = document.getElementById('cart-overlay');
    const panel = document.getElementById('cart-panel');
    const cartItemsEl = document.getElementById('cart-items');
    const cartEmptyEl = document.getElementById('cart-empty');
    const cartTotalEl = document.getElementById('cart-total');
    const badge = document.getElementById('cart-badge');
    const fabBadge = document.getElementById('fab-cart-badge');
    const btnToCheckout = document.getElementById('btn-to-checkout');
    const cartView = document.getElementById('cart-view');
    const checkoutView = document.getElementById('checkout-view');
    const cartActions = document.getElementById('cart-actions');
    const checkoutActions = document.getElementById('checkout-actions');
    const panelTitle = document.getElementById('cart-panel-title');
    const panelSubtitle = document.getElementById('cart-panel-subtitle');
    const checkoutSummary = document.getElementById('checkout-summary');
    const checkoutError = document.getElementById('checkout-error');
    const catalogWasEmpty = {{ $catalog->isEmpty() ? 'true' : 'false' }};

    function formatRp(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    }

    function saveCart() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); } catch (e) {}
    }

    function loadCart() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            cart = raw ? JSON.parse(raw) : [];
            if (!Array.isArray(cart)) cart = [];
            cart = cart.map(function (item) {
                const maxQty = parseInt(item.maxQty, 10);
                if (!isNaN(maxQty) && maxQty > 0 && item.qty > maxQty) {
                    item.qty = maxQty;
                }
                return item;
            });
        } catch (e) { cart = []; }
    }

    function cartCount() {
        return cart.reduce((sum, i) => sum + (i.qty || 0), 0);
    }

    function cartTotal() {
        return cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    }

    function updateBadges() {
        const count = cartCount();
        [badge, fabBadge].forEach(function (el) {
            if (!el) return;
            el.textContent = String(count);
            if (el === badge) el.classList.toggle('hidden', count === 0);
        });
        btnToCheckout.disabled = count === 0;
        cartTotalEl.textContent = formatRp(cartTotal());
    }

    function renderCartItems() {
        cartItemsEl.innerHTML = '';
        if (!cart.length) {
            cartEmptyEl.classList.remove('hidden');
            updateBadges();
            return;
        }
        cartEmptyEl.classList.add('hidden');
        cart.forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'rounded-xl border border-white/10 bg-white/5 p-3';
            const maxHint = item.maxQty ? (' · max ' + item.maxQty) : '';
            row.innerHTML =
                '<div class="flex justify-between gap-2 mb-2">' +
                    '<div class="min-w-0">' +
                        '<div class="text-sm font-semibold text-white truncate">' + escapeHtml(item.name) + '</div>' +
                        '<div class="text-[11px] text-white/45">' + escapeHtml(item.serviceTitle || '') + ' · ' + escapeHtml(item.type || '') + escapeHtml(maxHint) + '</div>' +
                        '<div class="text-sm text-purple-200 mt-1 tabular-nums">' + formatRp(item.price) + ' <span class="text-white/40 text-xs">/ ' + escapeHtml(item.unit || '') + '</span></div>' +
                    '</div>' +
                    '<button type="button" data-remove="' + escapeAttr(item.id) + '" class="text-white/40 hover:text-red-300 text-xs shrink-0">Hapus</button>' +
                '</div>' +
                '<div class="inline-flex items-center rounded-lg border border-white/15 overflow-hidden">' +
                    '<button type="button" data-dec="' + escapeAttr(item.id) + '" class="px-3 py-1.5 text-white/80 hover:bg-white/10">−</button>' +
                    '<span class="px-3 py-1.5 text-sm text-white tabular-nums min-w-[2rem] text-center">' + item.qty + '</span>' +
                    '<button type="button" data-inc="' + escapeAttr(item.id) + '" class="px-3 py-1.5 text-white/80 hover:bg-white/10">+</button>' +
                '</div>';
            cartItemsEl.appendChild(row);
        });
        updateBadges();
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
    }
    function escapeAttr(str) { return escapeHtml(str).replace(/`/g, ''); }

    function clampQty(qty, maxQty) {
        const n = Math.max(1, qty || 1);
        if (!maxQty || maxQty <= 0) return n;
        return Math.min(n, maxQty);
    }

    function addToCart(card) {
        const id = card.dataset.id;
        const maxQty = parseInt(card.dataset.maxQty, 10) || 0;
        const existing = cart.find(function (i) { return i.id === id; });
        if (existing) {
            if (maxQty && existing.qty >= maxQty) return;
            existing.qty = clampQty(existing.qty + 1, maxQty || existing.maxQty);
            existing.maxQty = maxQty || existing.maxQty;
        } else {
            cart.push({
                id: id,
                name: card.dataset.name,
                price: parseFloat(card.dataset.price) || 0,
                unit: card.dataset.unit || '',
                type: card.dataset.type || '',
                serviceId: card.dataset.serviceId || '',
                serviceTitle: card.dataset.serviceTitle || '',
                maxQty: maxQty || null,
                qty: 1
            });
        }
        saveCart();
        renderCartItems();
        openCart();
    }

    function setQty(id, delta) {
        const item = cart.find(function (i) { return i.id === id; });
        if (!item) return;
        const next = item.qty + delta;
        if (next <= 0) {
            cart = cart.filter(function (i) { return i.id !== id; });
        } else {
            item.qty = clampQty(next, item.maxQty);
        }
        saveCart();
        renderCartItems();
        if (mode === 'checkout') renderCheckoutSummary();
    }

    function removeItem(id) {
        cart = cart.filter(function (i) { return i.id !== id; });
        saveCart();
        renderCartItems();
        if (mode === 'checkout') renderCheckoutSummary();
    }

    function filterCatalog() {
        if (catalogWasEmpty) {
            emptyEl.classList.remove('hidden');
            grid.classList.add('hidden');
            return;
        }
        const q = (searchInput.value || '').trim().toLowerCase();
        let visible = 0;
        grid.querySelectorAll('.catalog-card').forEach(function (card) {
            const matchService = activeServiceId === 'all' || String(card.dataset.serviceId) === String(activeServiceId);
            const matchSearch = !q || (card.dataset.search || '').indexOf(q) !== -1;
            const show = matchService && matchSearch;
            card.classList.toggle('hidden', !show);
            if (show) visible += 1;
        });
        emptyEl.classList.toggle('hidden', visible > 0);
        grid.classList.toggle('hidden', visible === 0);
        if (visible === 0) {
            emptyEl.textContent = 'Tidak ada item yang cocok dengan pencarian/filter.';
        }
    }

    function openCart() {
        mode = 'cart';
        syncModeUi();
        overlay.classList.remove('hidden');
        overlay.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(function () {
            panel.classList.remove('translate-x-full');
        });
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        panel.classList.add('translate-x-full');
        setTimeout(function () {
            overlay.classList.add('hidden');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            mode = 'cart';
            syncModeUi();
        }, 250);
    }

    function syncModeUi() {
        const isCheckout = mode === 'checkout';
        cartView.classList.toggle('hidden', isCheckout);
        checkoutView.classList.toggle('hidden', !isCheckout);
        cartActions.classList.toggle('hidden', isCheckout);
        checkoutActions.classList.toggle('hidden', !isCheckout);
        panelTitle.textContent = isCheckout ? 'Checkout' : 'Keranjang';
        panelSubtitle.textContent = isCheckout ? 'Isi identitas untuk kirim pesanan' : 'Kelola item sebelum checkout';
        if (isCheckout) renderCheckoutSummary();
    }

    function renderCheckoutSummary() {
        if (!cart.length) {
            checkoutSummary.innerHTML = '<p class="text-white/50">Keranjang kosong.</p>';
            return;
        }
        checkoutSummary.innerHTML = cart.map(function (i) {
            return '<div class="flex justify-between gap-2"><span class="truncate">' + escapeHtml(i.name) + ' ×' + i.qty + '</span><span class="tabular-nums shrink-0">' + formatRp(i.price * i.qty) + '</span></div>';
        }).join('') + '<div class="pt-2 mt-1 border-t border-white/10 flex justify-between font-semibold text-white"><span>Total</span><span class="tabular-nums">' + formatRp(cartTotal()) + '</span></div>';
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function syncCompanyField() {
        const typeEl = document.getElementById('co-type');
        const wrap = document.getElementById('co-company-wrap');
        const company = document.getElementById('co-company');
        if (!typeEl || !wrap) return;
        const opt = typeEl.options[typeEl.selectedIndex];
        const legacy = opt ? (opt.getAttribute('data-legacy') || '') : '';
        const label = opt ? (opt.textContent || '').trim().toLowerCase() : '';
        const needCompany = legacy === 'company' || (label && label !== 'individu' && typeEl.value !== '');
        wrap.classList.toggle('hidden', !needCompany);
        if (!needCompany && company) company.value = '';
    }

    function submitOrder() {
        checkoutError.classList.add('hidden');
        const typeEl = document.getElementById('co-type');
        const typeId = typeEl ? typeEl.value : '';
        const name = document.getElementById('co-name').value.trim();
        const phone = document.getElementById('co-phone').value.trim();
        const address = document.getElementById('co-address').value.trim();
        const company = (document.getElementById('co-company')?.value || '').trim();
        const pay = document.getElementById('co-pay').value;
        const btn = document.getElementById('btn-submit-order');
        const opt = typeEl ? typeEl.options[typeEl.selectedIndex] : null;
        const legacy = opt ? (opt.getAttribute('data-legacy') || '') : '';

        if (!typeId) {
            checkoutError.textContent = 'Pilih tipe customer.';
            checkoutError.classList.remove('hidden');
            return;
        }
        if (!name || !phone || !address) {
            checkoutError.textContent = 'Nama, No. WhatsApp, dan Alamat/Catatan wajib diisi.';
            checkoutError.classList.remove('hidden');
            return;
        }
        if (legacy === 'company' && !company) {
            checkoutError.textContent = 'Nama perusahaan wajib diisi.';
            checkoutError.classList.remove('hidden');
            return;
        }
        if (!cart.length) {
            checkoutError.textContent = 'Keranjang kosong.';
            checkoutError.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Menyimpan…';

        fetch(@json(route('services.checkout')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                name: name,
                phone: phone,
                address: address,
                customer_type_id: parseInt(typeId, 10),
                company_name: company || null,
                payment_method: pay,
                items: cart.map(function (i) {
                    return { id: i.id, qty: i.qty };
                })
            })
        }).then(async function (res) {
            const data = await res.json().catch(function () { return {}; });
            if (!res.ok) {
                let msg = data.message || 'Gagal menyimpan pesanan.';
                if (data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first[0]) msg = first[0];
                }
                throw new Error(msg);
            }
            cart = [];
            saveCart();
            renderCartItems();
            closeCart();
            if (data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank', 'noopener');
            }
        }).catch(function (err) {
            checkoutError.textContent = err.message || 'Gagal menyimpan pesanan.';
            checkoutError.classList.remove('hidden');
        }).finally(function () {
            btn.disabled = false;
            btn.textContent = 'Kirim ke WhatsApp';
        });
    }

    // Events
    document.querySelectorAll('.cat-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            activeServiceId = chip.getAttribute('data-service-id') || 'all';
            document.querySelectorAll('.cat-chip').forEach(function (c) {
                const active = c === chip;
                c.className = 'cat-chip px-4 py-2 rounded-xl text-sm font-medium border transition ' +
                    (active
                        ? 'bg-purple-600 text-white border-purple-400/40'
                        : 'bg-white/5 text-white/80 border-white/10 hover:bg-white/10');
            });
            filterCatalog();
        });
    });

    searchInput.addEventListener('input', filterCatalog);

    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-add-cart');
        if (!btn) return;
        const card = btn.closest('.catalog-card');
        if (card) addToCart(card);
    });

    cartItemsEl.addEventListener('click', function (e) {
        const rem = e.target.closest('[data-remove]');
        const inc = e.target.closest('[data-inc]');
        const dec = e.target.closest('[data-dec]');
        if (rem) removeItem(rem.getAttribute('data-remove'));
        if (inc) setQty(inc.getAttribute('data-inc'), 1);
        if (dec) setQty(dec.getAttribute('data-dec'), -1);
    });

    document.getElementById('btn-open-cart').addEventListener('click', openCart);
    document.getElementById('fab-cart').addEventListener('click', openCart);
    overlay.querySelectorAll('[data-close-cart]').forEach(function (el) {
        el.addEventListener('click', closeCart);
    });
    document.getElementById('btn-clear-cart').addEventListener('click', function () {
        cart = [];
        saveCart();
        renderCartItems();
    });
    btnToCheckout.addEventListener('click', function () {
        if (!cart.length) return;
        mode = 'checkout';
        syncModeUi();
    });
    document.getElementById('btn-back-cart').addEventListener('click', function () {
        mode = 'cart';
        syncModeUi();
    });
    document.getElementById('btn-submit-order').addEventListener('click', submitOrder);
    document.getElementById('co-type').addEventListener('change', syncCompanyField);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.classList.contains('hidden')) closeCart();
    });

    loadCart();
    renderCartItems();
    filterCatalog();
    syncCompanyField();
})();
</script>
@endpush
