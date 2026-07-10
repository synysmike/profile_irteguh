@extends('admin.layout')

@section('title', 'Respon Pesan - Admin')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Respon Pesan</h2>
        <p class="text-gray-600">{{ $message->subject ?? 'Tanpa subjek' }} · {{ $message->name }}</p>
    </div>
    <a href="{{ route('admin.contact.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm font-medium">
        ← Kembali ke Kontak
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-700">{{ session('success') }}</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Pesan dari Pengunjung</h3>
                    <p class="text-sm text-gray-500">{{ $message->created_at->format('d F Y, H:i') }}</p>
                </div>
                @if($message->admin_response)
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sudah Direspon</span>
                @else
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Menunggu Respon</span>
                @endif
            </div>
            <p class="text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Respon Admin</h3>
            <form action="{{ route('admin.contact.messages.respond', $message->id) }}" method="POST" class="space-y-4">
                @csrf
                <textarea name="admin_response" rows="8" required
                          placeholder="Tulis respon internal atau catatan tindak lanjut..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('admin_response', $message->admin_response) }}</textarea>
                @error('admin_response')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                        Simpan Respon
                    </button>
                    <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . ($message->subject ?? 'Pesan Anda')) }}"
                       class="px-6 py-2.5 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition font-semibold">
                        Balas via Email
                    </a>
                    @if($message->phone)
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $message->phone) }}"
                       target="_blank"
                       class="px-6 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700 transition font-semibold">
                        Balas via WhatsApp
                    </a>
                    @endif
                </div>
            </form>
            @if($message->responded_at)
            <p class="text-xs text-gray-500 mt-4">Terakhir direspon: {{ $message->responded_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengirim</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Nama</dt>
                    <dd class="font-medium text-gray-900">{{ $message->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd><a href="mailto:{{ $message->email }}" class="text-purple-600 hover:text-purple-800">{{ $message->email }}</a></dd>
                </div>
                @if($message->phone)
                <div>
                    <dt class="text-gray-500">Telepon</dt>
                    <dd><a href="tel:{{ $message->phone }}" class="text-purple-600 hover:text-purple-800">{{ $message->phone }}</a></dd>
                </div>
                @endif
                @if($message->read_at)
                <div>
                    <dt class="text-gray-500">Dibaca</dt>
                    <dd class="text-gray-900">{{ $message->read_at->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-3">
            @if(!$message->is_read)
            <form action="{{ route('admin.contact.messages.mark-read', $message->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm">Tandai Sudah Dibaca</button>
            </form>
            @else
            <form action="{{ route('admin.contact.messages.mark-unread', $message->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition text-sm">Tandai Belum Dibaca</button>
            </form>
            @endif
            <button type="button" onclick="deleteContactMessage({{ $message->id }})"
                    class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm">
                Hapus Pesan
            </button>
        </div>
    </div>
</div>
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
            window.location.href = '{{ route('admin.contact.index') }}';
        }
    })
    .catch(() => alert('Gagal menghapus pesan.'));
}
</script>
@endpush
