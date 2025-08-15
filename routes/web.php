<?php

use Illuminate\Support\Facades\Route;

// Redirect all routes to the PHP-based system
Route::get('/', function () {
    return redirect('/login.php');
});

Route::get('/login', function () {
    return redirect('/login.php');
});

Route::get('/register', function () {
    return redirect('/register.php');
});

Route::get('/dashboard', function () {
    return redirect('/dashboard.php');
});

Route::get('/customer-dashboard', function () {
    return redirect('/customer.php');
});

Route::get('/admin-trips', function () {
    return redirect('/admin-trips.php');
});

Route::get('/admin-users', function () {
    return redirect('/admin-users.php');
});

Route::get('/admin-reports', function () {
    return redirect('/admin-reports.php');
});

Route::get('/logout', function () {
    return redirect('/login.php?logout=1');
});
