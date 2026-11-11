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
@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-700">{{ session('success') }}</p>
</div>
@endif

@php $layananPendingProjects = $projects->where('is_layanan_pending', true)->count(); @endphp
@if($layananPendingProjects > 0)
<div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
    <p class="text-orange-800 text-sm font-medium">{{ $layananPendingProjects }} draft Order Layanan (jasa) menunggu konfirmasi.</p>
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
                    <div class="mt-1 flex flex-wrap gap-1">
                        @if($project->is_portfolio_public)
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-emerald-100 text-emerald-800">Portofolio</span>
                        @endif
                        @if($project->is_portfolio_featured)
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-amber-100 text-amber-800">Unggulan</span>
                        @endif
                        @if($project->show_in_layanan)
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-indigo-100 text-indigo-800">Layanan Kami{{ $project->service ? ' · '.$project->service->title : '' }}</span>
                        @endif
                        @if($project->is_layanan_pending)
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-orange-100 text-orange-800" title="{{ $project->layanan_template_project_id ? 'Clone dari template #'.$project->layanan_template_project_id : 'Order publik' }}">Order Layanan</span>
                        @endif
                    </div>
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
                    @if($project->is_layanan_pending)
                    <form method="POST" action="{{ route('admin.projects.layanan.confirm', $project) }}" class="inline mr-2" onsubmit="return confirm('Konfirmasi order jasa ini? Project akan aktif (berjalan) dan termin default dibuat jika belum ada.')">
                        @csrf
                        <button type="submit" class="text-emerald-700 hover:text-emerald-900 font-medium">Konfirmasi</button>
                    </form>
                    <form method="POST" action="{{ route('admin.projects.layanan.reject', $project) }}" class="inline mr-2" onsubmit="return confirm('Tolak draft order jasa ini? Project akan dibatalkan; template etalase tetap aman.')">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Tolak</button>
                    </form>
                    @endif
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
                    @if($project->status !== 'cancelled' && ! $project->is_layanan_pending)
                        @if($project->show_in_layanan)
                        <form method="POST" action="{{ route('admin.projects.update-layanan', $project) }}" class="inline mr-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="show_in_layanan" value="0">
                            <button type="submit" class="text-amber-700 hover:text-amber-900">Sembunyikan Etalase</button>
                        </form>
                        @else
                        <details class="inline-block text-left mr-2 align-top">
                            <summary class="cursor-pointer text-indigo-600 hover:text-indigo-800">Tampil Etalase</summary>
                            <form method="POST" action="{{ route('admin.projects.update-layanan', $project) }}" class="mt-2 p-2 border border-gray-200 rounded bg-white shadow-sm space-y-2 min-w-[12rem]">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="show_in_layanan" value="1">
                                <select name="service_id" required class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                                    <option value="">Kategori *</option>
                                    @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->title }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full text-xs px-2 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">Simpan</button>
                            </form>
                        </details>
                        @endif
                    @endif
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
