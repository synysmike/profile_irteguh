@extends('admin.layout')

@section('title', 'Kelola Kontak - Admin')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Kelola Halaman Kontak</h2>
        <p class="text-gray-600">Atur informasi kontak publik dan tanggapi pesan masuk</p>
    </div>
    <a href="{{ route('contact') }}" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm font-medium">
        Lihat Halaman Publik ↗
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-700">{{ session('success') }}</p>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <p class="text-red-700">{{ session('error') }}</p>
</div>
@endif

{{-- Panel informasi kontak publik --}}
<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-1">Informasi Kontak Publik</h3>
    <p class="text-sm text-gray-500 mb-6">Data ini ditampilkan di sidebar halaman <strong>/contact</strong></p>

    <form action="{{ route('admin.contact.settings.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        @method('PUT')

        <div class="md:col-span-2">
            <label for="contact_address" class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
            <textarea id="contact_address" name="contact_address" rows="2" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('contact_address', $contactSettings['address']) }}</textarea>
            @error('contact_address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="contact_maps_embed" class="block text-sm font-medium text-gray-700 mb-2">Embed Google Maps</label>
            <textarea id="contact_maps_embed" name="contact_maps_embed" rows="3"
                      placeholder="Tempel URL embed atau kode iframe dari Google Maps"
                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 font-mono text-sm">{{ old('contact_maps_embed', $contactSettings['maps_embed_url']) }}</textarea>
            <p class="text-xs text-gray-500 mt-2">
                Cara ambil: buka Google Maps → cari lokasi → <strong>Share</strong> → <strong>Embed a map</strong> → Copy HTML.
                Boleh tempel seluruh kode <code>&lt;iframe&gt;</code> atau hanya URL <code>https://www.google.com/maps/embed?...</code>
            </p>
            @error('contact_maps_embed')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror

            @if(!empty($contactSettings['maps_embed_url']))
            <div class="mt-4 rounded-lg overflow-hidden border border-gray-200">
                <iframe
                    src="{{ $contactSettings['maps_embed_url'] }}"
                    class="w-full h-56 border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
            </div>
            @endif
        </div>

        <div>
            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input type="email" id="contact_email" name="contact_email" required
                   value="{{ old('contact_email', $contactSettings['email']) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @error('contact_email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="contact_whatsapp" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp *</label>
            <input type="text" id="contact_whatsapp" name="contact_whatsapp" required
                   value="{{ old('contact_whatsapp', $contactSettings['whatsapp']) }}"
                   placeholder="6281234567890"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <p class="text-xs text-gray-500 mt-1">Format angka internasional, contoh: 6281234567890</p>
            @error('contact_whatsapp')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="contact_whatsapp_label" class="block text-sm font-medium text-gray-700 mb-2">Teks Link WhatsApp</label>
            <input type="text" id="contact_whatsapp_label" name="contact_whatsapp_label"
                   value="{{ old('contact_whatsapp_label', $contactSettings['whatsapp_label']) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label for="contact_response_note" class="block text-sm font-medium text-gray-700 mb-2">Catatan Waktu Respon</label>
            <input type="text" id="contact_response_note" name="contact_response_note"
                   value="{{ old('contact_response_note', $contactSettings['response_note']) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="md:col-span-2">
            <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                Simpan Informasi Kontak
            </button>
        </div>
    </form>
</div>

{{-- Statistik pesan --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Total Pesan</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCount) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Belum Dibaca</p>
        <p class="text-2xl font-bold text-red-600">{{ number_format($unreadCount) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Menunggu Respon</p>
        <p class="text-2xl font-bold text-amber-600">{{ number_format($pendingCount) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Sudah Direspon</p>
        <p class="text-2xl font-bold text-green-600">{{ number_format($totalCount - $pendingCount) }}</p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg shadow border border-gray-200 p-4 mb-6">
    <div class="flex flex-wrap items-center gap-2">
        @php
            $filters = [
                null => 'Semua',
                'unread' => 'Belum Dibaca',
                'pending' => 'Menunggu Respon',
                'responded' => 'Sudah Direspon',
                'read' => 'Sudah Dibaca',
            ];
        @endphp
        @foreach($filters as $key => $label)
        <a href="{{ route('admin.contact.index', $key ? ['filter' => $key] : []) }}"
           class="px-4 py-2 rounded-md text-sm font-medium transition {{ request('filter', '') === (string) $key || ($key === null && !request('filter')) ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- Tabel pesan --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Pesan Masuk</h3>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengirim</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pesan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($messages as $message)
            <tr id="messageRow_{{ $message->id }}" class="{{ !$message->is_read ? 'bg-amber-50/60' : '' }}">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $message->name }}</div>
                    <div class="text-sm text-gray-500">{{ $message->email }}</div>
                    @if($message->phone)
                    <div class="text-xs text-gray-400">{{ $message->phone }}</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $message->subject ?? 'Tanpa subjek' }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ Str::limit($message->message, 80) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col gap-1 items-start">
                        @if($message->admin_response)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sudah Direspon</span>
                        @else
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Menunggu Respon</span>
                        @endif
                        @if($message->is_read)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Dibaca</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Baru</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $message->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.contact.messages.show', $message->id) }}" class="text-purple-600 hover:text-purple-900 mr-3">Respon</a>
                    <button type="button" onclick="deleteContactMessage({{ $message->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada pesan masuk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($messages->hasPages())
<div class="mt-6">{{ $messages->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
function deleteContactMessage(id) {
    if (!confirm('Hapus pesan ini?')) return;

    fetch(`/admin/contact/messages/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`messageRow_${id}`)?.remove();
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'success');
            }
        }
    })
    .catch(() => alert('Gagal menghapus pesan.'));
}
</script>
@endpush
