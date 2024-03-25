<?php

namespace Modules\Ticket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Ticket\Entities\TicketType;

class TicketTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $ticket_types = TicketType::all();
        $fields = $this->ticketTypeFields();

        return view('ticket::settings.index',compact('ticket_types','fields'));
        return view('ticket::index');
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
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        $ticket_type_save = new TicketType();
        $ticket_type_save->fill($request->except('_token'));
        $ticket_type_save->save();

        return redirect()->back()->with('success',__('ticket::main.ticket_type_created'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('ticket::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('ticket::edit');
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

        ]);
        $ticket_type_save = TicketType::find($id);
        $ticket_type_save->fill($request->except('_token'));
        $ticket_type_save->save();

        return redirect()->back()->with('success',__('ticket::main.ticket_type_updated'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $ticket_type = TicketType::find($id);
        $ticket_type->delete();
        return redirect()->back()->with('success',__('ticket::main.ticket_type_deleted'));
    }
}
