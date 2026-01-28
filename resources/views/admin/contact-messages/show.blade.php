@extends('admin.layout')

@section('title', 'Detail Pesan - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Detail Pesan</h2>
                <p class="text-gray-600">Informasi lengkap pesan kontak</p>
            </div>
            <a href="{{ route('admin.contact-messages.index') }}" 
               class="px-6 py-3 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                ← Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Message Card -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $message->subject ?? 'Tanpa Subjek' }}</h3>
                            <p class="text-sm text-gray-500">
                                Diterima pada {{ $message->created_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                        @if($message->is_read)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sudah Dibaca</span>
                        @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Belum Dibaca</span>
                        @endif
                    </div>
                    
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Sender Info -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengirim</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-600">Nama</label>
                            <p class="text-gray-900 font-medium">{{ $message->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="text-gray-900">
                                <a href="mailto:{{ $message->email }}" class="text-purple-600 hover:text-purple-800">
                                    {{ $message->email }}
                                </a>
                            </p>
                        </div>
                        @if($message->phone)
                        <div>
                            <label class="text-sm text-gray-600">Telepon</label>
                            <p class="text-gray-900">
                                <a href="tel:{{ $message->phone }}" class="text-purple-600 hover:text-purple-800">
                                    {{ $message->phone }}
                                </a>
                            </p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-gray-600">Tanggal</label>
                            <p class="text-gray-900">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($message->read_at)
                        <div>
                            <label class="text-sm text-gray-600">Dibaca pada</label>
                            <p class="text-gray-900">{{ $message->read_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi</h3>
                    <div class="space-y-3">
                        @if(!$message->is_read)
                        <form action="{{ route('admin.contact-messages.mark-read', $message->id) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                Tandai Sudah Dibaca
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.contact-messages.mark-unread', $message->id) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition">
                                Tandai Belum Dibaca
                            </button>
                        </form>
                        @endif
                        
                        <button onclick="deleteMessage({{ $message->id }})" 
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                            Hapus Pesan
                        </button>
                        
                        <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject ?? 'Pesan dari ' . $message->name }}" 
                           class="block w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-center">
                            Balas via Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
function deleteMessage(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus pesan ini?')) return;
    
    fetch(`/admin/contact-messages/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route('admin.contact-messages.index') }}';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menghapus pesan.');
    });
}
</script>
@endpush
