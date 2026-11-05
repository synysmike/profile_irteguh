{{-- Modal minta ulasan via WhatsApp (project) --}}
<div id="reviewWaModal" class="fixed inset-0 bg-black/50 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4" style="display:none;" aria-hidden="true">
    <div class="bg-white rounded-t-2xl sm:rounded-lg shadow-xl w-full sm:max-w-2xl max-h-[92vh] flex flex-col">
        <div class="flex items-center justify-between p-4 sm:p-5 border-b gap-3">
            <div class="min-w-0">
                <h3 class="text-lg font-bold text-gray-800">Minta Ulasan via WhatsApp</h3>
                <p id="reviewWaMeta" class="text-xs text-gray-500 mt-0.5 truncate"></p>
            </div>
            <button type="button" onclick="closeReviewWaModal()" class="text-gray-400 hover:text-gray-600 p-1 shrink-0" aria-label="Tutup">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-4">
            <div id="reviewWaPhoneWarn" class="hidden bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg p-3">
                Customer belum punya nomor HP. Lengkapi data customer sebelum mengirim.
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3 border">
                    <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Penerima</div>
                    <div id="reviewWaCustomer" class="font-medium text-gray-900"></div>
                    <div id="reviewWaPhone" class="text-gray-600 text-xs mt-0.5"></div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border">
                    <div id="reviewWaSubjectLabel" class="text-xs text-gray-500 uppercase font-semibold mb-1">Referensi</div>
                    <div id="reviewWaProject" class="font-medium text-gray-900"></div>
                    <div id="reviewWaStatus" class="text-gray-600 text-xs mt-0.5"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <label for="reviewWaMessage" class="block text-sm font-medium text-gray-700">Pesan WhatsApp</label>
                    <button type="button" id="reviewWaReset" class="text-xs text-purple-600 hover:text-purple-800">Reset template</button>
                </div>
                <textarea id="reviewWaMessage" rows="14" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-mono leading-relaxed focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                <p class="text-xs text-gray-500 mt-1">Template sudah berisi link ulasan. Silakan edit sebelum kirim.</p>
            </div>
        </div>
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end p-4 sm:p-5 border-t bg-white">
            <button type="button" onclick="closeReviewWaModal()" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Batal</button>
            <button type="button" id="reviewWaSend" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-semibold">
                Kirim ke WhatsApp
            </button>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('reviewWaModal');
    if (!modal || window.__reviewWaModalBound) return;
    window.__reviewWaModalBound = true;

    var messageEl = document.getElementById('reviewWaMessage');
    var templateCache = '';
    var phoneCache = '';

    function readMessage(btn) {
        try {
            return JSON.parse(btn.getAttribute('data-message') || '""');
        } catch (e) {
            return btn.dataset.message || '';
        }
    }

    window.openReviewWaModal = function(btn) {
        phoneCache = (btn.dataset.phone || '').trim();
        templateCache = readMessage(btn);

        var subjectLabel = btn.dataset.subjectLabel || 'Referensi';
        document.getElementById('reviewWaSubjectLabel').textContent = subjectLabel;
        document.getElementById('reviewWaMeta').textContent = (btn.dataset.code || '') + ' · Minta ulasan customer';
        document.getElementById('reviewWaCustomer').textContent = btn.dataset.customer || '—';
        document.getElementById('reviewWaPhone').textContent = phoneCache
            ? ('WA: ' + (btn.dataset.phoneDisplay || phoneCache))
            : 'Nomor HP belum ada';
        var title = btn.dataset.title || '';
        document.getElementById('reviewWaProject').textContent = title
            ? ((btn.dataset.code || '') + ' — ' + title)
            : (btn.dataset.code || '—');
        document.getElementById('reviewWaStatus').textContent = btn.dataset.status || '';
        messageEl.value = templateCache;

        var warn = document.getElementById('reviewWaPhoneWarn');
        var sendBtn = document.getElementById('reviewWaSend');
        if (!phoneCache) {
            warn.classList.remove('hidden');
            sendBtn.disabled = true;
            sendBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warn.classList.add('hidden');
            sendBtn.disabled = false;
            sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { messageEl.focus(); }, 50);
    };

    window.closeReviewWaModal = function() {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.getElementById('reviewWaReset').addEventListener('click', function () {
        messageEl.value = templateCache;
    });

    document.getElementById('reviewWaSend').addEventListener('click', function () {
        if (!phoneCache) {
            alert('Customer belum memiliki nomor HP.');
            return;
        }
        var text = (messageEl.value || '').trim();
        if (!text) {
            alert('Pesan tidak boleh kosong.');
            return;
        }
        var url = 'https://wa.me/' + phoneCache + '?text=' + encodeURIComponent(text);
        window.open(url, '_blank', 'noopener');
        closeReviewWaModal();
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeReviewWaModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeReviewWaModal();
    });
});
</script>
@endpush
@endonce
