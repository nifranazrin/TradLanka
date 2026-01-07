<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TradLanka | Discover the Essence of Sri Lanka</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Sticky Header */
        .sticky-header { position: fixed; top: 0; width: 100%; z-index: 50; transition: all 0.3s ease-in-out; }
        .hidden-header { transform: translateY(-100%); }
        .no-scroll { overflow: hidden; }
        
        /* Modal Tabs - Brand Maroon Colors */
        .tab-active { color: #5b2c2c; border-bottom: 2px solid #5b2c2c; }
        .tab-inactive { color: #9ca3af; border-bottom: 2px solid transparent; }
        .tab-inactive:hover { color: #5b2c2c; }


    </style>
    <script src="https://accounts.google.com/gsi/client" async defer></script> 
</head>

<body class="bg-gray-50 text-gray-800 antialiased">

    @include('frontend.partials.header')

    <main class="pt-[130px] min-h-screen"> 
        @yield('content')
    </main>

    @include('frontend.partials.support')

    @include('frontend.partials.footer')

    {{-- ========================================== --}}
    {{--      FLOATING ACTION BUTTONS (Global)      --}}
    {{-- ========================================== --}}
    <div class="fixed bottom-6 right-6 flex flex-col items-center space-y-3 z-50">
        
        {{-- Chatbot Button --}}
        <a href="#" id="chatbotBtn" class="bg-[#5b2c2c] text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform duration-300">
            <i class="fas fa-robot text-xl"></i>
        </a>

        {{-- WhatsApp Button (Updated with your number) --}}
        <a href="https://wa.me/94757679793?text=Hello%20TradLanka%2C%20I%20am%20interested%20in%20your%20products." 
           target="_blank" 
           class="bg-[#25D366] text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform duration-300">
            <i class="fab fa-whatsapp text-2xl"></i>
        </a>
    </div>

    {{-- ========================================== --}}
    {{--           AUTHENTICATION MODAL             --}}
    {{-- ========================================== --}}
    <div id="authModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border-t-4 border-[#5b2c2c]">
                    
                    <button type="button" onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-[#5b2c2c] z-20 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="flex border-b border-gray-100">
                        <button id="btn-tab-login" onclick="switchTab('login')" class="w-1/2 py-4 text-center font-bold text-lg tab-active focus:outline-none transition-colors">
                            Login
                        </button>
                        <button id="btn-tab-register" onclick="switchTab('register')" class="w-1/2 py-4 text-center font-bold text-lg tab-inactive focus:outline-none transition-colors">
                            Sign Up
                        </button>
                    </div>

                    <div class="px-8 py-8">
                        
                        {{-- LOGIN FORM --}}
                        <div id="login-content">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-extrabold text-[#5b2c2c]">Customer Login</h3>
                                <p class="text-sm text-gray-500 mt-1 font-medium">
                                    Welcome back to 
                                    <span class="text-yellow-500 font-bold">Trad</span><span class="text-[#5b2c2c] font-bold">Lanka</span>
                                </p>
                            </div>
                            
                            <form id="ajax-login-form" class="space-y-5">
                                @csrf
                                <div>
                                    <input type="email" name="email" placeholder="Please enter your Phone or Email" required 
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition placeholder-gray-400 text-sm">
                                </div>
                                
                                <div class="relative">
                                    <input type="password" name="password" id="login-password" placeholder="Please enter your password" required 
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition placeholder-gray-400 text-sm pr-10">
                                    
                                    <span onclick="togglePassword('login-password', 'login-eye-svg')" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-[#5b2c2c]">
                                        <svg id="login-eye-svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </span>
                                </div>

                                <div class="flex justify-end text-xs">
                                    <a href="#" class="text-gray-500 hover:text-[#5b2c2c]">Forgot password?</a>
                                </div>

                                <button type="submit" class="w-full bg-[#5b2c2c] hover:bg-[#4a2424] text-white py-3 rounded-lg font-bold text-sm shadow-md hover:shadow-lg transition transform active:scale-95 uppercase tracking-wide">
                                    LOGIN
                                </button>
                            </form>

                            <div class="mt-6 text-center text-sm text-gray-600">
                                Don't have an account? 
                                <a href="javascript:void(0);" onclick="switchTab('register')" class="text-[#5b2c2c] font-bold hover:underline">Create an account</a>
                            </div>
                            
                            <div class="mt-8 text-center">
                                    <div class="relative mb-6">
                                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                                        <div class="relative flex justify-center text-xs"><span class="px-2 bg-white text-gray-400">or login with</span></div>
                                    </div>
                                    
                                    <div class="flex justify-center">
                                        <div id="g_id_onload"
                                            data-client_id="603543448678-jsm9dq38dkm60uh5tm0evlvd2bnhh5p7.apps.googleusercontent.com"
                                            data-callback="handleCredentialResponse"
                                            data-auto_prompt="false">
                                        </div>
                                        <div class="g_id_signin" 
                                            data-type="standard" 
                                            data-size="large" 
                                            data-theme="outline" 
                                            data-text="signin_with" 
                                            data-shape="pill" 
                                            data-logo_alignment="left">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                           {{-- REGISTER FORM --}}
                            <div id="register-content" class="hidden flex flex-col items-center"> {{-- Flex container for centering --}}
                                
                                {{-- Header Section --}}
                                <div class="text-center mb-8 w-full px-6">
                                    <h3 class="text-3xl font-extrabold text-[#5b2c2c] tracking-tight uppercase">Create Account</h3>
                                    <p class="text-sm text-gray-500 mt-1 font-medium italic">Join the TradLanka family</p>
                                </div>

                                {{-- Form Container - Constrained width to prevent "Covering" the edges --}}
                                <form id="ajax-register-form" class="w-full max-w-[340px] space-y-5 pb-8"> {{-- max-width centers the boxes --}}
                                    @csrf
                                    
                                    {{-- Full Name --}}
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                                        <div class="relative group">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-[#5b2c2c]">
                                                <i class="fas fa-user text-xs"></i>
                                            </span>
                                            <input type="text" name="name" placeholder="John Doe" required 
                                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition placeholder-gray-300 text-sm">
                                        </div>
                                    </div>

                                    {{-- Email Address --}}
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                                        <div class="relative group">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-[#5b2c2c]">
                                                <i class="fas fa-envelope text-xs"></i>
                                            </span>
                                            <input type="email" name="email" placeholder="name@example.com" required 
                                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition placeholder-gray-300 text-sm">
                                        </div>
                                    </div>

                                    {{-- Password --}}
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Password</label>
                                        <div class="relative group">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-[#5b2c2c]">
                                                <i class="fas fa-lock text-xs"></i>
                                            </span>
                                            <input type="password" name="password" id="reg-password" placeholder="••••••••" required 
                                                class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-200 bg-white focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition placeholder-gray-300 text-sm">
                                            <span onclick="togglePassword('reg-password', 'reg-eye-svg')" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-[#5b2c2c]">
                                                <svg id="reg-eye-svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Confirm Password --}}
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Confirm Password</label>
                                        <div class="relative group">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-[#5b2c2c]">
                                                <i class="fas fa-shield-alt text-xs"></i>
                                            </span>
                                            <input type="password" name="password_confirmation" id="reg-confirm" placeholder="••••••••" required 
                                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition placeholder-gray-300 text-sm">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="w-full bg-[#5b2c2c] hover:bg-[#4a2424] text-white py-4 rounded-xl font-bold text-sm shadow-md hover:shadow-xl transition transform active:scale-95 uppercase tracking-widest mt-6">
                                        CREATE ACCOUNT
                                    </button>

                                    <div class="mt-6 text-center text-sm text-gray-600 font-medium">
                                        Already have an account? 
                                        <a href="javascript:void(0);" onclick="switchTab('login')" class="text-[#5b2c2c] font-bold hover:underline">Sign In</a>
                                    </div>
                                </form>
                            </div>
                      </div>
            </div>
        </div>
    </div>

    <script>

        function handleCredentialResponse(response) {
    // Show a loading spinner using SweetAlert2
    Swal.fire({
        title: 'Verifying Google Account...',
        didOpen: () => { Swal.showLoading() },
        allowOutsideClick: false
    });

    // Send the token to your Laravel Backend
    $.ajax({
        url: "{{ route('login.google') }}", // Ensure you create this route in web.php
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { 
            token: response.credential 
        },
        success: function(data) {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Welcome!',
                text: 'Google login successful.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                checkPendingCartItem(); // Re-use your existing logic
            });
        },
        error: function(xhr) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Authentication Failed', 
                text: 'We could not log you in with Google. Please try again.' 
            });
        }
    });
}
        // 1. HEADER SCROLL LOGIC
        let lastScroll = 0;
        const header = document.getElementById("mainHeader");
        if(header) {
            window.addEventListener("scroll", () => {
                const currentScroll = window.pageYOffset;
                if (currentScroll > lastScroll && currentScroll > 100) {
                    header.classList.add("hidden-header");
                } else {
                    header.classList.remove("hidden-header");
                }
                lastScroll = currentScroll;
            });
        }

        // 2. MODAL & TAB LOGIC
        function openAuthModal(tab = 'login') {
            document.getElementById('authModal').classList.remove('hidden');
            document.body.classList.add('no-scroll');
            switchTab(tab);
        }

        function closeModal() {
            document.getElementById('authModal').classList.add('hidden');
            document.body.classList.remove('no-scroll');
            // Reset forms
            document.getElementById('ajax-login-form').reset();
            document.getElementById('ajax-register-form').reset();
        }

        function switchTab(tab) {
            const btnLogin = document.getElementById('btn-tab-login');
            const btnRegister = document.getElementById('btn-tab-register');
            const contentLogin = document.getElementById('login-content');
            const contentRegister = document.getElementById('register-content');

            if(tab === 'login') {
                btnLogin.classList.add('tab-active');
                btnLogin.classList.remove('tab-inactive');
                btnRegister.classList.add('tab-inactive');
                btnRegister.classList.remove('tab-active');
                contentLogin.classList.remove('hidden');
                contentRegister.classList.add('hidden');
            } else {
                btnRegister.classList.add('tab-active');
                btnRegister.classList.remove('tab-inactive');
                btnLogin.classList.add('tab-inactive');
                btnLogin.classList.remove('tab-active');
                contentRegister.classList.remove('hidden');
                contentLogin.classList.add('hidden');
            }
        }

        // --- PASSWORD VISIBILITY TOGGLE (SVG) ---
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const iconSvg = document.getElementById(iconId);
            
            // Eye Slash SVG path
            const slashPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
            // Eye SVG path
            const eyePath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';

            if (input.type === "password") {
                input.type = "text";
                iconSvg.innerHTML = slashPath;
            } else {
                input.type = "password";
                iconSvg.innerHTML = eyePath;
            }
        }

        // 3. HELPER: Update Cart Badge
        function updateCartIconGlobal(count) {
            const badge = document.getElementById('cart-badge'); 
            if(badge) {
                badge.innerText = count;
                badge.classList.remove('hidden');
            }
        }

          // 4. CHECK PENDING ITEM (UPDATED LOGIC)
        function checkPendingCartItem() {
            const pendingItemStr = localStorage.getItem('pendingCartItem');
            
            if (pendingItemStr) {
                // If there IS a pending item, add it to cart via AJAX
                const item = JSON.parse(pendingItemStr);
                
                const Toast = Swal.mixin({
                    toast: true, position: 'bottom-end', showConfirmButton: false, timer: 3000
                });
                Toast.fire({ icon: 'info', title: 'Adding your item to cart...' });

                $.ajax({
                    url: "{{ route('cart.add') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                    contentType: 'application/json',
                    data: JSON.stringify({ product_id: item.id, product_qty: item.qty }),
                    success: function(data) {
                        localStorage.removeItem('pendingCartItem');
                        if (data.status === 'success' || data.status === 'exists') {
                            Toast.fire({ icon: 'success', title: data.message });
                            // Reload to update header
                            setTimeout(() => location.reload(), 1000); 
                        }
                    },
                    error: function() {
                        // Even if adding fails, reload to show logged-in state
                        location.reload();
                    }
                });
            } else {
                // If NO pending item, just reload to update header (Name, Auth state)
                setTimeout(() => location.reload(), 500);
            }
        }

        // 5. JQUERY DOCUMENT READY
        $(document).ready(function() {
            
            if (typeof jQuery !== 'undefined') {
                $.fn.modal = function(action) {
                    if(action === 'show') openAuthModal('login');
                    if(action === 'hide') closeModal();
                };
            }

           $('#ajax-login-form').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.ajax({
            url: "{{ route('login.popup') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                closeModal();

                // ✅ Check if login was triggered by "Add to Cart"
                if (response.is_cart_login) {
                    // Show Maroon-themed alert for Cart Logins
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome Back!',
                        text: response.message, // "Login successful & Item added to cart!"
                        timer: 2500,
                        showConfirmButton: false,
                        // ✅ Trigger the Maroon & Gold CSS you defined
                        customClass: { 
                            popup: 'cart-alert-popup' 
                        }
                    }).then(() => {
                        // ✅ CRITICAL: Sync the guest's clicked item to the database
                        checkPendingCartItem(); 
                    });
                } else {
                    // ✅ NORMAL LOGIN: Simple "Welcome Back" alert
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome Back!',
                        text: 'Login successful.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Refresh to show user name in header
                    });
                }
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Login Failed', text: 'Please check your credentials.' });
            }
        });
    });

    // --- AJAX REGISTER ---
    $('#ajax-register-form').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.ajax({
            url: "{{ route('register.popup') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                closeModal();
                
                // ✅ Decision logic for registration intent
                if (response.is_cart_login) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome to TradLanka!',
                        text: response.message, 
                        timer: 2500,
                        showConfirmButton: false,
                        // ✅ Trigger the Maroon & Gold CSS
                        customClass: { 
                            popup: 'cart-alert-popup' 
                        }
                    }).then(() => {
                        // ✅ Sync the guest's clicked item to the database
                        checkPendingCartItem();
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome!',
                        text: 'Registration successful.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                let msg = 'Registration failed.';
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
            }
        });
    });
});
    </script>
</body>
</html>