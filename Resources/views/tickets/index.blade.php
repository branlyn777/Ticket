@extends('layouts.app')

@section('title') Tickets @endsection
@section('breadcrumb')
	<div class="row">
		<div class="col-md-6">
		    <div class="breadcrumb">
		        <ul>
		            <li><strong>{{  strtoupper(__('ticket::main.ticket')) }}</strong></li>
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
<style>
	.chat-sidebar-container{
		height: calc(100vh - 197px);
	}
	.task-description {
		width: 100%;
		border: none !important;
		overflow: auto;
		outline: none;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none;
		box-shadow: none;
		resize: none;
		margin-left: 15px;
	}

	@media screen and (min-width: 1025px){
		.modal-dialog {
			 max-width: 50% !important;
			margin: 1.75rem auto;
		}
	}

	.tox-statusbar__branding {
      display: none;
   }

   .operator-data-letters {
        overflow: hidden;
        color: #fff !important;
        display: inline-block;
        font-size: 0.8vw;
        width: 2em;
        height: 2em;
        line-height: 2em;
        text-align: center;
        border-radius: 50%;
        vertical-align: middle;
        margin-right: 1em;
   }

   .custom-icon {
        cursor: pointer;
        font-size: 20px;
        color: #6d6e6f;
        position: absolute;
        top: 80%;
        left: 10px;
        transform: rotate(45deg);
    }

    .message-user {
        border: 2px solid #a80000;
        background: #a80000;
        width: 3em;
        height: 3em;
        line-height: 3em;
    }

    .message-width {
        position: relative;
        flex-grow: 1;
        width: 100%;
    }

    .message-submit-button {
        position: absolute;
        top: 70%;
        right: 10px;
    }

    #message {
        background-image: none !important;
        border: 1px solid #ced4da !important;
        background: #fff;
        border-radius: 10px;
        padding-top: 10px !important;
        padding-left: 10px !important;
    }

    input[name="file"] {
        opacity: 0;
        position: absolute;
        top: 80%;
        left: 0;
        height: 20%;
        width: 5%;
        cursor: pointer;
    }

     .attachment-container {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .attachment-container input[type="file"] {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .attachment-container .attachment-input-button {
        display: inline-block;
        padding: 8px 16px;
        background-color: #003473;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
</style>
@endsection
@section('content')

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
                {{  __('ticket::main.ticket') }}
            </div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-12">
						<button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modal-ticket-create"><i class="fa fa-plus-circle"></i> Crea ticket</button>
					</div>
					<div class="col-md-12 mt-3">
						@include("ticket::partials.table-list-tickets",["checkScrivania" => $checkScrivania,"tickets" => $tickets,"ticket_types" => $ticket_types])
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{{--@include('ticket::partials.file-upload')--}}
@include('partials.modal-model',[
   'title' => "CREA TICKET" ,
   'fields' => $fields,
   'modal_id' => "modal-ticket-create",
   'formOpen' => Form::open(array('route' => 'tickets.store', 'method' => 'post',"enctype"=>"multipart/form-data", 'class' => "save-ticket")),
   'modalSize' => 'lg'
])
@endsection
@section('scripts')
	@include("ticket::tickets.ticket-script")
@endsection















