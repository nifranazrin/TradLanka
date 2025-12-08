<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// FRONTEND
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;

// AUTH
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\StaffLoginController;

// USER
use App\Http\Controllers\User\UserDashController;

// ADMIN
use App\Http\Controllers\Admin\AdminDashController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\SellerApprovalController;
use App\Http\Controllers\Admin\AdminProductController;

// SELLER
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\Seller\SellerDashController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Seller\ProductController;

// DELIVERY
use App\Http\Controllers\Delivery\DeliveryDashController;

// MIDDLEWARES
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\DeliveryPersonMiddleware;
use App\Http\Middleware\CustomerMiddleware;



// CUSTOMER CART
use App\Http\Controllers\CartController;

 //FRONTEND ROUTES

Route::get('/', [FrontendController::class, 'home'])->name('home');

//FRONTEND SHOW
Route::get('/product/{product:slug}', [FrontendProductController::class, 'show'])
     ->name('product.show');
     
     // CATEGORY PAGE
Route::get('/category/{slug}', [FrontendController::class, 'productsByCategory'])->name('categories.show');


// Search Results Page
Route::get('/search', [FrontendController::class, 'searchPage'])->name('search.page');

// CUSTOMER AUTH

Route::get('/login', [CustomerLoginController::class, 'showLoginForm'])->name('customer.login');
Route::post('/login', [CustomerLoginController::class, 'login'])->name('customer.login.submit');
Route::post('/logout', [CustomerLoginController::class, 'logout'])->name('customer.logout');

Route::prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashController::class, 'dashboard'])->name('dashboard');
});

 //STAFF LOGIN (Admin / Seller / Delivery)

Route::get('/staff/login', [StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/staff/login', [StaffLoginController::class, 'login'])->name('staff.login.submit');
Route::post('/staff/logout', [StaffLoginController::class, 'logout'])->name('staff.logout');

//ADMIN ROUTES (protected)
Route::prefix('admin')->name('admin.')->middleware([AdminMiddleware::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashController::class, 'dashboard'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Seller Requests & Management
    Route::get('/seller-requests', [SellerApprovalController::class, 'index'])->name('seller.requests');
    Route::post('/seller-requests/{id}/approve', [SellerApprovalController::class, 'approve'])->name('seller.approve');
    Route::post('/seller-requests/{id}/reject', [SellerApprovalController::class, 'reject'])->name('seller.reject');
    Route::put('/seller-requests/{id}/toggle-status', [SellerApprovalController::class, 'toggleStatus'])->name('seller.toggleStatus');
//  'seller-requests' routes
Route::post('/seller-requests/{id}/restore', [SellerApprovalController::class, 'restore'])->name('seller.restore');
    // Admin Products
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [AdminProductController::class, 'show'])->name('products.show');
    Route::post('/products/{id}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::post('/products/{id}/reject', [AdminProductController::class, 'reject'])->name('products.reject');

    // Admin Profile
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // Password Check (AJAX)
    Route::post('/check-password', function (Illuminate\Http\Request $request) {
        $admin = Auth::guard('admin')->user() ?? \App\Models\Staff::find(session('staff_id'));
        $isValid = Hash::check($request->password, $admin->password ?? '');
        return response()->json(['valid' => $isValid]);
    })->name('check-password');

    // --- NEW NOTIFICATION ROUTES (Now Correctly Inside Middleware) ---
    
    // Mark single notification as read and redirect
    Route::get('/notifications/{id}/read', function ($id) {
        $notification = auth()->guard('admin')->user()->notifications->find($id);
        if ($notification) {
            $notification->markAsRead();
            return redirect($notification->data['link']);
        }
        return back();
    })->name('notifications.read');

    // Mark ALL as read
    Route::get('/notifications/mark-all', function () {
        auth()->guard('admin')->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    })->name('notifications.markAllRead');

}); 
 //SELLER ROUTES (protected)

Route::prefix('seller')->name('seller.')->middleware([SellerMiddleware::class])->group(function () {

    Route::get('/dashboard', [SellerDashController::class, 'dashboard'])->name('dashboard');

    // Products (keep your explicit routes so existing forms keep working)
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

    // AJAX Password Check
    Route::post('/check-password', function (Illuminate\Http\Request $request) {
        $seller = Auth::guard('seller')->user() ?? \App\Models\Staff::find(session('staff_id'));
        $isValid = $seller && Hash::check($request->password, $seller->password ?? '');
        return response()->json(['valid' => $isValid]);
    })->name('check-password');
});


 //DELIVERY ROUTES (protected)

Route::prefix('delivery')->name('delivery.')->middleware([DeliveryPersonMiddleware::class])->group(function () {
    Route::get('/dashboard', [DeliveryDashController::class, 'dashboard'])->name('dashboard');
});

//SELLER REGISTRATION (public)

Route::get('/seller/register', [SellerRegistrationController::class, 'showForm'])->name('seller.register');
Route::post('/seller/register', [SellerRegistrationController::class, 'submitForm'])->name('seller.register.submit');

//CART ROUTES

Route::get('/cart', [CartController::class, 'showCart'])->name('cart.show');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::get('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');

