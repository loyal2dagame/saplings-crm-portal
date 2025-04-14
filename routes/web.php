<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FormController;

Route::get('/', function () {
    return Inertia::render('IndexPage'); // Ensure 'IndexPage' exists in your frontend
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::post('/inquire', [FormController::class, 'handleInquiry']);
Route::post('/waitlist', [FormController::class, 'handleWaitlist']);
Route::match(['get', 'post'], '/process-inquiry', [FormController::class, 'processInquiry'])->name('inquiry.process');
Route::post('/process-waitlist', [FormController::class, 'processWaitlist'])->name('process.waitlist');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
