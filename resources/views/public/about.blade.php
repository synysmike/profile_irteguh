@extends('public.layouts.app')

@section('title', 'Tentang - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Hero -->
            <div class="text-center mb-16">
                <h1 class="text-5xl font-bold text-white mb-6">Tentang Ir Teguh Solution</h1>
                <p class="text-xl text-white/80">Mengubah tantangan menjadi peluang</p>
            </div>

            <!-- Mission -->
            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-white mb-6">Misi Kami</h2>
                <p class="text-white/80 text-lg leading-relaxed mb-4">
                    Di Ir Teguh Solution, kami menjembatani kesenjangan antara teknologi dan kreativitas, menyediakan solusi 
                    terintegrasi yang memberdayakan sektor pendidikan dan bisnis. Kami percaya dalam memberikan bukan hanya layanan, 
                    tetapi pengalaman transformatif yang mendorong pertumbuhan dan inovasi.
                </p>
            </div>

            <!-- Strengths -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4">🚀 Otomasi & Infrastruktur IT</h3>
                    <p class="text-white/70">
                        Kami mengkhususkan diri dalam deployment Docker, manajemen server, dan solusi infrastruktur cloud. 
                        Keahlian kami memastikan sistem IT yang aman, skalabel, dan efisien.
                    </p>
                </div>
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4">🎨 Desain Kreatif</h3>
                    <p class="text-white/70">
                        Dari branding hingga desain UI/UX, kami menciptakan solusi visual yang menarik yang beresonansi dengan 
                        audiens Anda dan memperkuat identitas merek Anda.
                    </p>
                </div>
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4">⚖️ Layanan Hukum & Bisnis</h3>
                    <p class="text-white/70">
                        Kami menyediakan layanan hukum dan bisnis yang komprehensif termasuk pendaftaran perusahaan, 
                        kepatuhan, dan konsultasi bisnis untuk membantu Anda menavigasi regulasi yang kompleks.
                    </p>
                </div>
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4">🔧 Optimasi Workflow</h3>
                    <p class="text-white/70">
                        Kami menganalisis dan mengoptimalkan proses bisnis Anda, mengimplementasikan solusi otomasi 
                        yang menghemat waktu, mengurangi kesalahan, dan meningkatkan produktivitas.
                    </p>
                </div>
            </div>

            <!-- Values -->
            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-white mb-6">Nilai-Nilai Kami</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="text-white/80 text-lg">
                            <strong class="text-white">Inovasi:</strong> Kami selalu mengikuti tren teknologi dan terus 
                            mengeksplorasi solusi baru untuk menyelesaikan masalah yang kompleks.
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-white/80 text-lg">
                            <strong class="text-white">Kualitas:</strong> Setiap proyek dikerjakan dengan perhatian 
                            yang teliti terhadap detail dan komitmen terhadap keunggulan.
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-white/80 text-lg">
                            <strong class="text-white">Berfokus pada Klien:</strong> Kesuksesan Anda adalah kesuksesan kami. Kami bekerja erat 
                            dengan klien untuk memahami kebutuhan unik mereka dan memberikan solusi yang disesuaikan.
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-white/80 text-lg">
                            <strong class="text-white">Transparansi:</strong> Kami percaya pada komunikasi yang jelas, umpan balik yang jujur, 
                            dan proses yang transparan di setiap proyek.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contributors/Team Section -->
            @if(isset($contributors) && $contributors->count() > 0)
            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-white text-center mb-12">Tim Kami</h2>
                
                <!-- Slider Container -->
                <div class="contributor-slider-wrapper relative">
                    <!-- Slider Track -->
                    <div class="contributor-slider-track" id="contributorSlider">
                        @php
                            $chunks = $contributors->chunk(4);
                        @endphp
                        @foreach($chunks as $chunk)
                        <div class="contributor-slide">
                            <div class="contributor-slide-content">
                                @foreach($chunk as $contributor)
                                <div class="contributor-profile">
                                    <div class="contributor-image-wrapper">
                                        <img src="{{ $contributor->image_url }}" 
                                             alt="{{ $contributor->name }}" 
                                             class="contributor-image"
                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($contributor->name) }}&size=300&background=fce7f3&color=9f1239&bold=true'">
                                    </div>
                                    <div class="contributor-info">
                                        <div class="contributor-name">{{ $contributor->name }}</div>
                                        <div class="contributor-role">{{ $contributor->role }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <button class="contributor-slider-btn contributor-slider-prev" id="prevBtn" aria-label="Previous">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button class="contributor-slider-btn contributor-slider-next" id="nextBtn" aria-label="Next">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <!-- Dots Indicator -->
                    <div class="contributor-slider-dots flex justify-center gap-2 mt-8" id="dotsContainer"></div>
                </div>
            </div>
            @endif

            <!-- CTA -->
            <div class="text-center mt-12">
                <a href="{{ route('contact') }}" class="inline-block px-8 py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Mari Bekerja Bersama
                </a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('contributorSlider');
    if (!slider) return;
    
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dotsContainer = document.getElementById('dotsContainer');
    const slides = slider.querySelectorAll('.contributor-slide');
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    
    if (totalSlides === 0) return;
    
    // Create dots
    for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('button');
        dot.classList.add('contributor-slider-dot');
        if (i === 0) dot.classList.add('active');
        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
        dot.addEventListener('click', () => goToSlide(i));
        dotsContainer.appendChild(dot);
    }
    
    const dots = dotsContainer.querySelectorAll('.contributor-slider-dot');
    
    function updateSlider() {
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // Update dots
        dots.forEach((dot, index) => {
            if (index === currentSlide) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    function goToSlide(index) {
        currentSlide = index;
        updateSlider();
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlider();
    }
    
    // Event listeners
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    
    // Auto-play
    let autoPlayInterval = setInterval(nextSlide, 5000);
    
    // Pause on hover
    const sliderWrapper = slider.closest('.contributor-slider-wrapper');
    if (sliderWrapper) {
        sliderWrapper.addEventListener('mouseenter', () => {
            clearInterval(autoPlayInterval);
        });
        
        sliderWrapper.addEventListener('mouseleave', () => {
            autoPlayInterval = setInterval(nextSlide, 5000);
        });
    }
    
    // Touch/swipe support
    let startX = 0;
    let isDragging = false;
    
    slider.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        clearInterval(autoPlayInterval);
    });
    
    slider.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
    });
    
    slider.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        
        const endX = e.changedTouches[0].clientX;
        const diffX = startX - endX;
        
        if (Math.abs(diffX) > 50) {
            if (diffX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
        
        autoPlayInterval = setInterval(nextSlide, 5000);
    });
});
</script>
@endpush
@endsection
