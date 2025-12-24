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

// USER CONTROLLERS
use App\Http\Controllers\User\UserDashController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Frontend\UserReviewController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\AdminDashController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\SellerApprovalController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\BannerController; 
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\OrderController;

// SELLER CONTROLLERS
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\Seller\SellerDashController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\ChatController as SellerChatController;

// DELIVERY CONTROLLERS
use App\Http\Controllers\Delivery\DeliveryDashController;
use App\Http\Controllers\Delivery\DeliveryOrderController;
use App\Http\Controllers\Delivery\ChatController;

// MIDDLEWARES
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\DeliveryPersonMiddleware;
use App\Http\Middleware\CustomerMiddleware;

//imgage based search
use App\Http\Controllers\ImageSearchController;

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
// Example fix in web.php

// Route to store the product review
// Now it points to the correct ProductController where you added the code
Route::post('/product/review/store', [App\Http\Controllers\Frontend\ProductController::class, 'storeReview'])->name('review.store')->middleware('auth');



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

// SELLER REGISTRATION (Public)
Route::get('/seller/register', [SellerRegistrationController::class, 'showForm'])->name('seller.register');
Route::post('/seller/register', [SellerRegistrationController::class, 'submitForm'])->name('seller.register.submit');


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
    
    // 3. Save the review to database
    Route::post('/reviews/store', [UserReviewController::class, 'store'])->name('reviews.store');
    // Orders
    Route::get('/orders', [ProfileController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{id}', [ProfileController::class, 'viewOrder'])->name('orders.show');
});

// =============================================================
//                    ADMIN DASHBOARD (Protected)
// =============================================================

Route::prefix('admin')->name('admin.')->middleware([AdminMiddleware::class])->group(function () {

    Route::get('/dashboard', [AdminDashController::class, 'dashboard'])->name('dashboard');

    // Resources
    Route::resource('categories', CategoryController::class);

    // Banner Management
    Route::get('/website-content/banner', [BannerController::class, 'edit'])->name('banner.edit');
    Route::put('/website-content/banner', [BannerController::class, 'update'])->name('banner.update');

    // Seller Approvals
    Route::get('/seller-requests', [SellerApprovalController::class, 'index'])->name('seller.requests');
    Route::post('/seller-requests/{id}/approve', [SellerApprovalController::class, 'approve'])->name('seller.approve');
    Route::post('/seller-requests/{id}/reject', [SellerApprovalController::class, 'reject'])->name('seller.reject');
    Route::put('/seller-requests/{id}/toggle-status', [SellerApprovalController::class, 'toggleStatus'])->name('seller.toggleStatus');
    Route::post('/seller-requests/{id}/restore', [SellerApprovalController::class, 'restore'])->name('seller.restore');

    // Product Management
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [AdminProductController::class, 'show'])->name('products.show');
    Route::post('/products/{id}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::post('/products/{id}/reject', [AdminProductController::class, 'reject'])->name('products.reject');

    // Profile & Security
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
   
    // Inside Admin Group chat
Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
Route::get('/chat/fetch/{sellerId}', [AdminChatController::class, 'fetchMessages'])->name('chat.fetch');
Route::post('/chat/send', [AdminChatController::class, 'sendMessage'])->name('chat.send');

 // Your new Review and Assignment routes
Route::get('review-orders', [App\Http\Controllers\Admin\OrderController::class, 'reviewOrders'])
    ->name('orders.review'); // Removed "admin." because the group adds it automatically
    
Route::put('assign-order/{id}', [App\Http\Controllers\Admin\OrderController::class, 'assignOrder'])
    ->name('orders.assign'); // Removed "admin."

    Route::get('review-orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');

    // Notifications
    Route::get('/notifications/{id}/read', function ($id) {
        $notification = auth()->guard('admin')->user()->notifications->find($id);
        if ($notification) {
            $notification->markAsRead();
            return redirect($notification->data['link']);
        }
        return back();
    })->name('notifications.read');

    Route::get('/notifications/mark-all', function () {
        auth()->guard('admin')->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    })->name('notifications.markAllRead');
}); 


