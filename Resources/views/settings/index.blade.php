@extends('layouts.app')

@section('title') Tickets @endsection

@section('breadcrumb')
    <div class="row">
        <div class="col-md-6">
            <div class="breadcrumb">
                <ul>
                    <li><strong>{{  strtoupper(__('ticket::main.ticket_type')) }}</strong></li>
                    <li>Lista</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            @include('ticket::partials.nav-bar')
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>
@endsection

@section('head')
@endsection
@section('content')
	<div class="card">
        <div class="card-header">
            {{ __('ticket::main.ticket_type') }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    @include('partials.buttons.modal', ['dataTarget' => '.modal-create-ticket-types', 'text' => __('ticket::main.create_ticket_type'), 'icon' => 'fa fa-plus'])
                    @include('partials.modal-model',[
                        'modalClass' => 'modal-create-ticket-types',
                        'modalSize' => 'sm',
                        'title' => __('ticket::main.create_ticket_type'),
                        'formOpen' => Form::open(array('route' => 'ticket-types.store', 'method' => 'post')),
                        'fields' => $fields
                    ])

                    <div class="table-responsive mt-2">
                        <table class="display table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th style="width: 80%">{{ __('main.name') }}</th>
                                <th class="text-center">{{ __('main.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(count($ticket_types))
                                    @foreach($ticket_types as $ticket_type)
                                        <tr>
                                            <td>{{$ticket_type->name}}</td>
                                            <td class="text-center">
                                                {{--Modifica--}}
                                                @include('partials.icons.modal', ['class'=>'','dataTarget' => '.modal-ticket-type-'.$ticket_type->id, 'icon' => 'ti-pencil' ])
                                                @include('partials.modal-model',[
                                                    'modalClass' => 'modal-ticket-type-' . $ticket_type->id,
                                                    'title' => "Aggiorna tipologia ticket",
                                                    'formOpen' => Form::model($ticket_type,array('url' => route('ticket-types.update',$ticket_type->id), 'method' => 'PUT')),
                                                    'fields' => $ticket_type->fields(),

                                                ])
                                                @include('partials.icons.delete', ['dataUrl' => route('ticket-types.destroy', $ticket_type->id ), 'text' => __('ticket::main.ticket_type_delete'), 'message' => __('ticket::main.delete_status_message'), 'btnSubmitText' => __('main.yes_delete') ])
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">Nessun record</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection