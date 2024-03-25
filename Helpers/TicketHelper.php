<?php

namespace Modules\Ticket\Helpers;
use App\User;
use Carbon\Carbon;

class TicketHelper{

    /**
     * Send email notification to admin or customer
     */
    public static function sendNotificationEmail($data,$ticket)
    {
        // Subject
        $subject = 'Ticket ( ID: #' . $ticket->id . ') '. '| ' .$ticket->subject .'| '.$ticket->project->name . ' | Stato: '. $ticket->getStatusName();

        //SEND EMAIL AUTHOR
        $messageText = view('ticket::emails.admin-ticket-notification',[ 'ticket' => $ticket ]);
        \App\Helpers\MailHelper::sendEmail($messageText, $subject, null,null, $data['author_email']);

        //SEND EMAILS ALL OPETATORS
        //if status is open
        $roles = $ticket->status == "open" ? ["operator", "admin"] : ["admin","superadmin"];

        $operators = User::Filters(["roles" => $roles])->get();
        foreach ($operators as $operator){
            $messageText = view('ticket::emails.admin-ticket-notification',[ 'ticket' => $ticket ]);
            \App\Helpers\MailHelper::sendEmail($messageText, $subject, $operator );
        }
    }

    public static function calculateUrgeDate($startDate, $addDays) {
        $holidays = [
            '11-23', '11-01', '06-02', '05-01', '04-25', '01-01', '01-02', '08-12', '08-13', '08-14', '08-15', '08-16', '08-17', '08-18', '08-19', '12-24', '12-25', '12-26', '12-31',
        ];

        $urgeDate = Carbon::parse($startDate)->startOfDay();

        while ($addDays > 0) {
            $urgeDate->addDay();

            if ($urgeDate->isWeekend()) {
                continue;
            }

            if (in_array($urgeDate->format('m-d'), $holidays)) {
                continue;
            }

            $addDays--;
        }

        return $urgeDate;
    }
}