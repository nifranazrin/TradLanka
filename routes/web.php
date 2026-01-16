<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


// FRONTEND CONTROLLERS
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CartController; 
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Session;
// AUTH CONTROLLERS
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Auth\AuthPopupController;
use App\Http\Controllers\Auth\StaffForgotPasswordController;

// USER CONTROLLERS
use App\Http\Controllers\User\UserDashController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Frontend\UserReviewController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\AdminDashController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\BannerController; 
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SellerAnalyticsController;
use App\Http\Controllers\Admin\AdminReviewController;

// SELLER CONTROLLERS
use App\Http\Controllers\StaffRegistrationController;
use App\Http\Controllers\Seller\SellerDashController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\ChatController as SellerChatController;
use App\Http\Controllers\Seller\SellerReportController;

// DELIVERY CONTROLLERS
use App\Http\Controllers\Delivery\DeliveryDashController;
use App\Http\Controllers\Delivery\DeliveryOrderController;
use App\Http\Controllers\Delivery\ChatController;
use App\Http\Controllers\Delivery\DeliveryProfileController;

// MIDDLEWARES
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\DeliveryPersonMiddleware;
use App\Http\Middleware\CustomerMiddleware;

//imgage based search
use App\Http\Controllers\ImageSearchController;

use App\Http\Controllers\MailTestController;

// =============================================================
//                    FRONTEND ROUTES (Public)
// =============================================================

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/product/{product:slug}', [FrontendProductController::class, 'show'])->name('product.show');
Route::get('/category/{slug}', [FrontendController::class, 'productsByCategory'])->name('categories.show');
Route::get('/search', [FrontendController::class, 'searchPage'])->name('search.page');
Route::get('/about-us', [FrontendController::class, 'about'])->name('about');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::post('/contact/submit', [FrontendController::class, 'submitContact'])->name('contact.submit');
Route::get('/track-order', [App\Http\Controllers\FrontendController::class, 'trackOrder'])->name('track.order');
Route::post('/login/google', [GoogleController::class, 'handleGoogleLogin'])->name('login.google');
Route::get('/test-order-email', [MailTestController::class, 'sendTestEmail']);
// Example fix in web.php



// Route to store the product review

Route::post('/product/review/store', [App\Http\Controllers\Frontend\ProductController::class, 'storeReview'])->name('review.store')->middleware('auth');
Route::get('/stripe-success', [CheckoutController::class, 'stripeSuccess'])->name('stripe.success');
// 1. Requesting the Link (Forgot Password Page)
Route::get('staff/password/reset', [StaffForgotPasswordController::class, 'showLinkRequestForm'])->name('staff.password.request');
Route::post('staff/password/email', [StaffForgotPasswordController::class, 'sendResetLinkEmail'])->name('staff.password.email');

// 2. Resetting the Password (The Link in the Email)

