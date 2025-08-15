<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Authentication Routes (No CSRF)
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/', function () {
    return redirect('/login');
});

// Dashboard routes (protected)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return '<h1>Admin Dashboard</h1><p>Welcome ' . auth()->user()->name . '</p><a href="/logout">Logout</a>';
    });
    
    Route::get('/customer-dashboard', function () {
        return '<h1>Customer Dashboard</h1><p>Welcome ' . auth()->user()->name . '</p><a href="/logout">Logout</a>';
    });
});
