<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
    
});
use App\Http\Controllers\RsvpController;

Route::get('/rsvp/{token}', [RsvpController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('rsvp.show');
Route::post('/rsvp/{token}', [RsvpController::class, 'submit'])
    ->middleware('throttle:10,1')
    ->name('rsvp.submit');