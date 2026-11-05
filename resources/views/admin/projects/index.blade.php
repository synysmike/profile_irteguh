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
                            data-subject-label="Project"
                            data-code="{{ $project->code }}"
                            data-title="{{ $project->title }}"
                            data-customer="{{ $project->customer?->name }}"
                            data-phone="{{ \App\Support\PhoneNumber::normalize($project->customer?->phone) }}"
                            data-phone-display="{{ $project->customer?->phone }}"
                            data-status="{{ $project->statusLabel() }} · Progress {{ $project->progress_percent }}%"
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
@include('admin.partials.review-wa-modal')
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
});
</script>
@endpush
