<?php

namespace Modules\Ticket\Http\Controllers;

use Google\Service\CloudResourceManager\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Project\Entities\ProjectTask;
use Modules\Ticket\Entities\TicketToken;
use Form;
use Illuminate\Support\Str;
use Modules\Ticket\Http\Traits\TicketTokenTrait;
use App\Helpers\EncodingHelper;

class TicketTokenController extends Controller
{
    use TicketTokenTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $ticketTokens = TicketToken::paginate(100);
        $fields = $this->ticketTokenFields();
        return view('ticket::ticket-tokens.index',compact('fields','ticketTokens'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required',
            'project_id'=>'required'
        ]);

        $project = \Modules\Project\Entities\Project::find($request->project_id);

        if (empty($project)){
            return redirect()->back()->with("error","Progetto non trovato");
        }

        $token_name = Str::slug($project->name)."-".EncodingHelper::generateRandomString();
        $ticket_token_save = new TicketToken();
        $ticket_token_save->fill($request->except('_token'));
        $ticket_token_save->token = $token_name;
        $ticket_token_save->fill($request->except('project_id'));
        $ticket_token_save->save();

        return redirect()->back()->with('success',__('ticket::main.ticket_token_created'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'project_id'=>'required'
        ]);
        
        $ticket_token = TicketToken::find($id);
        $project = \Modules\Project\Entities\Project::find($request->project_id);
        if (empty($project)){
            return redirect()->back()->with("error","Progetto non trovato");
        }

        $token_name = $ticket_token->project_id != $project->id ? Str::slug($project->name)."-".EncodingHelper::generateRandomString() : $ticket_token->token;

        $ticket_token->token = $token_name;
        $ticket_token->fill($request->except('_token'));
        $ticket_token->fill($request->except('project_id'));
        $ticket_token->save();

        return redirect()->back()->with('success',__('ticket::main.ticket_token_updated'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        TicketToken::where('id',$id)->delete();
        return redirect()->back()->with('success',__('ticket::main.ticket_token_deleted'));
    }
}