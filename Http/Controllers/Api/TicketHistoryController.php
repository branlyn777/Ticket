<?php

namespace Modules\Ticket\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketToken;
use Modules\Ticket\Entities\TicketAttachment;
use Modules\Project\Entities\ProjectTask;
use App\User;

class TicketHistoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $token = $request->header('token');

            $validToken = TicketToken::where('token', $token)->first();

            if (!$validToken) {
                return response()->json(['error' => 'Wrong credentials.'], 401);
            }

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

            return response()->json([
                'getLogs' => collect($getLogs),
                'ticketId' => collect($ticketId),
                'historyUser' => collect($historyUser),
                'ticket' => collect($ticket),
                'message' => 'Ticket histories retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    public function destroy($id)
    {
        $activity = Activity::find($id);
        $activity->delete();

        return redirect()->back()->with('success', __('main.record_deleted'));
    }
}
