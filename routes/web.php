<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FormController;
use App\Http\Controllers\WaitlistController;

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

Route::get('/waitlist/update/{contactId}', [\App\Http\Controllers\WaitlistController::class, 'edit'])->name('waitlist.update');
Route::post('/waitlist/update/{opportunityId}', [\App\Http\Controllers\WaitlistController::class, 'update'])->name('waitlist.update');
Route::post('/waitlist/opt-out/{contactId}', [\App\Http\Controllers\WaitlistController::class, 'optOut']);

Route::get('/update-waitlist', [WaitlistController::class, 'edit'])->name('waitlist.edit');

// Route::get('/get-custom-fields', [FormController::class, 'getCustomFields']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
