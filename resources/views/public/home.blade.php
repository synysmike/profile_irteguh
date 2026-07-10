@extends('public.layouts.app')

@section('title', 'Beranda - Ir Teguh Solution')

@section('body_class', 'bg-gray-950')
@section('fullpage_hero', true)

@section('content')
<!-- Hero Section — fullpage dengan background slide dari admin -->
<section class="hero-fullpage relative w-full h-screen min-h-[600px] overflow-hidden isolate -mt-16">
    @if(isset($slides) && $slides->count() > 0)
    <div class="absolute inset-0 z-0" id="hero-bg-slider" aria-hidden="true">
        @foreach($slides as $index => $slide)
        <img src="{{ $slide->resolvedImageUrl() }}"
             alt=""
             class="hero-bg-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
             data-hero-slide="{{ $index }}"
             loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
             decoding="async">
        @endforeach
        <div class="absolute inset-0 bg-black/35"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/50"></div>
    </div>
    @else
    <div class="absolute inset-0 z-0 bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900" aria-hidden="true"></div>
    @endif

    <div class="relative z-10 flex h-full items-center justify-center px-4 pt-16 pb-20">
        <div class="max-w-4xl mx-auto text-center w-full">
            <div class="hero-brand mb-8 md:mb-10 animate-fade-in">
                <p class="inline-flex items-center gap-2 px-4 py-1.5 mb-4 text-xs md:text-sm uppercase tracking-[0.25em] text-white/90 font-semibold rounded-full border border-white/20 bg-white/10 backdrop-blur-md shadow-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span>
                    Selamat Datang Di Situs Resmi
                </p>
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white drop-shadow-lg leading-tight tracking-tight">
                    <span class="bg-gradient-to-r from-white via-purple-100 to-white bg-clip-text text-transparent">
                        {{ \App\Models\Setting::appName() }}
                    </span>
                </h1>
                <div class="mx-auto mt-5 h-1 w-24 rounded-full bg-gradient-to-r from-transparent via-purple-400 to-transparent opacity-80"></div>
            </div>
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6 min-h-[1.2em] flex items-center justify-center gap-0 drop-shadow-lg" id="hero-typing-wrapper">
                <span id="hero-typing-text" class="inline-block"></span>
                <span id="hero-typing-cursor" class="hero-cursor inline-block shrink-0 w-1 h-[0.9em] bg-white ml-0.5 align-middle" aria-hidden="true"></span>
            </h2>
            <p class="text-xl md:text-2xl text-white/90 mb-8 animate-fade-in drop-shadow-md">
                Untuk Pendidikan dan Bisnis
            </p>
            <p class="text-lg text-white/80 mb-12 max-w-2xl mx-auto animate-fade-in drop-shadow-md">
                Mengubah tantangan menjadi peluang melalui otomasi, infrastruktur IT, desain kreatif, dan layanan hukum.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in">
                <a href="{{ route('portfolio.index') }}" class="px-8 py-4 bg-white/20 backdrop-blur-md text-white rounded-lg hover:bg-white/30 transition border border-white/30 font-semibold shadow-lg">
                    Lihat Portfolio
                </a>
                <a href="{{ route('contact') }}" class="px-8 py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold shadow-lg">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </div>

    @if(isset($slides) && $slides->count() > 1)
    <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-2 z-10" id="hero-bg-dots">
        @foreach($slides as $index => $slide)
        <button type="button"
                class="hero-bg-dot h-2.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/70 w-2.5' }}"
                data-hero-dot="{{ $index }}"
                aria-label="Slide {{ $index + 1 }}: {{ $slide->title }}"></button>
        @endforeach
    </div>
    @endif
</section>

