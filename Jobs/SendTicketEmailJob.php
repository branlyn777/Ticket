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

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;
    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ticket,$data)
    {
        $this->ticket = $ticket;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ticket = $this->ticket;
        $data = $this->data;

        $sendToEmails = User::whereIn('id',$ticket->project->projectPermissions->pluck('user_id')->toArray())->pluck('email')->toArray();
        $roles = ['superadmin', 'admin'];
        $superadmins = \App\User::whereHas('roles', function ($query) use ($roles) {
                    $query->whereIn('name', $roles);
                })
                ->pluck('email')
                ->toArray();
        $toMail = array_unique(array_merge($superadmins, $sendToEmails));
        
        $createdAt = \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y');
        $userName = $data ? $data['urge_name'] : auth()->user()->getFullName();
        $ticketUrl = route('tickets.index',['ticket_id' => base64_encode($ticket->id)]);

        $emailSubject = "URGENTE - Sollecito per il ticket ".$ticket->subject." con priorità ".$ticket->priorityTranslation($ticket->priority).".";
        $emailMessage = "<p>ticket <strong>".$ticket->id."</strong></p><p><strong>".$userName."</strong> ha chiesto un sollecito del ticket #<strong>".$ticket->id."</strong> <strong>".$ticket->subject."</strong> per il progetto <strong>".$ticket->project->name."</strong> creato in data <strong>".$createdAt."</strong></p><p>Per vedere il ticket: <a href='".$ticketUrl."'>Clicca qui</a></p>";

        MailHelper::sendEmail($emailMessage, $emailSubject,null, null,$toMail);
    }
}
