@extends('admin.keuangan.layout')

@section('title', 'Detail Project - ' . $project->code)

@section('keuangan_content')
<div class="mb-6">
    <a href="{{ route('admin.projects.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali ke Project</a>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $project->title }}</h2>
            <p class="text-gray-600 mt-1">{{ $project->code }} · {{ $project->customer?->name }}</p>
        </div>
        <button type="button" onclick="openResourceModal('projectModal', 'projects', 'Project', {{ $project->id }})" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">Edit Project</button>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"><p class="text-green-700">{{ session('success') }}</p></div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"><p class="text-red-700">{{ session('error') }}</p></div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Status</div>
        <div class="text-lg font-semibold text-gray-900">{{ \App\Models\Project::statusLabels()[$project->status] ?? $project->status }}</div>
        <form method="POST" action="{{ route('admin.projects.update-status', $project) }}" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                @foreach(\App\Models\Project::statusLabels() as $value => $label)
                <option value="{{ $value }}" {{ $project->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="number" name="progress_percent" min="0" max="100" value="{{ $project->progress_percent }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Progress %">
            <button type="submit" class="w-full px-3 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-900">Update Progress</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Nilai & Pajak</div>
        <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span>DPP</span><span>Rp {{ number_format($project->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>{{ $project->tax_name ?: 'Pajak' }}</span><span>Rp {{ number_format($project->ppn_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>Rp {{ number_format($project->total, 0, ',', '.') }}</span></div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="text-sm text-gray-500 mb-1">Pembayaran</div>
        <div class="text-lg font-semibold">{{ $project->payment_method === 'full' ? 'Langsung Lunas' : 'Termin' }}</div>
        <div class="mt-2 text-sm text-gray-600">Terkumpul: Rp {{ number_format($project->paidAmount(), 0, ',', '.') }} ({{ $project->paymentProgressPercent() }}%)</div>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $project->paymentProgressPercent() }}%"></div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Termin Pembayaran</h3>
        <p class="text-sm text-gray-500 mt-1">Klik "Posting ke Keuangan" untuk membuat invoice penjualan, pajak, dan penerimaan kas.</p>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($project->paymentTerms as $term)
            <tr>
                <td class="px-4 py-3 text-sm">{{ $term->term_number }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ $term->label }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ number_format($term->percentage, 2, ',', '.') }}%</td>
                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($term->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">{{ $term->due_date?->format('d/m/Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($term->status === 'paid')
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Lunas</span>
                    @else
                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Pending</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    @if($term->sale_id)
                    <a href="{{ route('admin.sales.invoice', $term->sale_id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Invoice</a>
                    @endif
                    @if($term->status !== 'paid')
                    <form method="POST" action="{{ route('admin.projects.pay-term', [$project, $term]) }}" class="inline" onsubmit="return confirm('Posting pembayaran termin ini ke penjualan & kas?')">
                        @csrf
                        <button type="submit" class="text-purple-600 hover:text-purple-800 font-medium">Posting ke Keuangan</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.projects.unpay-term', [$project, $term]) }}" class="inline" onsubmit="return confirm('Batalkan pelunasan termin ini?\nInvoice penjualan dan transaksi kas terkait akan dihapus, status kembali ke Pending.')">
                        @csrf
                        <button type="submit" class="text-amber-700 hover:text-amber-900 font-medium">Batalkan Lunas</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada termin.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($project->description || $project->notes)
<div class="bg-white rounded-lg shadow border border-gray-200 p-6">
    @if($project->description)<p class="text-gray-700 mb-3">{{ $project->description }}</p>@endif
    @if($project->notes)<p class="text-sm text-gray-500"><strong>Catatan:</strong> {{ $project->notes }}</p>@endif
</div>
@endif

@include('admin.components.modal', ['modalId' => 'projectModal', 'title' => 'Edit Project'])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const originalOpen = window.openResourceModal;
    if (typeof originalOpen !== 'function') return;

    window.openResourceModal = function(modalId, resourceName, singularName, id) {
        originalOpen(modalId, resourceName, singularName, id);
        if (resourceName !== 'projects') return;
        setTimeout(function() { if (typeof window.initProjectForm === 'function') window.initProjectForm(); }, 200);
        setTimeout(function() { if (typeof window.initProjectForm === 'function') window.initProjectForm(); }, 450);
    };
});
</script>
@endpush
