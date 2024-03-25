<?php

namespace Modules\Ticket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Ticket\Entities\TicketType;
use Form;
use Illuminate\Support\Str;
use Modules\Ticket\Http\Traits\TicketTypeTrait;

class SettingsTypeController extends Controller
{
    use TicketTypeTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        $ticket_types = TicketType::all();
        $fields = $this->ticketTypeFields();

        return view('ticket::settings.index',compact('ticket_types','fields'));
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
    {}

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {}
}