Route::get('staff/password/reset/{token}', [StaffForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('staff/password/reset', [StaffForgotPasswordController::class, 'reset'])->name('password.update');


// GLOBAL CUSTOMER LOGOUT
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// =============================================================
//                    CART SYSTEM ROUTES
// =============================================================

Route::post('/add-to-cart', [CartController::class, 'addProduct'])->name('cart.add');
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.show');
Route::post('/cart/update-quantity', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/delete-items', [CartController::class, 'deleteCart'])->name('cart.delete');
Route::get('stripe-success', [CheckoutController::class, 'stripeSuccess'])->name('stripe.success');
Route::post('/cart/save-intent', [App\Http\Controllers\Frontend\CartController::class, 'saveIntent'])->name('cart.save-intent');

// Save cart items to session before checkout
Route::post('/cart/checkout-items', function (Request $request) {
    session(['checkout_cart_ids' => $request->ids]);
    return response()->json(['status' => 'ok']);
})->name('cart.checkout.store')->middleware('auth');

// =============================================================
//                    CHECKOUT ROUTES (Protected)
// =============================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.placeorder');
});

// =============================================================
//                    AUTHENTICATION ROUTES
// =============================================================


// POPUP AUTH (AJAX)
Route::post('/login-popup', [AuthPopupController::class, 'login'])->name('login.popup');
Route::post('/register-popup', [AuthPopupController::class, 'register'])->name('register.popup');

// STAFF LOGIN (Admin / Seller / Delivery)
Route::get('/staff/login', [StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/staff/login', [StaffLoginController::class, 'login'])->name('staff.login.submit');
Route::post('/staff/logout', [StaffLoginController::class, 'logout'])->name('staff.logout');

// S REGISTRATION (Public)
Route::get('/seller/register', [StaffRegistrationController::class, 'showForm'])->name('seller.register');
Route::post('/seller/register', [StaffRegistrationController::class, 'submitForm'])->name('seller.register.submit');


//imgage based search
Route::get('/image-search', [ImageSearchController::class, 'index'])->name('search.index');
Route::post('/image-search', [ImageSearchController::class, 'search'])->name('search.process');

// =============================================================
//                    CUSTOMER DASHBOARD (Protected)
// =============================================================

Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    
    // Dashboard & Profile
    Route::get('/dashboard', [UserDashController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    
    // Address Book
    Route::get('/profile/address', [ProfileController::class, 'address'])->name('profile.address');
    Route::put('/profile/address/update', [ProfileController::class, 'updateAddress'])->name('profile.address.update');


      // 1. The Main "My Reviews" Dashboard (Tabs)
    Route::get('/reviews', [UserReviewController::class, 'index'])->name('reviews.index');
    
    // 2. The page to write a review for a specific product
    Route::get('/reviews/create/{product_id}', [UserReviewController::class, 'create'])->name('reviews.create');
    Route::post('/notifications/mark-all-read', [UserReviewController::class, 'markAllRead'])
         ->name('notifications.markAllRead');
         
    // 3. Save the review to database
    Route::post('/reviews/store', [UserReviewController::class, 'store'])->name('reviews.store');
    // Orders
    Route::get('/orders', [ProfileController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{id}', [ProfileController::class, 'viewOrder'])->name('orders.show');
    Route::put('/orders/cancel/{id}', [ProfileController::class, 'cancelOrder'])->name('orders.cancel');


});

// =============================================================
//               CURRENCY & GLOBAL SETTINGS (TOP)
// =============================================================
Route::get('/set-currency/{currency}', function ($currency) {
    if (in_array($currency, ['LKR', 'USD'])) {
        session(['currency' => $currency]);
    }
    return redirect()->back();
});

// =============================================================
//               ADMIN DASHBOARD (Protected)
// =============================================================
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminMiddleware::class])->group(function () {

    Route::get('/dashboard', [AdminDashController::class, 'dashboard'])->name('dashboard');

    // --- Resources ---
    Route::resource('categories', CategoryController::class);

    // --- Banner Management ---
    Route::get('/website-content/banner', [BannerController::class, 'edit'])->name('banner.edit');
    Route::put('/website-content/banner', [BannerController::class, 'update'])->name('banner.update');

    // --- User (Staff/Seller) Approvals & Notifications ---
    Route::get('/user-requests', [UserApprovalController::class, 'index'])->name('seller.requests');
    Route::post('/user-requests/{id}/approve', [UserApprovalController::class, 'approve'])->name('seller.approve');
    Route::post('/user-requests/{id}/reject', [UserApprovalController::class, 'reject'])->name('seller.reject');
    Route::put('/user-requests/{id}/toggle-status', [UserApprovalController::class, 'toggleStatus'])->name('seller.toggleStatus');
    Route::post('/user-requests/{id}/restore', [UserApprovalController::class, 'restore'])->name('seller.restore');
    Route::post('/send-staff-credentials', [UserApprovalController::class, 'sendCredentialsEmail'])->name('staff.sendEmail');
    Route::get('/notifications/{id}/read', [UserApprovalController::class, 'readNotification'])->name('notifications.read');
    Route::get('/notifications/mark-all', [UserApprovalController::class, 'markAllRead'])->name('notifications.markAllRead');

    // --- Staff Management ---
    Route::get('/staff-management', [StaffManagementController::class, 'index'])->name('staff.index');
    Route::put('/staff-management/{id}/toggle', [StaffManagementController::class, 'toggleStatus'])->name('staff.toggle');

    // --- Order Management ---
    Route::get('review-orders', [OrderController::class, 'reviewOrders'])->name('orders.review');
    Route::get('review-orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('assign-order/{id}', [OrderController::class, 'assignOrder'])->name('orders.assign');
    Route::put('orders/finalize-refund/{id}', [OrderController::class, 'finalizeRefund'])->name('orders.finalize_refund');

    // --- Product Management ---
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [AdminProductController::class, 'show'])->name('products.show');
    Route::post('/products/{id}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::post('/products/{id}/reject', [AdminProductController::class, 'reject'])->name('products.reject');

    //Review
    Route::get('/reviews', [App\Http\Controllers\Admin\AdminReviewController::class, 'index'])->name('reviews');

    // --- Profile & Security ---
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/check-password', [AdminProfileController::class, 'checkPassword'])->name('check-password');

    // --- Chat System ---
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [AdminChatController::class, 'index'])->name('index');
        Route::post('/clear/{receiverId}/{type}', [AdminChatController::class, 'clearConversation'])->name('clear');
        Route::get('/fetch/{receiverId}/{type}', [AdminChatController::class, 'fetchMessages'])->name('fetch');
        Route::get('/profile/{type}/{id}', [AdminChatController::class, 'getProfile'])->name('profile');
        Route::post('/mark-read/{id}/{type}', [AdminChatController::class, 'markAsRead'])->name('mark-read');
        Route::post('/send', [AdminChatController::class, 'sendMessage'])->name('send');
        Route::post('/delete', [AdminChatController::class, 'deleteMessage'])->name('delete');
        Route::get('/orders', [AdminChatController::class, 'getRecentOrders'])->name('orders');
        // Change 'clearConversation' to 'clearChat'
Route::post('/clear/{receiverId}/{type}', [AdminChatController::class, 'clearChat'])->name('clear');
    });

    // --- Reports & Analytics ---
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/inventory', [ReportController::class, 'inventoryReport'])->name('inventory');
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
       
        Route::get('/sales/pdf', [ReportController::class, 'downloadPDF'])->name('sales.pdf');
    });

    // --- Seller Analytics ---
    Route::prefix('seller-analytics')->name('seller.analytics.')->group(function () {
        Route::get('/', [SellerAnalyticsController::class, 'index'])->name('index');
        Route::get('/view/{id}', [SellerAnalyticsController::class, 'viewReport'])->name('show');
    });
});
     