<!-- Quick Links to Categories -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-white text-center mb-12">Jelajahi Karya Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach(['Infrastruktur IT', 'Otomasi & Workflow', 'Kreatif/Desain', 'Layanan Hukum/Bisnis'] as $categoryDisplay)
            @php
                // Map display name to database category name
                $categoryMap = [
                    'Infrastruktur IT' => 'Infrastruktur IT',
                    'Otomasi & Workflow' => 'Otomasi & Workflow',
                    'Kreatif/Desain' => 'Kreatif/Desain',
                    'Layanan Hukum/Bisnis' => 'Layanan Hukum/Bisnis'
                ];
                $category = $categoryMap[$categoryDisplay] ?? $categoryDisplay;
            @endphp
            <a href="{{ route('portfolio.index', ['category' => $category]) }}" 
               class="glass-card rounded-xl p-6 hover:bg-white/10 transition group">
                <div class="text-white text-xl font-semibold mb-2 group-hover:text-purple-200 transition">
                    {{ $categoryDisplay }}
                </div>
                <div class="text-white/60 text-sm">
                    Lihat studi kasus →
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Case Studies -->
@if(isset($featuredCaseStudies) && $featuredCaseStudies->count() > 0)
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-white text-center mb-12">Proyek Unggulan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredCaseStudies as $caseStudy)
            <a href="{{ route('case-study.show', $caseStudy->slug) }}" 
               class="glass-card rounded-xl p-6 hover:scale-105 transition transform">
                <div class="mb-4">
                    @if($caseStudy->visuals && count($caseStudy->visuals) > 0)
                    <div class="w-full h-48 bg-white/10 rounded-lg mb-4 flex items-center justify-center">
                        <span class="text-white/50">Image</span>
                    </div>
                    @endif
                    <h3 class="text-xl font-bold text-white mb-2">{{ $caseStudy->title }}</h3>
                    <p class="text-white/70 text-sm mb-4">{{ Str::limit($caseStudy->excerpt ?? $caseStudy->challenge, 100) }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-white/50 bg-white/10 px-3 py-1 rounded-full">{{ $caseStudy->category }}</span>
                    <span class="text-white/70 text-sm">Baca selengkapnya →</span>
                </div>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-12">
            <a href="{{ route('portfolio.index') }}" class="text-white/80 hover:text-white transition">
                Lihat Semua Proyek →
            </a>
        </div>
    </div>
</section>
@endif

<!-- Services Preview -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="glass-card rounded-2xl p-8 md:p-12 max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-white text-center mb-8">Apa Yang Kami Tawarkan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="text-white">
                    <h3 class="font-semibold text-lg mb-2">Infrastruktur IT</h3>
                    <p class="text-white/70 text-sm">Deployment Docker, manajemen server, solusi cloud</p>
                </div>
                <div class="text-white">
                    <h3 class="font-semibold text-lg mb-2">Otomasi & Workflow</h3>
                    <p class="text-white/70 text-sm">Optimasi proses, otomasi workflow</p>
                </div>
                <div class="text-white">
                    <h3 class="font-semibold text-lg mb-2">Desain Kreatif</h3>
                    <p class="text-white/70 text-sm">Branding, UI/UX, identitas visual</p>
                </div>
                <div class="text-white">
                    <h3 class="font-semibold text-lg mb-2">Hukum & Bisnis</h3>
                    <p class="text-white/70 text-sm">Pendaftaran perusahaan, layanan hukum</p>
                </div>
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('services') }}" class="text-white hover:text-purple-200 transition">
                    Pelajari Lebih Lanjut Tentang Layanan Kami →
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Our Team Section -->
@if(isset($contributors) && $contributors->count() > 0)
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-white text-center mb-12">Tim Kami</h2>
                
                <!-- Slider Container -->
                <div class="contributor-slider-wrapper relative">
                    <!-- Slider Track -->
                    <div class="contributor-slider-track" id="homeContributorSlider">
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
                    <button class="contributor-slider-btn contributor-slider-prev" id="homePrevBtn" aria-label="Previous">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button class="contributor-slider-btn contributor-slider-next" id="homeNextBtn" aria-label="Next">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <!-- Dots Indicator -->
                    <div class="contributor-slider-dots flex justify-center gap-2 mt-8" id="homeDotsContainer"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hero typing animation
    const heroTexts = @json($heroTexts ?? ['Solusi IT & Kreatif Terintegrasi']);
    const typingEl = document.getElementById('hero-typing-text');
    const cursorEl = document.getElementById('hero-typing-cursor');
    
    if (typingEl && heroTexts.length > 0) {
        let textIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        let isFirstRun = true;
        const typeSpeed = 90;
        const deleteSpeed = 55;
        const pauseAfterType = 2200;
        const pauseAfterDelete = 800;   // Cursor berkedip di kiri sebelum teks berikutnya
        const cursorBlinkFirst = 1200;  // Cursor berkedip dari kiri dulu sebelum mulai menulis

        function type() {
            const currentText = heroTexts[textIndex];

            // Fase 1: Cursor berkedip di kiri, lalu mulai menulis ke kanan
            if (isFirstRun) {
                isFirstRun = false;
                typingEl.textContent = '';
                charIndex = 0;
                setTimeout(type, cursorBlinkFirst);
                return;
            }

            if (isDeleting) {
                // Teks hilang dari kanan ke kiri (cursor mengikuti ke kiri)
                typingEl.textContent = currentText.substring(0, charIndex - 1);
                charIndex--;
                if (charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % heroTexts.length;
                    // Cursor berkedip di kiri sebentar, lalu teks berikutnya
                    setTimeout(type, pauseAfterDelete);
                    return;
                }
                setTimeout(type, deleteSpeed);
            } else {
                // Menulis dari kiri ke kanan (cursor di ujung kanan)
                typingEl.textContent = currentText.substring(0, charIndex + 1);
                charIndex++;
                if (charIndex === currentText.length) {
                    isDeleting = true;
                    setTimeout(type, pauseAfterType);
                    return;
                }
                setTimeout(type, typeSpeed);
            }
        }

        type();
    }

    // Hero background slideshow (dari Kelola Slide admin)
    const heroBgSlides = document.querySelectorAll('.hero-bg-slide');
    const heroBgDots = document.querySelectorAll('.hero-bg-dot');
    if (heroBgSlides.length > 1) {
        let heroBgIndex = 0;
        let heroBgTimer = null;

        function showHeroBgSlide(index) {
            heroBgIndex = index;
            heroBgSlides.forEach(function(slide, i) {
                slide.classList.toggle('opacity-100', i === index);
                slide.classList.toggle('opacity-0', i !== index);
            });
            heroBgDots.forEach(function(dot, i) {
                dot.classList.toggle('bg-white', i === index);
                dot.classList.toggle('w-8', i === index);
                dot.classList.toggle('bg-white/40', i !== index);
                dot.classList.toggle('w-2.5', i !== index);
            });
        }

        function nextHeroBgSlide() {
            showHeroBgSlide((heroBgIndex + 1) % heroBgSlides.length);
        }

        function startHeroBgAutoplay() {
            if (heroBgTimer) clearInterval(heroBgTimer);
            heroBgTimer = setInterval(nextHeroBgSlide, 6000);
        }

        heroBgDots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                showHeroBgSlide(parseInt(dot.dataset.heroDot, 10));
                startHeroBgAutoplay();
            });
        });

        startHeroBgAutoplay();
    }

    const slider = document.getElementById('homeContributorSlider');
    if (!slider) return;
    
    const prevBtn = document.getElementById('homePrevBtn');
    const nextBtn = document.getElementById('homeNextBtn');
    const dotsContainer = document.getElementById('homeDotsContainer');
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
