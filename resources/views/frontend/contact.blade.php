@extends('layouts.frontend')

@section('content')

{{-- ========================================== --}}
{{-- 1. HERO BANNER (Parallax + Animation) --}}
{{-- ========================================== --}}
<div class="relative w-full h-[400px] bg-cover bg-center bg-no-repeat bg-fixed" 
     style="background-image: url('{{ asset('storage/banner/about-us-hero.jpg') }}');">
    
    {{-- Dark Overlay --}}
    <div class="absolute inset-0 bg-[#1f1010] bg-opacity-70 flex flex-col justify-center items-center text-center px-4">
        <h1 class="text-4xl md:text-6xl font-extrabold text-white drop-shadow-xl mb-4 tracking-tight" 
            data-aos="fade-down" data-aos-duration="1000">
            Get in Touch
        </h1>
        <p class="text-gray-300 text-lg md:text-2xl max-w-2xl font-light drop-shadow-md" 
           data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            We'd love to hear from you. Here is how you can reach us.
        </p>
    </div>
</div>

{{-- ========================================== --}}
{{-- 2. CONTACT INFO CARDS (Pop-up Animation) --}}
{{-- ========================================== --}}
<div class="relative z-10 bg-[#1f1010] -mt-10 pb-20">
    <div class="container mx-auto px-6 lg:px-12">
        
        {{-- Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 transform -translate-y-10">
            
            {{-- Phone Card --}}
            <div class="bg-[#2d1a1a] p-8 rounded-xl shadow-2xl border border-[#3e2525] text-center group hover:-translate-y-2 transition duration-300"
                 data-aos="fade-up" data-aos-delay="0">
                <div class="w-14 h-14 bg-[#d97706]/20 rounded-full flex items-center justify-center mx-auto mb-4 text-[#d97706] group-hover:bg-[#d97706] group-hover:text-white transition">
                    <i class="fas fa-phone-alt text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Call Us</h3>
                <p class="text-gray-400 text-sm">Mon-Fri from 8am to 5pm.</p>
                <a href="tel:+94757679793" class="block mt-3 text-[#d97706] font-bold hover:underline text-lg">
                    +94 75 767 9793
                </a>
            </div>

            {{-- Email Card --}}
            <div class="bg-[#2d1a1a] p-8 rounded-xl shadow-2xl border border-[#3e2525] text-center group hover:-translate-y-2 transition duration-300"
                 data-aos="fade-up" data-aos-delay="100">
                <div class="w-14 h-14 bg-[#d97706]/20 rounded-full flex items-center justify-center mx-auto mb-4 text-[#d97706] group-hover:bg-[#d97706] group-hover:text-white transition">
                    <i class="fas fa-envelope text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Email Us</h3>
                <p class="text-gray-400 text-sm">Our friendly team is here to help.</p>
                <a href="mailto:infotradlanka@gmail.com" class="block mt-3 text-[#d97706] font-bold hover:underline text-lg">
                    infotradlanka@gmail.com
                </a>
            </div>

            {{-- Location Card --}}
            <div class="bg-[#2d1a1a] p-8 rounded-xl shadow-2xl border border-[#3e2525] text-center group hover:-translate-y-2 transition duration-300"
                 data-aos="fade-up" data-aos-delay="200">
                <div class="w-14 h-14 bg-[#d97706]/20 rounded-full flex items-center justify-center mx-auto mb-4 text-[#d97706] group-hover:bg-[#d97706] group-hover:text-white transition">
                    <i class="fas fa-map-marker-alt text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Visit Us</h3>
                <p class="text-gray-400 text-sm">Come say hello at our office.</p>
                <p class="block mt-3 text-[#d97706] font-bold text-lg">
                    Colombo, Sri Lanka
                </p>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- 3. FORM & MAP SECTION --}}
        {{-- ========================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mt-10">
            
            {{-- Contact Form --}}
            <div class="bg-[#2d1a1a] p-8 md:p-10 rounded-2xl shadow-lg border border-[#3e2525]" 
                 data-aos="fade-right" data-aos-duration="1000">
                
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Send us a Message</h2>
                    <p class="text-gray-400">We will get back to you within 24 hours.</p>
                </div>

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-900/50 border border-red-500 text-red-200 rounded-lg">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    
                    {{-- 
                        DYNAMIC SELLER ID: 
                        If the user is asking about a product, this will notify that specific seller.
                        If it's a general message, it notifies the admin.
                    --}}
                    <input type="hidden" name="seller_id" value="{{ $product->seller_id ?? $seller_id ?? '' }}">

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full bg-[#1f1010] border border-[#3e2525] rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d97706] transition" placeholder="John" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full bg-[#1f1010] border border-[#3e2525] rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d97706] transition" placeholder="Doe" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-[#1f1010] border border-[#3e2525] rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d97706] transition" placeholder="you@company.com" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Message</label>
                            <textarea name="message" rows="4" class="w-full bg-[#1f1010] border border-[#3e2525] rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d97706] transition" placeholder="How can we help you?" required>{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="w-full bg-[#d97706] hover:bg-[#b45309] text-white font-bold py-4 rounded-lg transition transform hover:-translate-y-1 shadow-lg">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>

            {{-- Google Map --}}
            <div class="relative h-[500px] lg:h-auto rounded-2xl overflow-hidden shadow-lg border border-[#3e2525]" 
                 data-aos="fade-left" data-aos-duration="1000">
                {{-- Updated to a real Colombo Embed Map --}}
                    <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126743.58285461942!2d79.78616393910393!3d6.921837369654158!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2593cf65a1e9d%3A0xe13843d87595a17e!2sColombo!5e0!3m2!1sen!2slk!4v1715600000000!5m2!1sen!2slk" 
                    width="100%" 
                    height="100%" 
                    style="border:0; filter: grayscale(100%) invert(92%) contrast(83%);" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <div class="absolute bottom-4 left-4 bg-white p-4 rounded-lg shadow-lg">
                    <p class="text-[#5b2c2c] font-bold text-sm">Find us in Colombo!</p>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- 4. FAQ SECTION (Accordion Style) --}}
        {{-- ========================================== --}}
        <div class="mt-20 max-w-4xl mx-auto" data-aos="fade-up" data-aos-duration="1000">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-12">
                Frequently Asked Questions
            </h2>

            <div class="space-y-4">
                {{-- FAQ Item 1 --}}
                <div class="bg-[#2d1a1a] rounded-lg border border-[#3e2525] overflow-hidden">
                    <button class="w-full px-6 py-4 flex justify-between items-center focus:outline-none bg-[#2d1a1a] hover:bg-[#3e2525] transition" onclick="toggleFAQ(this)">
                        <span class="text-lg font-medium text-white flex items-center gap-3">
                            <i class="fas fa-tag text-[#d97706]"></i>
                            What are the payment methods available?
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                    </button>
                    <div class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-[#1f1010]">
                        <p class="px-6 py-4 text-gray-400 leading-relaxed">
                            We accept all major credit/debit cards (Visa, MasterCard), bank transfers, and cash on delivery (COD) for local orders.
                        </p>
                    </div>
                </div>

                {{-- FAQ Item 2 --}}
                <div class="bg-[#2d1a1a] rounded-lg border border-[#3e2525] overflow-hidden">
                    <button class="w-full px-6 py-4 flex justify-between items-center focus:outline-none bg-[#2d1a1a] hover:bg-[#3e2525] transition" onclick="toggleFAQ(this)">
                        <span class="text-lg font-medium text-white flex items-center gap-3">
                            <i class="fas fa-shipping-fast text-[#d97706]"></i>
                            How long does it take to deliver an order?
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                    </button>
                    <div class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-[#1f1010]">
                        <p class="px-6 py-4 text-gray-400 leading-relaxed">
                            Standard delivery takes 2-3 business days within Colombo and 3-5 business days for other areas in Sri Lanka.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- REQUIRED SCRIPTS --}}
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Initialize AOS
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({ once: true, offset: 100 });
    });

    // FAQ Toggle Logic
    function toggleFAQ(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('.fa-chevron-down');

        if (content.style.maxHeight) {
            content.style.maxHeight = null;
            icon.classList.remove('rotate-180');
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
            icon.classList.add('rotate-180');
        }
    }

    // SweetAlert Success Logic
    @if(session('success'))
        Swal.fire({
            title: 'Message Sent!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#d97706',
            background: '#2d1a1a',
            color: '#ffffff'
        });
    @endif
</script>

<style>
    .rotate-180 { transform: rotate(180deg); }
</style>

@endsection