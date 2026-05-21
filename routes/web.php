<?php

use Illuminate\Support\Facades\Route;
use R124LEfendi\InstagramPublisher\Http\Controllers\InstagramController;

Route::middleware(['web'])->prefix('instagram')->name('instagram.')->group(function () {
    Route::get('/', [InstagramController::class, 'index'])->name('dashboard');
    Route::post('/connect-user-token', [InstagramController::class, 'connectUserToken'])->name('connect.user-token');
    Route::post('/connect-single-profile', [InstagramController::class, 'connectSingleProfile'])->name('connect.single-profile');
    Route::post('/toggle-profile/{profile}', [InstagramController::class, 'toggleProfile'])->name('profile.toggle');
    Route::get('/redirect', [InstagramController::class, 'redirectToFacebook'])->name('redirect');
    Route::get('/callback', [InstagramController::class, 'handleCallback'])->name('callback');
    Route::delete('/account/{account}', [InstagramController::class, 'deleteAccount'])->name('account.delete');
    Route::post('/account/{account}/renew', [InstagramController::class, 'renewToken'])->name('account.renew');
    Route::post('/account/{account}/force-import-page', [InstagramController::class, 'forceImportPage'])->name('account.force-import');
    Route::post('/post', [InstagramController::class, 'post'])->name('post');
});
