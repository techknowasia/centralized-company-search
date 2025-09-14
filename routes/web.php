<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [SearchController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

Route::get('/company/{slug}', [CompanyController::class, 'show'])->name('company.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart/company/{companyId}/reports', [CartController::class, 'getCompanyReports'])->name('cart.company.reports');
Route::delete('/cart/remove/{cartItemId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update/{cartItemId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
