<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TradLanka | Discover the Essence of Sri Lanka</title>

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">

    <style>
        /* Sticky Header on Scroll */
        .sticky-header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 50;
            transition: all 0.3s ease-in-out;
        }
        .hidden-header {
            transform: translateY(-100%);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">

    <!-- Header -->
    @include('frontend.partials.header')

    <main class="pt-[130px]"> {{-- Prevent header overlap --}}
        @yield('content')
    </main>

    <!-- Support Section -->
    @include('frontend.partials.support')

    <!-- Footer -->
    @include('frontend.partials.footer')

    <script>
        // Scroll-based sticky header logic
        let lastScroll = 0;
        const header = document.getElementById("mainHeader");
        window.addEventListener("scroll", () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > lastScroll && currentScroll > 100) {
                header.classList.add("hidden-header");
            } else {
                header.classList.remove("hidden-header");
            }
            lastScroll = currentScroll;
        });
    </script>
</body>
</html>
