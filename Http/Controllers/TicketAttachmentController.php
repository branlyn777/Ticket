<?php

namespace Modules\Ticket\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Ticket\Entities\TicketAttachment;

class TicketAttachmentController extends BaseController
{
	public function downloadAttachment($id)
    {
        $file = TicketAttachment::find($id);
        $pathToFile = storage_path('app/public/module-ticket/ticket-attachment/'.$file->attachment);
        if ( file_exists($pathToFile) ) {
            return response()->download($pathToFile);
        } else {
            return redirect()->back()->with('error', __('main.file_not_found'));
        }
    }

    public function destroy($id)
    {
        $destroyTicketAttachment = TicketAttachment::find($id);
        \App\Helpers\FileHelper::unlinkFile(storage_path('app/public/module-ticket/ticket-attachment/'),$destroyTicketAttachment->attachment);
        $destroyTicketAttachment->delete();
        
        return redirect()->back()->with('success', __('ticket::main.attachment_deleted'));
    }
}
?>