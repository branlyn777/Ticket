<?php

namespace Modules\Ticket\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use Modules\Project\Entities\ProjectTask;
use Modules\Project\Entities\ProjectTaskStatus;
use Modules\Ticket\Entities\TicketToken;
use Modules\Ticket\Entities\TicketType;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketAttachment;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\FileHelper;
use Modules\Ticket\Helpers\TicketHelper;
use Spatie\Activitylog\Models\Activity;
use Modules\Project\Jobs\SendProjectTaskWhatasappJob;
use Modules\Ticket\Jobs\SendTicketEmailJob;
use Modules\Ticket\Jobs\SendStausChangeTicketEmailJob;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
	
        try {
            $token = $request->header('token');

            $validToken = TicketToken::where('token', $token)->first();
	
            if (!$validToken) {
                return response()->json(['error' => 'Wrong credentials.'], 401);
            }

	    $data = $request->all();
	    
            $tickets = Ticket::with('ticketToken')
                                    ->with('operator')
                                    ->with(['ticketType' => function ($query) {
                                        $query->withTrashed();
                                    }])
                                    ->with('ticketAttachments.author')
                                    ->where('project_id',$validToken->project_id)
                                    ->orderBy('id','desc')
                                    // ->withTrashed()
                                    ->Filters($data)
                                    ->get();

            $ticket_types = TicketType::pluck('name','id');

            return response()->json(['ticket_types'=>$ticket_types,'tickets' => $tickets, 'message' => 'Tickets retrieved successfully'], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('ticket::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {

            $token = $request->header('token');
            $validToken = TicketToken::where('token', $token)->first();
            if (!$validToken) {
                return response()->json(['error' => 'Wrong credentials.'], 401);
            }

            DB::beginTransaction();
            $data = $request->all();
            \Log::info($data);

            $data['ticket_type_id'] = $data['ticket_type_id'];
            $data['author_full_name'] = $data['author_full_name'];
            $data['author_email'] = $data['author_email'];
            $data['author_id'] = $data['author_id'];
            $data['ticket_source'] = config('app.name');
            $data['project_id'] = TicketToken::where("token",$data['ticket_token'])->first()->project_id;
            $data['status'] = 'open';
            $ticket = Ticket::create($data);

            // Save file if exists
            if ($request->hasFile('attachment')) {

                $files = $request->file('attachment');
                foreach ($files as $key => $file) {
                    $path_to_save = 'public/module-ticket/ticket-attachment';
                    $fileName = FileHelper::uploadFile($path_to_save, $file);
                    $description = $data['description'][$key];

                    $ticketAttachment = TicketAttachment::create([
                        'attachment' => $fileName,
                        'description' => $description,
                        'ticket_id' => $ticket->id,
                        'author_id' => $data['author_id'],
                    ]);
                }
            }

            //Converti in Project TASK
            $project_task_status_id = ProjectTaskStatus::where("slug","da-assegnare")->first()->id;
            $admin_id = User::where("email","sviluppo@innover.cloud")->first()->id;

            $subject = isset($ticket->subject) ? $ticket->subject : "";
            $subject_text = $ticket->text;

            $project_task = ProjectTask::create([
                'author_id' => $admin_id,
                'project_id' => $ticket->project_id,
                'project_task_status_id' => $project_task_status_id,
                'priority' => $ticket->priority,
                'description' => $subject_text,
                'name' => $subject,
                'ticket_id' => $ticket->id,
            ]);

            $ticket->update(["project_task_id" => $project_task->id]);

            // add to activity
            activity()
           ->performedOn($ticket)
           ->causedBy(auth()->user())
           ->useLog('ticket')
           ->withProperties([
                'subject' => $ticket->subject,
                'ticketType' => $ticket->ticketType->name,
                'ticketSource' => 'other',
                'author' => $ticket->author_full_name,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'customerPriority' => $ticket->customer_priority,
                'updatedAt' => $ticket->updated_at->format('d-m-Y'),
                'projectTaskId' => $ticket->project_task_id,
                'urgeDate' => $ticket->urge_date ? $ticket->urge_date->format('d-m-Y') : null,
                'type_operation' => $ticket->type_operation,
                'operation_note' => $ticket->operation_note,
                'ticket_attachment_id' => @$ticketAttachment->id,
           ])
           ->log('Ticket update history');
            //SEND EMAIL
            TicketHelper::sendNotificationEmail($data,$ticket);
            // send whatsapp if high priority and status is da-assegnare
            if ($project_task->projectTaskStatus->sort != 2 && $project_task->priority == 'high') {
                SendProjectTaskWhatasappJob::dispatch($project_task)->delay(now()->addHours(2));
            }

            DB::commit();
            return response()->json(['message' => __('ticket::main.ticket_opened_success')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('ticket::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('ticket::edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function update(Request $request,$id)
    {
        $data = $request->all();
        try {
            $token = $request->header('token');
            $validToken = TicketToken::where('token', $token)->first();

            if (!$validToken) {
                return response()->json(['error' => 'Wrong credentials.'], 401);
            }

            DB::beginTransaction();

            $ticket = Ticket::find($id);
            // old status
            $oldStatus = $ticket->getOriginal('status');
            
            $ticket->fill($data);
            $ticket->save();

            // save to TicketAttachment
            if(isset($data['attachment']) and $data['attachment'] != 'undefined'){

                foreach ($request->file('attachment') as $key => $attachment) {
                    $path_to_save = 'public/module-ticket/ticket-attachment';
                    $fileName = FileHelper::uploadFile($path_to_save, $attachment);
                    $description = $data['description'][$key];

                    $ticketAttachment = TicketAttachment::create([
                        'attachment' => $fileName,
                        'description' => $description,
                        'ticket_id' => $ticket->id,
                        'author_id' => Auth::user()->id,
                    ]);
                }
            }

            // update project task if ticket
            $projectTask = ProjectTask::find($ticket->project_task_id);
            if ($projectTask && $ticket->status) {
                $slug = $ticket->status;
                $slugToStatus = $this->slugToStatus($slug);
                $projectTaskStatus = ProjectTaskStatus::where('slug', $slugToStatus)->first()->id;
                $completed_at = null;
                if ($slug === 'closed') {
                    $completed_at = now();
                }
                $projectTask->update([
                    "completed_at" => $completed_at,
                    "project_task_status_id" => $projectTaskStatus,
                ]);
            }

            if ($ticket->status != $oldStatus) {
                SendStausChangeTicketEmailJob::dispatch($ticket,$oldStatus);
            }

            // add to activity
            activity()
           ->performedOn($ticket)
           ->causedBy(auth()->user())
           ->useLog('ticket')
           ->withProperties([
                'subject' => $ticket->subject,
                'ticketType' => $ticket->ticketType->name,
                'ticketSource' => 'other',
                'author' => $ticket->author_full_name,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'customerPriority' => $ticket->customer_priority,
                'updatedAt' => $ticket->updated_at->format('d-m-Y'),
                'projectTaskId' => $ticket->project_task_id,
                'urgeDate' => $ticket->urge_date ? $ticket->urge_date->format('d-m-Y') : null,
                'type_operation' => $ticket->type_operation,
                'operation_note' => $ticket->operation_note,
                'ticket_attachment_id' => @$ticketAttachment->id
           ])
           ->log('Ticket update history');

            TicketHelper::sendNotificationEmail($data,$ticket);
            DB::commit();
            return response()->json(['message' => __('ticket::main.ticke_updated')], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function slugToStatus($slug) {
        $statusMap = [
            'request_submitted' => 'da-assegnare',
            'open' => 'to-do',
            'in_process' => 'in-lavorazione',
            'testing' => 'da-testare',
            'waiting_integration' => 'in-attesa-integrazione',
            'closed' => 'completato'
        ];

        return $statusMap[$slug] ?? null;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function updateTicket(Request $request, $id)
    {
        try {
            $data = $request->all();
            $ticket = Ticket::find($id);

            if ($ticket) {
                $ticket->update([
                    'urge_date' => now(),
                ]);
                
                if ($ticket->project && $ticket->project->projectPermissions) {
                    // dispatch job
                    SendTicketEmailJob::dispatch($ticket,$data);
                }
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }



}
