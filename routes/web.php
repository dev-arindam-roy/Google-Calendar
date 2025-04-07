<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Google\CalendarBookingEvent;
use App\Http\Controllers\Google\GoogleController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('google-calendar')->group(function () {
    Route::name('gcal.')->group(function () {
        Route::controller(CalendarBookingEvent::class)->group(function () {
            Route::get('/event-service', 'index')->name('event-service');
            Route::post('/create-event-service', 'createEventService')->name('create-event-service');

            Route::get('/quick-event', 'quickEvent')->name('quick-event');
            Route::post('/create-quick-event', 'createQuickEvent')->name('create-quick-event');
        });
    });
});

