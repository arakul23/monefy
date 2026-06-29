<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('/invoices')->controller(InvoiceController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{invoice}', 'show')->whereNumber('invoice');
    Route::post('/', 'store');
    Route::put('/{invoice}', 'update')->whereNumber('invoice');
});

