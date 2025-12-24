@extends('layouts.frontend')

@section('content')

{{-- ========================================== --}}
{{--// 1. HERO BANNER --}}
{{-- ========================================== --}}
<div class="relative w-full h-[500px] bg-cover bg-center bg-no-repeat bg-fixed" 
     style="background-image: url('{{ asset('storage/banner/about-us-hero.jpg') }}');">
    
    {{-- Dark Overlay --}}
    <div class="absolute inset-0 bg-[#1f1010] bg-opacity-60 flex flex-col justify-center items-center text-center px-4">
        {{-- ANIMATION: Fade Down --}}
        <h1 class="text-5xl md:text-6xl font-extrabold text-white drop-shadow-xl mb-4 tracking-tight" 
            data-aos="fade-down" data-aos-duration="1000">
            Our Story
        </h1>
        {{-- ANIMATION: Fade Up (Delayed) --}}
        <p class="text-gray-200 text-lg md:text-2xl max-w-2xl font-light drop-shadow-md" 
           data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            Bringing the authentic essence of Ceylon to the world.
        </p>
    </div>
</div>

{{-- ========================================== --}}
{{-- 2. WHO WE ARE (Dark Section) --}}
{{-- ========================================== --}}
<div class="relative w-full bg-[#1f1010] py-20 overflow-hidden">
    <div class="container mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            
            {{-- Text Content --}}
            {{-- ANIMATION: Slide in from Left --}}
            <div data-aos="fade-right" data-aos-duration="1000">
                <h4 class="text-[#d97706] font-bold uppercase tracking-widest mb-3 text-sm">Who We Are</h4>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-6 leading-tight">
                    Discover the Soul of <br> Sri Lanka
                </h2>
                <p class="text-gray-300 leading-relaxed mb-6 text-justify font-normal text-lg">
                    Welcome to <strong>TradLanka</strong>, your gateway to the finest products from the pearl of the Indian Ocean. 
                    We were born out of a passion for preserving Sri Lanka's rich heritage and sharing its natural treasures with the world.
                </p>
                
                <a href="{{ url('/') }}" class="inline-block border border-[#d97706] text-[#d97706] hover:bg-[#d97706] hover:text-white px-8 py-3 rounded-full font-semibold transition duration-300 ease-in-out transform hover:-translate-y-1">
                    Explore Our Collection
                </a>
            </div>

            {{-- Image Grid --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- ANIMATION: Zoom in with delay --}}
                <img src="{{ asset('storage/images/about/story-1.jpeg') }}" 
                     class="w-full h-80 object-cover rounded-xl shadow-2xl transform translate-y-12 border-4 border-[#2d1a1a]"
                     alt="Sri Lankan Heritage"
                     data-aos="zoom-in-up" data-aos-duration="1000">
                
                {{-- ANIMATION: Zoom in with more delay --}}
                <img src="{{ asset('storage/images/about/story-2.jpg') }}" 
                     class="w-full h-80 object-cover rounded-xl shadow-2xl border-4 border-[#2d1a1a]"
                     alt="Authentic Spices"
                     data-aos="zoom-in-up" data-aos-delay="200" data-aos-duration="1000">
            </div>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- 3. MIDDLE BANNER (Parallax Window) --}}
{{-- ========================================== --}}
<div class="relative w-full h-[400px] bg-cover bg-center bg-no-repeat bg-fixed" 
     style="background-image: url('{{ asset('storage/images/background.jpg') }}');">
    
    <div class="absolute inset-0 bg-[#1f1010] bg-opacity-70 flex items-center justify-center">
        {{-- ANIMATION: Zoom In --}}
        <h2 class="text-3xl md:text-5xl font-bold text-white text-center px-4 border-b-2 border-[#d97706] pb-4"
            data-aos="zoom-in" data-aos-duration="800">
            Pure. Ethical. Authentic.
        </h2>
    </div>
</div>

{{-- ========================================== --}}
{{-- 4. VALUES SECTION --}}
{{-- ========================================== --}}
<div class="bg-[#1a0e0e] py-20">
    <div class="container mx-auto px-6 lg:px-12 text-center">
        <h2 class="text-3xl font-bold text-white mb-16" data-aos="fade-up">Why Choose TradLanka?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
            
            {{-- Card 1: Fades Up --}}
            <div class="bg-[#2d1a1a] p-8 rounded-2xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:-translate-y-2 border border-[#3e2525]"
                 data-aos="fade-up" data-aos-delay="0" data-aos-duration="800">
                <div class="w-16 h-16 bg-[#d97706]/20 rounded-full flex items-center justify-center mx-auto mb-6 text-[#d97706]">
                    <i class="fas fa-leaf text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">100% Authentic</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Sourced directly from local farmers and artisans.
                </p>
            </div>

            {{-- Card 2: Fades Up (Delayed) --}}
            <div class="bg-[#2d1a1a] p-8 rounded-2xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:-translate-y-2 border border-[#3e2525]"
                 data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
                <div class="w-16 h-16 bg-[#d97706]/20 rounded-full flex items-center justify-center mx-auto mb-6 text-[#d97706]">
                    <i class="fas fa-hands-helping text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Community First</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    We empower local communities by ensuring fair trade.
                </p>
            </div>

            {{-- Card 3: Fades Up (More Delayed) --}}
            <div class="bg-[#2d1a1a] p-8 rounded-2xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:-translate-y-2 border border-[#3e2525]"
                 data-aos="fade-up" data-aos-delay="400" data-aos-duration="800">
                <div class="w-16 h-16 bg-[#d97706]/20 rounded-full flex items-center justify-center mx-auto mb-6 text-[#d97706]">
                    <i class="fas fa-shipping-fast text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Global Delivery</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    We bring the taste of Sri Lanka to your doorstep.
                </p>
            </div>
        </div>

     {{-- CTA Section: Fades Up Big --}}
<div class="relative rounded-3xl overflow-hidden shadow-2xl bg-cover bg-center"
     style="background-image: url('{{ asset('storage/images/about/signature-bg.jpg') }}');"
     data-aos="flip-up" data-aos-duration="1000">
    
    {{-- Dark Overlay (Adjust opacity '/70' to make it darker or lighter) --}}
    <div class="absolute inset-0 bg-[#1f1010]/70"></div>

    {{-- Content (Wrapped in relative z-10 to sit on top of the overlay) --}}
    <div class="relative z-10 p-10 md:p-16 text-white text-center">
        
        <h2 class="text-2xl md:text-4xl font-bold mb-6">
            Explore Our Signature Collection
        </h2>
        <p class="text-gray-200 mb-8 max-w-xl mx-auto text-lg">
            Hand-picked for their exceptional quality. Experience the world-renowned flavor of pure Ceylon Tea.
        </p>
        
        <a href="{{ url('category/tea') }}" class="inline-block bg-white text-[#5b2c2c] px-10 py-4 rounded-full font-bold hover:bg-gray-100 transition shadow-lg">
            View Signature Products
        </a>
    </div>
</div>

{{-- 
    ========================================== 
    REQUIRED SCRIPTS FOR ANIMATION (AOS)
    ========================================== 
--}}
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            once: true, // Animation happens only once - while scrolling down
            offset: 100, 
        });
    });
</script>

@endsection