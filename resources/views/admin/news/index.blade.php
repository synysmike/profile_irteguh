@extends('admin.layout')

@section('title', 'Kelola Berita - Admin')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Kelola Berita</h2>
        <p class="text-gray-600">CRUD konten berita untuk halaman publik</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('news.index') }}" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm font-medium">Lihat Publik ↗</a>
        <a href="{{ route('admin.news.create') }}" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">+ Tambah Berita</a>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-700">{{ session('success') }}</p>
</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pembaca</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($news as $item)
            <tr id="newsRow_{{ $item->id }}">
                <td class="px-4 py-4">
                    <div class="flex items-start gap-3">
                        @if($item->coverUrl())
                        <img src="{{ $item->coverUrl() }}" alt="" class="w-14 h-14 rounded object-cover border border-gray-200 shrink-0">
                        @endif
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $item->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">/berita/{{ $item->slug }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    @if($item->is_published)
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Published</span>
                    @else
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">Draft</span>
                    @endif
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-800">
                    {{ number_format($item->views_count) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ optional($item->published_at ?? $item->created_at)->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('news.show', $item->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">Lihat</a>
                    <a href="{{ route('admin.news.edit', $item->id) }}" class="text-purple-600 hover:text-purple-800 mr-3">Edit</a>
                    <button type="button" onclick="deleteNews({{ $item->id }})" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                    Belum ada berita.
                    <a href="{{ route('admin.news.create') }}" class="text-purple-600 hover:text-purple-800">Buat yang pertama</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($news->hasPages())
<div class="mt-6">{{ $news->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
function deleteNews(id) {
    if (!confirm('Hapus berita ini?')) return;
    fetch(`/admin/news/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`newsRow_${id}`)?.remove();
        }
    })
    .catch(() => alert('Gagal menghapus berita.'));
}
</script>
@endpush
