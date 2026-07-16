@extends('admin.keuangan.layout')

@section('title', 'Buat Surat Tugas - ' . $project->code)

@section('keuangan_content')
<div class="mb-6">
    <a href="{{ route('admin.projects.show', $project) }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali ke Detail Project</a>
    <h2 class="text-2xl font-bold text-gray-800">Buat Surat Tugas</h2>
    <p class="text-gray-600 mt-1">{{ $project->code }} · {{ $project->title }}</p>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 max-w-3xl">
    <form method="POST" action="{{ route('admin.projects.assignment-letters.store', $project) }}">
        @csrf
        @include('admin.projects.assignment-letters.partials.form', ['letter' => null])
        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold">Simpan</button>
            <a href="{{ route('admin.projects.show', $project) }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Batal</a>
        </div>
    </form>
</div>
@endsection
