<?php

use Illuminate\Support\Facades\Route;
use YourVendorName\Pageman\Http\Controllers\Auth\LoginController; // Adjust namespace

Route::name('pageman.auth.') // Route name prefix for all Pageman auth routes
    ->prefix(config('pageman.auth.route_prefix', 'pageman/auth')) // Configurable prefix
    // ->middleware('web') // 'web' middleware group is usually applied globally or by a parent route group
    ->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('postLogin'); // Or just 'login.post'
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    });

Route::name('pageman.admin.') // Route name prefix for all Pageman admin routes
    ->prefix(config('pageman.admin.route_prefix', 'pageman/admin')) // Configurable prefix
    ->middleware('web') // 'web' middleware group is usually applied globally or by a parent route group
    ->group(function () {
        Route::get('dashboard', function () {
            return view('pageman::admin.dashboard');
        })->name('dashboard');
    });
