<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TradLanka - Authentic Sri Lankan Products</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    /* Dropdown Animation */
    .dropdown-menu {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      background-color: #fff8f4;
      color: #4a2b2b;
      border-radius: 0.5rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      min-width: 230px;
      z-index: 9999;
    }
    .dropdown-menu.show {
      display: block;
    }
  </style>
</head>

<body class="bg-[#f9f6f3] font-sans">

  <!--  Sticky Header -->
  <header class="fixed top-0 left-0 right-0 z-50 bg-[#5b2c2c] text-white shadow-lg">
    <div class="w-full flex items-center justify-between px-4 py-3 gap-4">

      <!-- LEFT: Logo + Nav -->
      <div class="flex items-center gap-6">
        <!-- Logo -->
        <div class="flex items-center gap-2">
          <img src="https://via.placeholder.com/40" alt="TradLanka"
               class="h-10 w-10 rounded-full bg-white p-1" />
          <h1 class="text-xl font-bold">
            Trad<span class="text-yellow-400">Lanka</span>
          </h1>
        </div>

        <!-- Nav Links -->
        <nav class="hidden md:flex items-center gap-5 text-sm font-medium relative">
          <!-- Dropdown -->
          <div class="relative">
            <button id="categoryBtn"
                    class="bg-[#8a4b2b] hover:bg-[#703b23] px-4 py-2 rounded flex items-center gap-2">
              <i class="fas fa-list"></i> All Categories
            </button>

            <!-- Dropdown Menu -->
            <div id="categoryDropdown" class="dropdown-menu">
              <div class="p-4 text-sm">
                <h4 class="font-semibold mb-2 text-[#5b2c2c]">Categories</h4>
                <ul class="space-y-1">
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Spices</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Teas</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Oil</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Coconut Products</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Ayurvedic Remedies</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Gifts & Hampers</a></li>
                </ul>

                <hr class="my-3 border-[#8a4b2b]" />

                <h4 class="font-semibold mb-2 text-[#5b2c2c]">Filters</h4>
                <ul class="space-y-1">
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Price: Low to High</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Price: High to Low</a></li>
                  <li><a href="#" class="block hover:text-[#8a4b2b]">Top Rated</a></li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Links -->
          <a href="#" class="hover:text-yellow-400">Shop</a>
          <a href="#" class="hover:text-yellow-400">Offers</a>
          <a href="#" class="hover:text-yellow-400">About</a>
          <a href="#" class="hover:text-yellow-400">Contact</a>
        </nav>
      </div>

      <!-- CENTER: Search Bar -->
      <div class="flex-1 mx-8 relative hidden lg:block">
        <input type="text"
               placeholder="Search for authentic Sri Lankan products..."
               class="w-full py-2 pl-5 pr-14 rounded-full text-gray-700 focus:outline-none shadow-inner bg-gray-100" />
        <button class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600 hover:text-green-800"
                title="Image Search">
          <i class="fas fa-camera text-xl"></i>
        </button>
      </div>

      <!-- RIGHT: Icons -->
      <div class="flex items-center gap-5 text-xl">
        <a href="#" title="Cart" class="hover:text-yellow-400 relative">
          <i class="fas fa-shopping-cart"></i>
          <span class="absolute -top-2 -right-3 bg-yellow-400 text-xs text-black rounded-full px-1">0</span>
        </a>
        <a href="#" title="Orders" class="hover:text-yellow-400">
          <i class="fas fa-box-open"></i>
        </a>
        <a href="#" title="Profile" class="hover:text-yellow-400">
          <i class="fas fa-user-circle"></i>
        </a>
      </div>
    </div>
  </header>

  <!--  Dropdown JavaScript -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const catBtn = document.getElementById("categoryBtn");
      const dropdown = document.getElementById("categoryDropdown");

      catBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdown.classList.toggle("show");
      });

      window.addEventListener("click", (e) => {
        if (!catBtn.contains(e.target) && !dropdown.contains(e.target)) {
          dropdown.classList.remove("show");
        }
      });
    });
  </script>

</body>
</html>
