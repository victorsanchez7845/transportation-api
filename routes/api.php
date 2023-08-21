<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Authorization\OauthController;
use App\Http\Controllers\Api\Quotation\AutocompleteController;
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
        //Ruta de autenticación
        Route::post('/oauth', [OauthController::class,'index'])->withoutMiddleware([Auth::class]);

        //Rutas de cotización
        Route::post('/autocomplete', [AutocompleteController::class,'index']);
        //Route::post('/quote', [SearchController::class,'index']);
        //Route::post('/create', [CreationController::class,'index']);
        //Route::get('/phone', [PhoneController::class,'index']);

        //Nota: En producción habilitar el pago, para habilitar el HTTPS y la busqueda por fecha...
        //Route::post('/flights/search', [FlightSearch::class,'index']);
    });
    
});