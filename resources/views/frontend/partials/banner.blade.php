<!-- Hero Slider -->
<section class="relative w-full overflow-hidden">
    <!-- Swiper Container -->
    <div class="swiper">
        <div class="swiper-wrapper">

            <!-- Slide 1 -->
            <div class="swiper-slide relative">
                <img src="{{ asset('storage/banner/home_banner.png') }}" 
                     class="w-full h-[450px] md:h-[550px] lg:h-[600px] object-cover object-center" 
                     alt="Banner 1">

                <!-- Dark Overlay -->
                <div class="absolute inset-0 bg-black/50"></div>

                <!-- Banner Text -->
                <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6">
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white drop-shadow-xl">
                        Discover the Essence of Sri Lanka
                    </h1>
                    <p class="mt-4 text-white/90 text-base md:text-lg max-w-2xl drop-shadow-md">
                        Spices, teas, handicrafts & more — source directly from Sri Lankan artisans.
                    </p>
                    <div class="mt-8">
                        <a href="#" 
                           class="inline-block bg-[#8a4b2b] hover:bg-[#6f3a23] text-white px-6 py-3 rounded-md font-semibold shadow-md transition duration-300">
                            Shop Now
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide relative">
                <img src="{{ asset('storage/banner/home_banner2.jpg') }}" 
                     class="w-full h-[450px] md:h-[550px] lg:h-[600px] object-cover object-center" 
                     alt="Banner 2">

                <!-- Dark Overlay -->
                <div class="absolute inset-0 bg-black/50"></div>

                <!-- Banner Text -->
                <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6">
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white drop-shadow-xl">
                        Taste the Flavors of Tradition
                    </h1>
                    <p class="mt-4 text-white/90 text-base md:text-lg max-w-2xl drop-shadow-md">
                        Experience the richness of Sri Lankan spices and authentic local flavors.
                    </p>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="swiper-slide relative">
                <img src="{{ asset('storage/banner/home_banner3.jpeg') }}" 
                     class="w-full h-[450px] md:h-[550px] lg:h-[600px] object-cover object-center" 
                     alt="Banner 3">

                <!-- Dark Overlay -->
                <div class="absolute inset-0 bg-black/50"></div>

                <!-- Banner Text -->
                <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6">
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white drop-shadow-xl">
                        Explore Sri Lankan Heritage
                    </h1>
                    <p class="mt-4 text-white/90 text-base md:text-lg max-w-2xl drop-shadow-md">
                        Discover timeless crafts, culture, and artistry from the heart of the island.
                    </p>
                </div>
            </div>

        </div>

        <!-- Pagination Dots -->
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Swiper CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Swiper Script -->
<script>
    const swiper = new Swiper('.swiper', {
        loop: true,
        autoplay: {
            delay: 3500, // 3.5 seconds between slides
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        effect: 'fade', // smooth fade transition
        speed: 1000,    // 1s fade duration
    });
</script>
