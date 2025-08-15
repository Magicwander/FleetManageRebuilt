<?php

use Illuminate\Support\Facades\Route;

// Redirect all routes to raw PHP login system
Route::get('/', function () {
    return redirect('/raw-login.php');
});

Route::get('/login', function () {
    return redirect('/raw-login.php');
});

Route::get('/dashboard', function () {
    return redirect('/raw-dashboard.php');
});

Route::get('/customer-dashboard', function () {
    return redirect('/raw-customer.php');
});

Route::get('/logout', function () {
    return redirect('/raw-login.php?logout=1');
});
