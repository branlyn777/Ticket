<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['web','auth','module.ticket','status'], 'prefix' => 'module'], function()
{
    //IN USO
    Route::resource('tickets', 'TicketController');
    Route::match(['get', 'post'], 'tickets/apply/filters', 'TicketController@index')->name('ticketFilters');
    Route::resource('ticket-tokens', 'TicketTokenController');
    Route::resource('ticket-types', 'TicketTypeController');
    Route::post('ticket/text-image-uploads', 'TicketController@descriptionImageUpload')->name('ticket-text-image-upload');
    // created at when click on bell icon
    Route::post('/tickets/update-ticket/{ticket}', 'TicketController@updateTicket')->name('update.ticket');

    //END


    Route::resource('ticket-messages', 'TicketMessageController');
    Route::post('tickets/messages', 'TicketController@ticketMessages')->name('ticketMessages');
    Route::resource('ticket-message-files', 'TicketMessageFileController');
    //routes for ticket settings
    Route::resource('ticket-settings', 'SettingsTypeController');
    Route::put('tickets/convert-to-task/{ticket_id}', 'TicketController@convertToTask')->name('tickets.convert-to-task');
    // ticket history
    // Route::get('ticket/ticket-histories', 'TicketController@ticketHistory')->name('ticket.ticket-histories');
    Route::resource('ticket/ticket-histories', 'TicketHistoryController');
    // ticket attachment
    Route::resource('ticket-attachments', 'TicketAttachmentController');
    Route::get('ticket-attachment/download/{id}', 'TicketAttachmentController@downloadAttachment')->name('ticketAttachmentDownload');
});

Route::get('module/ticket-message/file/download/{id}/{filename}', 'TicketMessageController@downloadFile')->name('ticketMessageDownloadFile');