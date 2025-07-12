<?php

use Illuminate\Support\Facades\Route;

// Serve the React application for all frontend routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Exclude API routes
