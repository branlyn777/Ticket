<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|


Route::middleware('auth:api')->get('/ticket', function (Request $request) {
    return $request->user();
});*/

Route::group(['middleware' => ['api'],'namespace'=>'Api'], function()
{
    Route::name('apis.')->group(function () {
        Route::resource('tickets', 'TicketController')->except(['update']);
        Route::post('tickets/{ticket}/update', 'TicketController@update');
        Route::resource('ticket-messages', 'TicketMessageController');
        Route::resource('ticket-attachments', 'TicketAttachmentController');
        Route::resource('ticket/ticket-histories', 'TicketHistoryController');
        Route::resource('ticket-message-files', 'TicketMessageFileController');
        Route::post('/tickets/update-ticket/{ticket}', 'TicketController@updateTicket')->name('update.ticket');
    });
});