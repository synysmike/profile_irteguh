@extends('admin.keuangan.layout')

@section('title', 'Surat Tugas - ' . $project->code)

@section('keuangan_content')
<div class="mb-6">
    <a href="{{ route('admin.projects.show', $project) }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali ke Detail Project</a>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Surat Tugas</h2>
            <p class="text-gray-600 mt-1">{{ $project->code }} · {{ $project->title }}</p>
        </div>
        <a href="{{ route('admin.projects.assignment-letters.create', $project) }}" class="px-5 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold text-sm">
            + Buat Surat Tugas
        </a>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"><p class="text-green-700">{{ session('success') }}</p></div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yang Bertugas</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($project->assignmentLetters as $letter)
            <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $letter->number }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $letter->letter_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $letter->assigneeNamesSummary() }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $letter->assignees->count() }} orang</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <a href="{{ route('admin.projects.assignment-letters.show', [$project, $letter]) }}" class="text-blue-600 hover:text-blue-800 mr-3">Lihat</a>
                    <a href="{{ route('admin.projects.assignment-letters.edit', [$project, $letter]) }}" class="text-purple-600 hover:text-purple-800 mr-3">Edit</a>
                    <form method="POST" action="{{ route('admin.projects.assignment-letters.destroy', [$project, $letter]) }}" class="inline" onsubmit="return confirm('Hapus surat tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    Belum ada surat tugas.
                    <a href="{{ route('admin.projects.assignment-letters.create', $project) }}" class="text-purple-600 hover:text-purple-800">Buat yang pertama</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