// MOVE THIS TO THE TOP (Before the seller group)
Route::get('/set-currency/{currency}', function ($currency) {
    if (in_array($currency, ['LKR', 'USD'])) {
        session(['currency' => $currency]);
    }
    return redirect()->back();
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

    // Inquiries
    Route::get('/inquiries', [SellerDashController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{id}/reply', [SellerDashController::class, 'markReplied'])->name('inquiries.reply');
    Route::post('/inquiries/{id}/send-reply', [SellerDashController::class, 'replyToInquiry'])->name('inquiries.sendReply');

    // Orders
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::put('/orders/{id}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pdf', [SellerOrderController::class, 'downloadPdf'])->name('orders.pdf');

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

    Route::get('/chat', [SellerChatController::class, 'index'])->name('chat.index');

    // 2. Fetch Messages (AJAX)
    Route::get('/chat/fetch/{id}/{type}', [SellerChatController::class, 'fetchMessages'])->name('chat.fetch');

    // 3. Send Message / Upload Media
    Route::post('/chat/send', [SellerChatController::class, 'sendMessage'])->name('chat.send');

    // 4. Delete Single Message
    Route::post('/chat/delete', [SellerChatController::class, 'deleteMessage'])->name('chat.delete');

    // 5. Fetch Profile for Modal (Fixes "Undefined" error)
    Route::get('/chat/profile/{type}/{id}', [SellerChatController::class, 'getStaffProfile'])->name('chat.profile');

    // 6. Clear Entire Conversation
    Route::post('/chat/clear/{id}/{type}', [SellerChatController::class, 'clearConversation'])->name('chat.clear');

    // 7. Fetch Recent Orders (Mapped to fname, lname, address1)
    Route::get('/chat/orders', [SellerChatController::class, 'getRecentOrders'])->name('chat.orders');


    // ✅ NOTIFICATIONS (FIXED)
    Route::post(
        'notifications/mark-all-read',
        [SellerDashController::class, 'markAllRead']
    )->name('notifications.markAllRead');

    Route::get(
        'notifications/{id}/read',
        [SellerDashController::class, 'readNotification']
    )->name('notifications.read');


    // AJAX Password Check
    Route::post('/check-password', function (Request $request) {
        $seller = Auth::guard('seller')->user() ?? \App\Models\Staff::find(session('staff_id'));
        $isValid = $seller && Hash::check($request->password, $seller->password ?? '');
        return response()->json(['valid' => $isValid]);
    })->name('check-password');

});

// =============================================================
//                   DELIVERY DASHBOARD (Protected)
// =============================================================


    Route::prefix('delivery')->name('delivery.')->middleware([DeliveryPersonMiddleware::class])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DeliveryDashController::class, 'dashboard'])->name('dashboard');

    // Active Orders List (Status 4)
    Route::get('/orders', [DeliveryOrderController::class, 'myDeliveries'])->name('my-deliveries');
    
    // ✅ ADDED: Task History (Status 5 & 6)
    // This allows completed/failed orders to be separated from active tasks
    Route::get('/task-history', [DeliveryOrderController::class, 'taskHistory'])->name('task-history');
    
    // View Item Details
    Route::get('/orders/{id}', [DeliveryOrderController::class, 'show'])->name('orders.show');
    
    // Update Actions
    Route::put('/mark-delivered/{id}', [DeliveryOrderController::class, 'markAsDelivered'])->name('mark-delivered');
    Route::put('/mark-failed/{id}', [DeliveryOrderController::class, 'markAsFailed'])->name('mark-failed');
    
    // Chat Routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/fetch/{receiverId}/{type}', [ChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/delete', [ChatController::class, 'deleteMessage'])->name('chat.delete');

    
});

