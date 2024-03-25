<?php

namespace Modules\Ticket\Http\Controllers;

use App\Helpers\FileHelper;
use App\Helpers\MailHelper;
use App\Http\Controllers\BaseController;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Modules\ManagementFile\Entities\ManagementFolderProject;
use Modules\Project\Entities\ProjectTask;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketType;
use Modules\Ticket\Entities\TicketToken;
use Modules\Ticket\Entities\TicketAttachment;
use Modules\Ticket\Helpers\TicketHelper;
use Modules\Ticket\Http\Traits\TicketTrait;
use Spatie\Activitylog\Models\Activity;
use Modules\Ticket\Jobs\SendTicketEmailJob;
use Modules\Ticket\Jobs\SendStausChangeTicketEmailJob;
use App\Helpers\HostHelper;
use Illuminate\Validation\Rule;
use Modules\Project\Entities\ProjectTaskStatus;
use Modules\Project\Jobs\SendProjectTaskWhatasappJob;
use Auth;
use Form;
use Lang;
use Config;
use Mail;
use DB;
use File;
use DOMDocument;

class TicketController extends BaseController
{
    use TicketTrait;

    /**
     * TicketController constructor.
     */
    public function __construct()
    {
        $this->class_name = get_class();
    }


    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {

        $data = $request->all();

        if (isset($data['id']) && !is_int($data['id'])) {
            $data['id'] = base64_decode($data['id']);
        }

        $data = $this->getSessionFilters($request, $data);
        // Ordinamento di default
        if (!isset($data['sort'])) {
            $data["sort"] = "id desc";
        }

        $this->saveSessionFilters($request, $data);
        $checkScrivania = HostHelper::isScrivania();
        //Check host , se è scrivania prendo direttamente  i tickets del db , in caso contrario ottengo i tickets tramite API

        if ($checkScrivania ) {
            $tickets = Ticket::Filters($data)
                ->with('ticketToken')
                ->with(['ticketType' => function ($query) {
                    $query->withTrashed();
                }])
                // ->withTrashed()
                ->with('operator')
                ->get();
            $ticket_types = TicketType::pluck('name','id');
        }else{
            $ticket_response = $this->getTickets($data);
            $tickets = !empty($ticket_response['tickets'])? $this->convertArrayToObjectRecursive($ticket_response['tickets']):[];
            $ticket_types = !empty($ticket_response['ticket_types'])?$ticket_response['ticket_types']:[];

        }
        //$tickets = $this->orderPriority($tickets);
        //FILTRI
        $filters = $this->filters($data,$ticket_types);
        //CAMPI TICKET
        $fields = $this->fields(null,$ticket_types);

        $filters = view('partials.panel-filters',['filters' => $filters, 'formOpen' => Form::open(array('route' => 'ticketFilters','class' => 'mb-0')) , 'reset' => route('ticketFilters') . '?reset=1' ])->render();
        return view('ticket::tickets.index',compact(
            'tickets',
            'ticket_types',
            'checkScrivania',
            'fields',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('ticket::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $data = $request->all();
            
            $data['author_id'] = Auth::user()->id;
            if ($data['text']) {
                $dom = new DOMDocument();
                $dom->loadHTML($data['text'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);
                $aTags = $dom->getElementsByTagName('a');
                foreach ($aTags as $aTag) {
                    $text = $aTag->textContent;
                    
                    $aTag->setAttribute('href', $text);
                }
                $data['text'] = $dom->saveHTML();

                //Serve a pulire il tag Img
                $text = $this->cleanDescription($data['text']);
                $data['text'] = $text;
            }

            $checkScrivania = HostHelper::isScrivania();

            if ($checkScrivania) {
                $data['author_email'] = Auth::user()->email;
                $data['author_full_name'] = Auth::user()->getFullName();
                $data['ticket_source'] = config('app.name');
                $data['status'] = 'open';
                $saveTicket = Ticket::create($data);

                if(isset($data['attachment']) and $data['attachment'] != 'undefined'){

                    foreach ($request->file('attachment') as $key => $attachment) {
                        $path_to_save = 'public/module-ticket/ticket-attachment';
                        $fileName = FileHelper::uploadFile($path_to_save, $attachment);
                        $description = $data['description'][$key];

                        $ticketAttachment = TicketAttachment::create([
                            'attachment' => $fileName,
                            'description' => $description,
                            'ticket_id' => $saveTicket->id,
                            'author_id' => Auth::user()->id,
                        ]);
                    }
                }

                //Converti in Project TASK
                $project_task_status_id = ProjectTaskStatus::where("slug","da-assegnare")->first()->id;
                $admin_id = User::where("email","sviluppo@innover.cloud")->first()->id;

                $subject = isset($saveTicket->subject) ? $saveTicket->subject : "";
                $subject_text = $saveTicket->text;

                $project_task = ProjectTask::create([
                    'author_id' => $admin_id,
                    'project_id' => $saveTicket->project_id,
                    'project_task_status_id' => $project_task_status_id,
                    'priority' => $saveTicket->priority,
                    'description' => $subject_text,
                    'name' => $subject,
                    'ticket_id' => $saveTicket->id,
                ]);

                $saveTicket->update(["project_task_id" => $project_task->id]);

                // add to activity
                activity()
               ->performedOn($saveTicket)
               ->causedBy(auth()->user())
               ->useLog('ticket')
               ->withProperties([
                    'subject' => $saveTicket->subject,
                    'ticketType' => $saveTicket->ticketType->name,
                    'ticketSource' => HostHelper::isScrivania() ? 'scrivania' : 'other',
                    'author' => $saveTicket->author_full_name,
                    'status' => $saveTicket->status,
                    'priority' => $saveTicket->priority,
                    'customerPriority' => $saveTicket->customer_priority,
                    'updatedAt' => $saveTicket->updated_at->format('d-m-Y'),
                    'projectTaskId' => $saveTicket->project_task_id,
                    'urgeDate' => $saveTicket->urge_date ? $saveTicket->urge_date->format('d-m-Y') : null,
                    'type_operation' => $saveTicket->type_operation,
                    'operation_note' => $saveTicket->operation_note,
                    'ticket_attachment_id' => @$ticketAttachment->id,
               ])
               ->log('Ticket update history');

               TicketHelper::sendNotificationEmail($data,$saveTicket);

               // send whatsapp if high priority and status is da-assegnare
               if ($project_task->projectTaskStatus->sort != 2 && $project_task->priority == 'high') {
                    SendProjectTaskWhatasappJob::dispatch($project_task)->delay(now()->addHours(2));
                }

            }else{
                $ticket = $this->saveTicket($data);

                if (!empty($ticket['errors'])) {
                    return redirect()->back()->with('error', $ticket['message']);
                }
            }

            DB::commit();
            return response()->json(['vanillaAlert'=>true,'vanillaStatus' => 'success','vanillaTitle'=> __('ticket::main.ticket_opened_success')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['vanillaAlert'=>true,'vanillaStatus'=>'error','vanillaTitle'=>'Qualcosa è andato storto']);
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $ticket = Ticket::find($id);
        $ticketMessagesHtml = view('ticket::partials.chat-content',compact('ticket'))->render();
        return response()->json(['success'=>true,'html'=>$ticketMessagesHtml]);
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('ticket::ticket.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,$id)
    {
        if ($request->ajax()) {
            $request->validate([
                'text' => 'required',
                'operation_note' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->status === 'closed';
                    }),
                ],
            ], [
                'operation_note.required_if' => 'Il campo type intervation è richiesto.',
            ]);
        }

        try {
            DB::beginTransaction();
            $checkScrivania = HostHelper::isScrivania();
            $data = $request->all();

            $data['author_id'] = Auth::user()->id;
            if ($request->has("text") && $data['text']) {
                //Serve a pulire il tag Img
                $text = $this->cleanDescription($data['text']);
                $data['text'] = $text;
            }
            if ($request->has("operation_note") && $data['operation_note']) {
                $typeIntervation = $this->cleanDescription($data['operation_note']);
                $data['operation_note'] = $typeIntervation;
            }
            if ($checkScrivania) {

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

             //$email_ticket_operator=User::where('id',$ticket->operator_id)->first();
             //$ticket_type=TicketType::where('id',$ticket->ticket_type_id )->first();

             //invio email
            $data["author_email"] = $ticket->author_email;
            TicketHelper::sendNotificationEmail($data,$ticket);
            
            // add to activity
            activity()
           ->performedOn($ticket)
           ->causedBy(auth()->user())
           ->useLog('ticket')
           ->withProperties([
                'subject' => $ticket->subject,
                'ticketType' => $ticket->ticketType->name,
                'ticketSource' => HostHelper::isScrivania() ? 'scrivania' : 'other',
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

            }else{
                $ticket = $this->updateTicket($data,$id);
                if (!empty($ticket['errors'])) {
                    return response()->json(['vanillaAlert'=>true,'vanillaStatus'=>'error','vanillaTitle'=>'Qualcosa è andato storto']);
                }
            }
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json(['vanillaAlert' => true, 'vanillaStatus' => 'success', 'vanillaTitle' => __('ticket::main.ticke_updated')]);
            }

            return redirect()->back()->with('success', __('ticket::main.ticke_updated'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['vanillaAlert'=>true,'vanillaStatus'=>'error','vanillaTitle'=>'Qualcosa è andato storto']);
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
     * @return Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
                $ticket = Ticket::find($id);
                $ticket->projectTasks->each(function ($projectTask) {
                    $projectTask->delete();
                });
                $ticket->delete();
            DB::commit();
            return redirect()->back()->with('success', __('ticket::main.ticket_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    //upload files to tmp folders
    public function saveTmpFile(Request $request){
        $file = $request->file('files');
        $path_to_save =  'public/module-ticket/tmp/';
        $fileName = \App\Helpers\FileHelper::uploadFile($path_to_save, $file);
     
        return response()->json([
            "executeFunction" => "addPhotoUploaded",
            "executeFunctionParams" => view("ticket::partials.tmp-single-photo",["filename"=>$fileName])->render()
        ]);
    }

    // CONVERTI TICKET IN TASK
    public function convertToTask(Request $request,$id){

        $date_start = Carbon::parse($request->date_start);

        $expiry_date = Carbon::parse($request->expiry_date);
        $differentDate =$expiry_date->gt($date_start);

        if($differentDate==FALSE){
            return response()->json(['vanillaAlert'=>true,'vanillaStatus'=>'error','vanillaTitle'=>'la data Inizio non puo essere minore della data Fine Correggi']);
        }
        $data = $request->all();
        $ticket = Ticket::find($id);

        $project_task = ProjectTask::create([
            'author_id'=>Auth::user()->id,
            'operator_id'=>$data["operator_id"],
            'project_id'=> $data["project_id"],
            'project_task_status_id' => $data["project_status_id"],
            'priority' => $data["priority"],
            'date_start' => $date_start,
            'expiry_date' => $expiry_date,
            'description' => $ticket->text
        ]);

        $ticket->update(["project_task_id" =>$project_task->id ]);

       return redirect()->back()->with("success","Task Convertito");
        return response()->json([
            'executeFunction' => 'closeModalTicket',
            'executeFunctionParams' => "modal-convert-to-task-".$item->id,
        ]);

    }


    public function convertArrayToObjectRecursive($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {

                $array[$key] = $this->convertArrayToObjectRecursive($value);
            }
            return (object)$array;
        } else {
            return $array;
        }
    }

    public function cleanDescription($description)
    {
        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/', $description, $matches);

        foreach ($matches[1] as $relative_url) {
            $absolute_url = asset(str_replace('../', '', $relative_url));
            $description = str_replace($relative_url, $absolute_url, $description);
        }

        return $description;
    }

    public function descriptionImageUpload(Request $request)
    {
        try {

            $file = $request->file('file');
            $path = "public/module-ticket/ticket-text/";
            $file_name = FileHelper::uploadFile($path,$file);
            $full_path = asset(\Storage::url($path.$file_name));

            return response()->json(['location' => $full_path]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function updateTicket(Request $request, $id)
    {
        try {
            $data = $request->all();
            $checkScrivania = HostHelper::isScrivania();

            if ($checkScrivania) {
                $ticket = Ticket::find($id);

                if ($ticket) {
                    $ticket->update([
                        'urge_date' => now(),
                    ]);
                    
                    if ($ticket->project) {
                        // dispatch job
                        SendTicketEmailJob::dispatch($ticket,$data=null);
                    }
                }
            }else{
                $ticket = $this->urgeTicket($data,$id);

                if (!empty($ticket['errors'])) {
                    return redirect()->back()->with('error', $ticket['message']);
                }                
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

}
