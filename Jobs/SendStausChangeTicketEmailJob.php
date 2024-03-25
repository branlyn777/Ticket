<?php

namespace Modules\Ticket\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\MailHelper;
use App\User;
use Carbon\Carbon;

class SendStausChangeTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;
    public $oldStatus;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ticket,$oldStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ticket = $this->ticket;
        $oldStatus = $ticket->statusTranslation($this->oldStatus);
        $newStatus = $ticket->statusTranslation($ticket->status);

        $emailSubject = "Aggiornato stato ticket - ".$newStatus;
        $emailMessage = "<p>Il tuo ticket <strong>".$ticket->subject."</strong> con urgenza <strong>".$ticket->priorityTranslation($ticket->priority)."</strong> ha variato lo stato da <strong>".$oldStatus."</strong> a <strong>".$newStatus."</strong></p>";

        if ($ticket->status === 'closed') {
            $typeOperation = $ticket->typeOperationTranslation($ticket->type_operation);
            $typeOperationText = "<p>Tipo intervento: <p><strong>".$typeOperation."</strong></p></p>";
            if ($typeOperationText) {
                $emailMessage .= $typeOperationText;
            }

            $operationNoteText = "<p>Note intervento: ".$ticket->operation_note."</p>";
            if ($operationNoteText) {
                $emailMessage .= $operationNoteText;
            }
        }

        MailHelper::sendEmail($emailMessage, $emailSubject,null, null,$ticket->author_email);
    }
}
