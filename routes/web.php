<?php

use App\Http\Controllers\Admin\LawyerAssistantController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSerialController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\CacheManagementController;
use App\Http\Controllers\ProductShowcaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TwoStepVerificationController;
use Illuminate\Support\Facades\Route;





Route::get('/', fn () => view('welcome'))->name('home');


Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create',])->name('login');
    Route::post('/login', [LoginController::class, 'store',])->middleware('throttle:login')->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm',])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink',])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'resetForm',])->name('password.reset');
    Route::post('/reset-password/verify-otp', [PasswordResetController::class,'verifyResetOtp'])->middleware('throttle:5,1')->name('password.verify-otp');
    Route::post('/reset-password', [PasswordResetController::class, 'reset',])->middleware('throttle:5,1')->name('password.update');
    Route::prefix('login/verify')->name('auth.2fa.')->controller(TwoStepVerificationController::class)->group(function () {
        Route::get('/', 'create')->name('create');
        Route::post('/', 'verifyLogin')->middleware('throttle:5,1')->name('store');
    });

});



Route::middleware(['auth', 'active.user', 'two-factor.complete', 'session.harden'])->group(function () {


    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route::prefix('dashboard')->name('dashboard.')->group(function () {
    //     Route::get('/super-admin', [SuperAdminDashboardController::class, 'index'])->middleware('role:super-admin')->name('super-admin');
    //     Route::get('/general-secretary', [GeneralSecretaryDashboardController::class, 'index'])->middleware('role:general-secretary')->name('general-secretary');
    //     Route::get('/office-staff', [OfficeStaffDashboardController::class, 'index'])->middleware('role:office-staff')->name('office-staff');
    //     Route::get('/member', [MemberDashboardController::class, 'index'])->middleware('role:member')->name('member');
    //     Route::get('/apprentice', [ApprenticeDashboardController::class, 'index'])->middleware('role:user-apprentice')->name('apprentice');
    // });


    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:users.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:users.create')->name('create');
        Route::post('/', 'store')->middleware('permission:users.create')->name('store');

        Route::get('/{user}/images/{type}', [UserController::class,'image',])->name('image');

        Route::get('/{user}', 'show')->whereNumber('user')->middleware('permission:users.view')->name('show');
        Route::get('/{user}/edit', 'edit')->whereNumber('user')->middleware('permission:users.edit')->name('edit');
        Route::put('/{user}', 'update')->whereNumber('user')->middleware('permission:users.edit')->name('update');

        Route::patch('/{user}/approval', 'updateApproval')->whereNumber('user')->middleware('permission:users.approve')->name('approve');
        Route::patch('/{user}/member-status', 'changeStatus')->whereNumber('user')->middleware('permission:users.change_status')->name('change-status');

        Route::delete('/{user}', 'destroy')->whereNumber('user')->middleware('permission:users.delete')->name('destroy');
        Route::patch('/{user}/role',[UserController::class, 'updateRole'])->name('roles.update');});

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('index');
        Route::get('/create', [RoleController::class, 'create'])->middleware('permission:roles.create')->name('create');
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->whereNumber('role')->middleware('permission:roles.edit')->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->whereNumber('role')->middleware('permission:roles.edit')->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->whereNumber('role')->middleware('permission:roles.delete')->name('destroy');
        Route::get('/{role}', [RoleController::class, 'show'])->whereNumber('role')->middleware('permission:roles.view')->name('show');
    });

    Route::prefix('assistants')->name('assistants.')->controller(LawyerAssistantController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{assistant}', 'show')->whereNumber('assistant')->name('show');
    });

    Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {

        Route::get('/', 'index')->middleware('permission:products.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:products.create')->name('create');
        Route::post('/', 'store')->middleware('permission:products.create')->name('store');
        Route::get('/{product}', 'show')->whereNumber('product')->middleware('permission:products.view')->name('show');
        Route::get('/{product}/edit', 'edit')->whereNumber('product')->middleware('permission:products.edit')->name('edit');
        Route::put('/{product}', 'update')->whereNumber('product')->middleware('permission:products.edit')->name('update');
        Route::delete('/{product}', 'destroy')->whereNumber('product')->middleware('permission:products.delete')->name('destroy');
        Route::post('/{product}/serials',[ProductSerialController::class, 'store'])->whereNumber('product')->middleware('permission:products.manage_serials')->name('serials.store');
    });

    Route::prefix('orders')->name('orders.')->controller(OrderController::class)->group(function () {

        Route::get('/', 'index')->middleware('permission:orders.view')->name('index');
        Route::get('/create', 'create')->middleware('permission:products.buy')->name('create');
        Route::post('/', 'store')->middleware('permission:products.buy')->name('store');
        Route::get('/{order}', 'show')->whereNumber('order')->middleware('permission:orders.view')->name('show');
        Route::patch('/{order}/status', 'updateStatus')->whereNumber('order')->middleware('permission:orders.status_update')->name('update-status');
        
        Route::get('/search', [OrderController::class, 'searchSuggestions'])->name('search'); 

    });

    Route::prefix('shop')->name('shop.')->controller(ProductShowcaseController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        // Route::get('/my-orders', 'orderHistory')->name('orders');
    });

    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('/password', 'editPassword')->name('password.edit');
        Route::put('/password', 'updatePassword')->name('password.update');
        // Route::get('/two-factor', 'showTwoFactor')->name('two-factor.show');
        // Route::post('/two-factor/enable', 'enableTwoFactor')->middleware('throttle:5,1')->name('two-factor.enable');
        // Route::post('/two-factor/verify', 'verifyTwoFactor')->middleware('throttle:5,1')->name('two-factor.verify');
        // Route::delete('/two-factor', 'disableTwoFactor')->middleware('throttle:5,1')->name('two-factor.disable');

        Route::prefix('two-step-verification')->name('two-factor.')->controller(TwoStepVerificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/authenticator/setup', 'setupAuthenticator')->middleware('throttle:5,1')->name('authenticator.setup');
            Route::post('/authenticator/confirm', 'confirmAuthenticator')->middleware('throttle:5,1')->name('authenticator.confirm');
            Route::post('/authenticator/disable', 'disableAuthenticator')->middleware('throttle:5,1')->name('authenticator.disable');
            Route::post('/sms/setup', 'setupSms')->name('sms.setup');
        });
    });

    Route::prefix('system/cache')->name('system.cache.')->group(function () {
        Route::get('/', [CacheManagementController::class,'index',])->name('index');
        Route::post('/clear', [CacheManagementController::class,'clear',])->name('clear');
    });
    

});