// =============================================================
//                    SELLER DASHBOARD (Protected)
// =============================================================

Route::prefix('seller')->name('seller.')->middleware([SellerMiddleware::class])->group(function () {

    Route::get('/dashboard', [SellerDashController::class, 'dashboard'])->name('dashboard');
    // Inside the 'seller.' group
Route::get('/dashboard/chart-data', [SellerDashController::class, 'getChartData'])->name('dashboard.chart-data');


    // Logout
    Route::post('/logout', function () {
        Auth::guard('seller')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/seller/login');
    })->name('logout');

    // --- CHAT SYSTEM (Synchronized with Delivery Logic) ---
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [SellerChatController::class, 'index'])->name('index');
        Route::get('/fetch/{receiverId}/{type}', [SellerChatController::class, 'fetchMessages'])->name('fetch');
        Route::post('/send', [SellerChatController::class, 'sendMessage'])->name('send');
        Route::post('/delete', [SellerChatController::class, 'deleteMessage'])->name('delete');
        Route::get('/orders', [SellerChatController::class, 'getRecentOrders'])->name('orders');
        
        // This fixes the "Select Contact" header
        Route::get('/profile/{type}/{id}', [SellerChatController::class, 'getProfile'])->name('profile');
        
        // This clears the red unread badges
        Route::post('/mark-read/{id}/{type}', [SellerChatController::class, 'markAsRead'])->name('mark-read');
        
        Route::post('/clear/{receiverId}/{type}', [SellerChatController::class, 'clearChat'])->name('clear');
    }); 

    Route::get('notifications/{id}/read', [SellerDashController::class, 'readNotification'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [SellerDashController::class, 'markAllRead'])->name('notifications.markAllRead');

    // Inquiries
    Route::get('/inquiries', [SellerDashController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{id}/reply', [SellerDashController::class, 'markReplied'])->name('inquiries.reply');
    Route::post('/inquiries/{id}/send-reply', [SellerDashController::class, 'replyToInquiry'])->name('inquiries.sendReply');

    // Orders
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::put('/orders/{id}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pdf', [SellerOrderController::class, 'downloadPdf'])->name('orders.pdf');

    // Inside the 'seller.' route name group
   Route::put('/orders/approve-cancel/{id}', [SellerOrderController::class, 'approveCancellation'])->name('orders.approve_cancel');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/image/{id}/delete', [ProductController::class, 'deleteImage'])->name('products.image.delete');

    // Profile
    Route::get('/profile', [SellerProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [SellerProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [SellerProfileController::class, 'updatePassword'])->name('profile.password');

    //Review
    Route::get('/reviews', [App\Http\Controllers\Seller\SellerDashController::class, 'reviews'])->name('reviews');

    //report
    Route::get('/reports', [SellerReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventory', [SellerReportController::class, 'inventoryResults'])->name('reports.inventory');
    Route::get('/reports/inventory/pdf', [SellerReportController::class, 'downloadPDF'])->name('reports.pdf');
    
    // This MUST match the name in your Blade form: route('seller.reports.submit')
    Route::post('/reports/submit', [SellerReportController::class, 'submitToAdmin'])->name('reports.submit');


    // AJAX Password Check
    Route::post('/check-password', function (Request $request) {
        $seller = Auth::guard('seller')->user() ?? \App\Models\Staff::find(session('staff_id'));
        $isValid = $seller && Hash::check($request->password, $seller->password ?? '');
        return response()->json(['valid' => $isValid]);
    })->name('check-password');

});



//DELIVERY

Route::prefix('delivery')->name('delivery.')->middleware([DeliveryPersonMiddleware::class])->group(function () {
    
    // Dashboard, Orders, and History
    Route::get('/dashboard', [DeliveryDashController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [DeliveryOrderController::class, 'myDeliveries'])->name('my-deliveries');
    Route::get('/task-history', [DeliveryOrderController::class, 'taskHistory'])->name('task-history');
    Route::get('/orders/{id}', [DeliveryOrderController::class, 'show'])->name('orders.show');
    Route::get('/performance-report', [DeliveryOrderController::class, 'downloadReport'])->name('report.download');
    
    Route::put('/update-milestone/{id}', [DeliveryOrderController::class, 'updateMilestone'])
    ->name('orders.update-milestone');
    // Update Actions
    Route::put('/mark-delivered/{id}', [DeliveryOrderController::class, 'markAsDelivered'])->name('mark-delivered');
    Route::put('/mark-failed/{id}', [DeliveryOrderController::class, 'markAsFailed'])->name('mark-failed');
    Route::post('/notifications/mark-all-read', [DeliveryOrderController::class, 'markAllRead'])->name('notifications.markAllRead');

    
    Route::get('/profile', [DeliveryProfileController::class, 'index'])->name('profile');
    
    Route::put('/profile/update', [DeliveryProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/check-password', [DeliveryProfileController::class, 'checkPassword'])->name('check-password');

    // Chat Routes
   Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/fetch/{receiverId}/{type}', [ChatController::class, 'fetchMessages'])->name('fetch');
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::post('/delete', [ChatController::class, 'deleteMessage'])->name('delete');
    Route::get('/orders', [ChatController::class, 'getRecentOrders'])->name('orders');
    
    // Add these two to fix the 500 errors
    Route::get('/profile/{type}/{id}', [ChatController::class, 'getProfile'])->name('profile');
    Route::post('/mark-read/{id}/{type}', [ChatController::class, 'markAsRead'])->name('mark-read');
        
        // Renamed chat profile to avoid conflict with Rider Profile
        Route::get('/staff-profile/{type}/{id}', [ChatController::class, 'getStaffProfile'])->name('staff_profile');
        Route::post('/clear/{receiverId}/{type}', [ChatController::class, 'clearChat'])->name('clear');
        Route::post('/mark-read/{id}/{type}', [ChatController::class, 'markAsRead'])->name('mark-read');

        
    });
});

// Route to export product data to JSON for Python
Route::get('/export-products', function() {
    $products = \App\Models\Product::whereIn('status', ['approved', 'reapproved'])
        ->where('is_active', 1)
        ->get()
        ->map(function($p) {
            // Get the category name
            $cat = $p->category->name ?? '';
            
            // REPEAT the category 5 times to force the AI to respect it
            // "Tea Tea Tea Tea Tea" is much stronger than "antioxidants"
            $boostedCategory = str_repeat($cat . " ", 5); 

            return [
                'id' => $p->id,
                // Name + Description + BOOSTED Category
                'text' => $p->name . " " . $p->description . " " . $boostedCategory,
            ];
        });

    \Illuminate\Support\Facades\File::put(base_path('ai_service/products.json'), $products->toJson());

    return "Products exported with Category Boost! Count: " . $products->count();
});

 


