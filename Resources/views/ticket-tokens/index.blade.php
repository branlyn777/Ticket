@extends('layouts.app')

@section('title') Tickets @endsection

@section('breadcrumb')
    <div class="row">   
        <div class="col-md-6">
            <div class="breadcrumb">
                <ul>
                    <li><strong>{{  strtoupper(__('ticket::main.generate_token')) }}</strong></li>
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
            {{ __('ticket::main.generate_token') }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    @include('partials.buttons.modal', ['dataTarget' => '.modal-create-ticket-types', 'text' => __('ticket::main.create_token'), 'icon' => 'fa fa-plus'])
                    <br>
                    <br>
                    <div class="table-responsive m-t-20">
                        <table class="display table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>{{ __('main.name') }}</th>
                                <th>{{ __('ticket::main.token') }}</th>
                                <th class="text-center">{{ __('main.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($ticketTokens as $token)
                                    <tr>
                                        <td>{{ $token->name }}</td>
                                        <td>{{ $token->token }}</td>
                                        <td class="text-center">
                                            @include('partials.icons.modal', ['dataTarget' => '.modal-ticket-ticket-'.$token->id, 'icon' => 'ti-pencil' ])
                                            @include('partials.icons.delete', [
                                                'dataUrl' => route('ticket-tokens.destroy', $token->id ), 
                                                'text' => __('ticket::main.ticket_token_delete'), 
                                                'message' => __('ticket::main.delete_status_message'), 
                                                'btnSubmitText' => __('main.yes_delete') 
                                            ])
                                        </td>
                                    </tr>
                                    @php
                                        $formOpen = Form::model($token,array('url' => route('ticket-tokens.update',$token->id),'method'=>'put'))
                                    @endphp
                                    @include('partials.modal-model',[
                                        'modalClass' => 'modal-ticket-ticket-'.$token->id,
                                        'modalSize' => 'sm',
                                        'title' => 'Token '.$token->name,
                                        'formOpen' => $formOpen,
                                        'fields' => $token->fields()
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br><br>
    {{ $ticketTokens->links() }}
    @include('partials.modal-model',[
        'modalClass' => 'modal-create-ticket-types',
        'modalSize' => 'sm',
        'title' => 'Nuovo Token',
        'formOpen' => Form::open(array('route' => 'ticket-tokens.store', 'method' => 'post')),
        'fields' => $fields
    ])
@endsection