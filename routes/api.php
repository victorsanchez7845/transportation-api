<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Authorization\OauthController;
use App\Http\Controllers\Api\Quotation\AutocompleteController;
use App\Http\Controllers\Api\Quotation\SearchController;
use App\Http\Controllers\Api\Quotation\PhoneController;
use App\Http\Controllers\Api\Quotation\CreationController;
use App\Http\Controllers\Api\Flights\SearchController as FlightSearch;
use App\Http\Controllers\Api\Mailing\ReservationController as MailingReservation;
use App\Http\Controllers\Api\Reservation\SearchController as SearchReservation;
use App\Http\Controllers\Api\Payments\HandlerController;
use App\Http\Controllers\Api\Webhook\VerifyController;
use App\Http\Controllers\Api\Contact\ContactController;
use App\Http\Controllers\Api\Terms\TermsController;
use App\Http\Controllers\Api\Hotels\HotelsController;
use App\Http\Controllers\Api\Hotels\RatesController;
use App\Http\Controllers\Api\Spam\SpamController;
use App\Http\Middleware\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware([Auth::class])->group(function () {

    Route::prefix('v1')->group(function () {

        //SPAM PROCESS
        Route::get('/spamChangeStatus', [SpamController::class,'spamChangeStatus'])->withoutMiddleware([Auth::class]);
        Route::get('/spamCallCount', [SpamController::class,'spamCallCount'])->withoutMiddleware([Auth::class]);
        
        //Ruta de autenticación
        Route::post('/oauth', [OauthController::class,'index'])->withoutMiddleware([Auth::class]);

        //Rutas de cotización
        Route::post('/autocomplete-affiliates', [AutocompleteController::class,'affiliates'])->withoutMiddleware([Auth::class]);
        Route::post('/hotels/add', [HotelsController::class,'index'])->withoutMiddleware([Auth::class]);
        Route::get('/hotels', [HotelsController::class,'getHotels'])->withoutMiddleware([Auth::class]);
        Route::get('/rates', [RatesController::class,'getRates'])->withoutMiddleware([Auth::class]);

        Route::post('/autocomplete', [AutocompleteController::class,'index']);
        Route::post('/quote', [SearchController::class,'index']);
        Route::get('/phone', [PhoneController::class,'index']);
        Route::post('/create', [CreationController::class,'index']);

        //Nota: En producción habilitar el pago, para habilitar el HTTPS y la busqueda por fecha...
        Route::post('/flights/search', [FlightSearch::class,'index']);
        Route::post('/flights/searchByDate', [FlightSearch::class,'searchDate']);

        //Mailing
        Route::get('/mailing/reservation/view', [MailingReservation::class,'view'])->withoutMiddleware([Auth::class]);

        //Reservation
        Route::post('/reservation/get', [SearchReservation::class,'index'])->withoutMiddleware([Auth::class]);
        Route::get('/reservation/send', [SearchReservation::class,'send'])->withoutMiddleware([Auth::class]);
        Route::get('/reservation/qr', [SearchReservation::class,'makeqr'])->withoutMiddleware([Auth::class]);

        //types cancellations
        Route::get('/types/cancellations/get', [SearchReservation::class,'getTypesCancellations'])->withoutMiddleware([Auth::class]);

        //Payments
        Route::get('/reservation/payment/handler', [HandlerController::class,'index'])->withoutMiddleware([Auth::class]);

        //Payments IPN
        Route::post('/ipn/stripe', [VerifyController::class,'stripe'])->withoutMiddleware([Auth::class]);
        Route::post('/ipn/paypal', [VerifyController::class,'paypal'])->withoutMiddleware([Auth::class]);

        //Contact form
        Route::post('/contact', [ContactController::class,'index'])->withoutMiddleware([Auth::class]);

        Route::get('/terms-and-conditions', [TermsController::class,'terms'])->withoutMiddleware([Auth::class]);
        Route::get('/privacy-policy', [TermsController::class,'privacy'])->withoutMiddleware([Auth::class]);
        
    });
    
});