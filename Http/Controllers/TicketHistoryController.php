<?php

namespace Modules\Ticket\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketAttachment;
use App\Helpers\HostHelper;
use Modules\Ticket\Http\Traits\TicketTrait;
use Modules\Project\Entities\ProjectTask;
use App\User;

class TicketHistoryController extends Controller
{
    use TicketTrait;

    public function index(Request $request)
    {
        $checkScrivania = HostHelper::isScrivania();
        if ($checkScrivania) {

            $ticketId = base64_decode($request->ticket);

            $getLogs = Activity::where('log_name', 'ticket')
                            ->where('subject_id', $ticketId)
                            ->orderBy('activity_log.id', 'desc')
                            ->leftJoin('tickets', 'activity_log.subject_id', '=', 'tickets.id')
                            ->leftJoin('ticket_messages', 'activity_log.properties->ticket_message_id', '=', 'ticket_messages.id')
                            ->select(
                                'activity_log.*', 
                                'ticket_messages.id as ticket_message_id', 
                                'ticket_messages.message', 
                                'ticket_messages.author_email', 
                                'ticket_messages.author_full_name', 
                                'ticket_messages.is_scrivania',
                                'tickets.type_operation as type_operation',
                                'tickets.operation_note as operation_note'
                            )
                            ->get();
            foreach ($getLogs as $key => $getLog) {
                $ticketAttachments = TicketAttachment::where('id', @$getLog->properties['ticket_attachment_id'])->get()->map(function ($attachment) {
                    $attachment->file_path = $attachment->attachment;
                    unset($attachment->attachment);
                    return $attachment;
                });

                $user = User::where('id', $getLog->causer_id)->first();
                $getLog['userAvatar'] = $user ? $user->getAvatar() : asset('/assets/images/default_avatar.png');
                $getLog['userName'] = $user ? $user->getFullName() : $getLog->properties['author'];
                $getLog['projectTask'] = ProjectTask::where('id', $getLog->properties['projectTaskId'])->with('project')->first();
            }
            
            $ticket = Ticket::find($ticketId);
            if (isset($ticket->projectTasks) && count($ticket->projectTasks) > 0) {
                $projectTask = $ticket->projectTasks[0];
            } else {
                $projectTask = null;
            }

            $historyUser = [
                'created_by' => $ticket->author_full_name,
            ];

            $userIds = [
                'operator_id' => $projectTask ? $projectTask->operator_id : '-',
                'tester_id' => $projectTask ? $projectTask->tester_id : '-',
                'publisher_id' => $projectTask ? $projectTask->publisher_id : '-',
            ];
            foreach ($userIds as $key => $userId) {
                $user = \App\User::find($userId);
                $historyUser[$key] = $user ? $user->getFullName() : '';
            }
        }else{
            $data = $request->all();
            $ticketHistoryResponse = $this->getTicketHistories($data);
            $getLogs = !empty($ticketHistoryResponse['getLogs'])?$ticketHistoryResponse['getLogs']:[];
            $ticketId = !empty($ticketHistoryResponse['ticketId'])?$ticketHistoryResponse['ticketId']:[];
            $historyUser = !empty($ticketHistoryResponse['historyUser'])?$ticketHistoryResponse['historyUser']:[];
            $ticket = !empty($ticketHistoryResponse['ticket'])?$ticketHistoryResponse['ticket']:[];
        }

        return view('ticket::partials.ticket-histories',[
            'getLogs' => $getLogs,
            'ticketId' => $ticketId,
            'historyUser' => $historyUser,
            'ticket' => $ticket
        ]);
    }

    public function destroy($id)
    {
        $checkScrivania = HostHelper::isScrivania();
        if ($checkScrivania) {
            $activity = Activity::find($id);
            if (isset($activity['properties']['ticket_message_id'])) {
                $ticketMessageId = $activity['properties']['ticket_message_id'];
                $ticketMessage->deleteFiles();
                $ticketMessage->delete();
            }
            $activity->delete();
        }else{
            $this->destroyTicketHistory($id);
        }

        return redirect()->back()->with('success', __('main.record_deleted'));
    }
}
