@extends('admin.keuangan.layout')

@section('title', 'Edit Termin - ' . $project->code)

@section('keuangan_content')
<div class="mb-6">
    <a href="{{ route('admin.projects.show', $project) }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali ke Detail Project</a>
    <h2 class="text-2xl font-bold text-gray-800">Edit Termin Pembayaran</h2>
    <p class="text-gray-600 mt-1">{{ $project->title }} · {{ $project->code }}</p>
    <p class="text-sm text-gray-500 mt-2">
        Total project saat ini: <strong>Rp {{ number_format($project->total, 0, ',', '.') }}</strong>
        (DPP jasa + stok + pajak). Nominal termin mengikuti persentase × total.
    </p>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <ul class="list-disc list-inside text-red-700 text-sm">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"><p class="text-red-700">{{ session('error') }}</p></div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
    <form method="POST" action="{{ route('admin.projects.terms.update', $project) }}" class="space-y-2">
        @csrf
        @method('PATCH')
        @include('admin.projects.partials.terms-fields')
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 pt-6 border-t border-gray-200 mt-6">
            <a href="{{ route('admin.projects.show', $project) }}" class="w-full sm:w-auto px-6 py-2.5 text-center bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Batal</a>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold">Simpan Termin</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initProjectForm === 'function') {
        window.initProjectForm();
    }
});
</script>
@endpush
