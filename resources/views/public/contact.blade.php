@extends('public.layouts.app')

@section('title', 'Kontak - ' . \App\Models\Setting::appName())

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-6">Hubungi Kami</h1>
                <p class="text-xl text-white/80">Mari diskusikan bagaimana kami dapat membantu mengubah bisnis Anda</p>
            </div>

            @if($errors->any())
            <div class="glass-card rounded-xl p-4 mb-8 bg-red-500/20 border-red-500/50">
                <ul class="text-white space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Contact Form -->
                <div class="glass-card rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">Kirim Pesan</h2>
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="name" class="block text-white/80 text-sm mb-2">Nama</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label for="email" class="block text-white/80 text-sm mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label for="subject" class="block text-white/80 text-sm mb-2">Subjek</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                                   class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label for="phone" class="block text-white/80 text-sm mb-2">Telepon (Opsional)</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                   class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label for="message" class="block text-white/80 text-sm mb-2">Pesan</label>
                            <textarea id="message" name="message" rows="6" required
                                      class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('message') }}</textarea>
                        </div>

                        {{-- Honeypot: disembunyikan dari pengguna, diisi bot --}}
                        <div class="absolute -left-[9999px] w-px h-px overflow-hidden" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
                        </div>

                        {{-- Verifikasi anti-robot --}}
                        <div class="rounded-xl border border-white/20 bg-white/5 p-4 backdrop-blur-md">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-purple-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-sm font-medium text-white/90">Verifikasi Anti-Robot</span>
                            </div>
                            <label for="captcha_answer" class="block text-white/80 text-sm mb-2">
                                Berapa hasil dari <strong class="text-white">{{ $captchaA }} + {{ $captchaB }}</strong>?
                            </label>
                            <input type="number" id="captcha_answer" name="captcha_answer" value="{{ old('captcha_answer') }}" required
                                   inputmode="numeric" min="0" max="99" placeholder="Masukkan jawaban"
                                   class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-purple-500 @error('captcha_answer') border-red-400 ring-1 ring-red-400 @enderror">
                            @error('captcha_answer')
                            <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="space-y-6">
                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">📍 Lokasi</h3>
                        <p class="text-white/80 whitespace-pre-line mb-4">{{ $contactInfo['address'] }}</p>
                        @if(!empty($contactInfo['maps_embed_url']))
                        <div class="rounded-xl overflow-hidden border border-white/20 shadow-lg">
                            <iframe
                                src="{{ $contactInfo['maps_embed_url'] }}"
                                title="Peta lokasi {{ \App\Models\Setting::appName() }}"
                                class="w-full h-64 md:h-72 border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen></iframe>
                        </div>
                        @endif
                    </div>

                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">💬 WhatsApp</h3>
                        <a href="{{ $contactInfo['whatsapp_url'] }}" target="_blank" rel="noopener"
                           class="text-white/80 hover:text-white transition inline-flex items-center gap-2">
                            <span>{{ $contactInfo['whatsapp_label'] }}</span>
                            <span>→</span>
                        </a>
                    </div>

                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">📧 Email</h3>
                        <a href="mailto:{{ $contactInfo['email'] }}"
                           class="text-white/80 hover:text-white transition">
                            {{ $contactInfo['email'] }}
                        </a>
                    </div>

                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">Waktu Respon</h3>
                        <p class="text-white/80">{{ $contactInfo['response_note'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Pesan Terkirim!',
        text: @json(session('success')),
        confirmButtonText: 'Baik',
        confirmButtonColor: '#9333ea',
        background: '#1e1b4b',
        color: '#f8fafc',
        iconColor: '#4ade80',
        customClass: {
            popup: 'rounded-2xl border border-white/10 shadow-2xl',
            title: 'text-xl font-bold',
            htmlContainer: 'text-sm leading-relaxed',
            confirmButton: 'rounded-lg px-6 py-2.5 font-semibold',
        },
    });
});
</script>
@endif
@endpush
