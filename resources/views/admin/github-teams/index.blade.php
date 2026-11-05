@extends('admin.layout')

@section('title', 'Tim GitHub - Admin')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Tim GitHub</h2>
        <p class="text-gray-600">Kelola nama tim dan link GitHub untuk ditampilkan di halaman Portfolio</p>
    </div>
    <a href="{{ route('admin.github-teams.create') }}" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
        + Tambah Tim
    </a>
</div>

@if(session('success'))
<div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-md">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Tim</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GitHub</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggota</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($teams as $team)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $team->sort_order }}</td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $team->name }}</div>
                </td>
                <td class="px-6 py-4">
                    <a href="{{ $team->normalizedGithubUrl() }}" target="_blank" rel="noopener" class="text-sm text-purple-600 hover:text-purple-800 break-all">
                        {{ $team->githubLogin() ?: $team->github_url }}
                    </a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $team->contributors_count }} anggota
                    @if($team->contributors->isNotEmpty())
                    <div class="text-xs text-gray-400 mt-1">{{ $team->contributors->take(3)->pluck('name')->join(', ') }}{{ $team->contributors_count > 3 ? '…' : '' }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($team->is_active)
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                    @else
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.github-teams.edit', $team) }}" class="text-purple-600 hover:text-purple-900 mr-4">Edit</a>
                    <form action="{{ route('admin.github-teams.destroy', $team) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tim {{ $team->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                    Belum ada tim GitHub.
                    <a href="{{ route('admin.github-teams.create') }}" class="text-purple-600 hover:text-purple-800 font-semibold">Tambah yang pertama</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
