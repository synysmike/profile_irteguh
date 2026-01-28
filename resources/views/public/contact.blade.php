@extends('public.layouts.app')

@section('title', 'Kontak - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-6">Hubungi Kami</h1>
                <p class="text-xl text-white/80">Mari diskusikan bagaimana kami dapat membantu mengubah bisnis Anda</p>
            </div>

            @if(session('success'))
            <div class="glass-card rounded-xl p-4 mb-8 bg-green-500/20 border-green-500/50">
                <p class="text-white">{{ session('success') }}</p>
            </div>
            @endif

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
                        <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="space-y-6">
                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">📍 Lokasi</h3>
                        <p class="text-white/80">Surabaya, Indonesia</p>
                    </div>

                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">💬 WhatsApp</h3>
                        <a href="https://wa.me/6281234567890" target="_blank" 
                           class="text-white/80 hover:text-white transition inline-flex items-center gap-2">
                            <span>Chat dengan kami di WhatsApp</span>
                            <span>→</span>
                        </a>
                    </div>

                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">📧 Email</h3>
                        <a href="mailto:contact@irteguhsolution.com" 
                           class="text-white/80 hover:text-white transition">
                            contact@irteguhsolution.com
                        </a>
                    </div>

                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">Waktu Respon</h3>
                        <p class="text-white/80">Kami biasanya merespons dalam 24-48 jam</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
