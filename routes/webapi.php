<?php

/*
|--------------------------------------------------------------------------
| Web API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register webapi routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "web" and "webapi" middleware group. Enjoy building your API!
|  Perfect for SPAs like React using Sessions for authentication
| eg. trenchdevs.org/webapi/*
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


if (env('APP_ENV') !== 'production') {
    // can be used for testing on react app
    Auth::guard('web')->loginUsingId(2);
}

Route::middleware(['auth:web', 'webapi'])->group(function () {

    Route::get('blogs', 'Blogs\BlogsController@index');

    Route::get('me', function () {
        return response()->json(auth()->user());
    });



    // todo: chris - restrict to account id 3 / subdomain check
    Route::prefix('alumni')->group(function(){
        Route::get('events', 'Alumni\AlumniEventsController@getAllEvents');
        Route::get('events/{id}', 'Alumni\AlumniEventsController@getEvent');
        Route::post('events/upsert','Alumni\AlumniEventsController@upsert');
    });

});


