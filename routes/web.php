<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Mail\Test as TestMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\TestJob;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get(
    "/",
    [ProductController::class, "index"],
);
Route::post(
    "/checkout",
    [ProductController::class, "checkout"],
)->name("checkout");
Route::get(
    "/success",
    [ProductController::class, "success"],
)->name("checkout.success");
Route::get(
    "/cancel",
    [ProductController::class, "cancel"],
)->name("checkout.cancel");
