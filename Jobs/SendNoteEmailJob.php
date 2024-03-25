<?php

namespace Modules\Ticket\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Project\Entities\ProjectTask;
use App\Helpers\MailHelper;
use App\User;
use Carbon\Carbon;

class SendNoteEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;
    public $user;
    public $data;
    public $public;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ticket,$user=null, $data=null, $public)
    {
        $this->ticket = $ticket;
        $this->user = $user;
        $this->data = $data;
        $this->public = $public;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ticket = $this->ticket;
        $user = $this->user;
        $data = $this->data;
        $public = $this->public;

        $superadmins = \App\User::whereHas('roles', function ($query) {
            $query->where('name', 'superadmin');
        })
        ->pluck('email')
        ->toArray();

        $projectTaskId = $ticket->project_task_id;
        $projectTask = ProjectTask::find($projectTaskId);
        $operator = \App\User::where('id', $projectTask->operator_id)->first();
        $operatorEmail = @$operator->email;

        $authorEmail = null;
        $ticketAuthorEmail = null;
        $checkScrivania = \App\Helpers\HostHelper::isScrivania();

        if ($checkScrivania && $public == 1) {
            $authorEmail = $data['author_email'];
            $ticketAuthorEmail = $ticket->author_email;
        } elseif (!$checkScrivania) {
            $authorEmail = $data['author_email'];
            $ticketAuthorEmail = $ticket->author_email;
        }
        
        $authEmail = $user ? $user->email : $authorEmail;

        $sendToEmails = array_merge($superadmins, [$ticketAuthorEmail, $operatorEmail]);
        $sendToEmails[] = $authEmail;
        $sendToEmails = array_filter($sendToEmails, function($email) use ($authEmail) {
            return !empty($email) && $email !== $authEmail;
        });
        $sendToEmails = array_unique($sendToEmails);
        \Log::info($sendToEmails);
        $emailSubject = "Nuova nota aggiunta al ticket: ".$ticket->subject;
        $authName = $user ?$user->getFullName() : $data['author_full_name'];
        $now = Carbon::now()->format('d/m/Y H:i');
        $emailMessage = "<p><strong>" . $authName . "</strong> ha aggiunto una nota</p><p><strong>" . $authName . " </strong>" . $now . "</p>";

        MailHelper::sendEmail($emailMessage, $emailSubject,null, null, $sendToEmails);
    }
}
