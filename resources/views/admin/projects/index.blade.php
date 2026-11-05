@extends('admin.keuangan.layout')

@section('title', 'Project - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Project</h2>
        <p class="text-gray-600">Kelola tiket project, progress, termin pembayaran, dan koneksi ke keuangan & pajak.</p>
    </div>
    <button type="button" onclick="openResourceModal('projectModal', 'projects', 'Project')" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
        + Tiket Project
    </button>
</div>

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <p class="text-red-600">{{ session('error') }}</p>
</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($projects as $project)
            <tr id="projectsRow_{{ $project->id }}">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $project->code }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">
                    {{ $project->title }}
                    @if($project->is_portfolio_public)
                    <div class="mt-1 flex flex-wrap gap-1">
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-emerald-100 text-emerald-800">Portofolio</span>
                        @if($project->is_portfolio_featured)
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-amber-100 text-amber-800">Unggulan</span>
                        @endif
                    </div>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $project->customer?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full {{ $project->statusBadgeClass() }}">{{ $project->statusLabel() }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <div class="w-28 bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $project->progress_percent }}%</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    {{ $project->payment_method === 'full' ? 'Lunas' : 'Termin' }}
                    <div class="text-xs text-gray-500">Bayar: {{ $project->paymentProgressPercent() }}%</div>
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($project->total, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:text-blue-800 mr-2">Detail</a>
                    <button type="button"
                            class="text-emerald-600 hover:text-emerald-800 mr-2"
                            onclick="openReviewWaModal(this)"
                            data-code="{{ $project->code }}"
                            data-title="{{ $project->title }}"
                            data-customer="{{ $project->customer?->name }}"
                            data-phone="{{ \App\Support\PhoneNumber::normalize($project->customer?->phone) }}"
                            data-phone-display="{{ $project->customer?->phone }}"
                            data-status="{{ $project->statusLabel() }}"
                            data-progress="{{ $project->progress_percent }}"
                            data-message="{{ json_encode(\App\Support\ProjectReviewWhatsApp::buildMessage($project), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}">
                        Minta Ulasan
                    </button>
                    <button type="button" onclick="openResourceModal('projectModal', 'projects', 'Project', {{ $project->id }})" class="text-purple-600 hover:text-purple-800 mr-2">Edit</button>
                    <button type="button" onclick="deleteResource('projects', {{ $project->id }}, 'Project')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                    Belum ada tiket project.
                    <button type="button" onclick="openResourceModal('projectModal', 'projects', 'Project')" class="text-purple-600 hover:text-purple-800">Buat yang pertama</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', ['modalId' => 'projectModal', 'title' => 'Tiket Project'])

{{-- Modal minta ulasan via WhatsApp --}}
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
                    <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Project</div>
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
                <p class="text-xs text-gray-500 mt-1">Template sudah berisi link ulasan dan detail project. Silakan edit sebelum kirim.</p>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const originalOpen = window.openResourceModal;
    if (typeof originalOpen === 'function') {
        window.openResourceModal = function(modalId, resourceName, singularName, id) {
            originalOpen(modalId, resourceName, singularName, id);
            if (resourceName !== 'projects') return;
            setTimeout(function () { window.initProjectForm && window.initProjectForm(); }, 200);
            setTimeout(function () { window.initProjectForm && window.initProjectForm(); }, 450);
        };
    }

    var modal = document.getElementById('reviewWaModal');
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

        document.getElementById('reviewWaMeta').textContent = (btn.dataset.code || '') + ' · Minta ulasan customer';
        document.getElementById('reviewWaCustomer').textContent = btn.dataset.customer || '—';
        document.getElementById('reviewWaPhone').textContent = phoneCache
            ? ('WA: ' + (btn.dataset.phoneDisplay || phoneCache))
            : 'Nomor HP belum ada';
        document.getElementById('reviewWaProject').textContent = (btn.dataset.code || '') + ' — ' + (btn.dataset.title || '');
        document.getElementById('reviewWaStatus').textContent = (btn.dataset.status || '') + ' · Progress ' + (btn.dataset.progress || '0') + '%';
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
