<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print/gudang/{code}', [PrintController::class, 'identitasBlok']);
