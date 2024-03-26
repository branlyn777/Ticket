@extends('layouts.app')
@section('title') Storico ticket @endsection
@section('head')
    <style type="text/css">
        .blue-color-btn {
            background-color: #003473 !important;
            color: #ffffff !important;
        }

        .font-14 {
            font-size: 14px;
        }

        .btn-rounded {
            border-radius: 40px !important;
            color: #ffffff !important;
            width: 14rem;
        }

        @media (min-width: 992px) {
          .modal-lg {
            max-width: 800px !important;
          }
        }

        p {
            margin-bottom: 0px;
        }

        p > img {
            display: none;
        }

        .img-width-editor {
            width: 100px;
        }

        .description{
            margin-top: 1rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 16px;
            line-height: 1.4;
        }
    </style>
@endsection
@section('breadcrumb')
<div class="breadcrumb" style="margin: 0 -15px 1rem;">
    <div class="row col-12 pr-0">
        <div class="col-md-6 p-0">
            <ul>
                <li>
                    <a href="{{ route('tickets.index') }}">
                    <strong>{{ strtoupper(__("ticket::main.ticket")) }}</strong>
                    </a>
                </li>
                <li>Storico ticket</li>
            </ul>
        </div>
        <div class="col-md-6 d-flex justify-content-end p-0 mt-2">
            <a href="{{ route('tickets.index') }}" class="btn btn-info d-none d-lg-block m-l-15 text-white"><i class=" icon-arrow-left"></i> {{ __('main.back') }}</a>
        </div>
    </div>
</div>
<div class="separator-breadcrumb border-top"></div>
@endsection
@section('content')
<div class="main-content">
    <div class="row">
        <div class="col-md-12 d-flex">
            <div class="col-md-2"></div>
            <div class="col-md-7 pl-0 mb-4">
                @if(\App\Helpers\HostHelper::isScrivania())
                <div class="d-flex justify-content-around">
                    <a href="javascript:void(0)" class="btn btn-light btn-rounded btn-lg font-14 {{ !empty($historyUser['created_by']) ? 'blue-color-btn' : '' }}">
                        <div class="text-center">Creato da</div>
                        <div class="text-center">{{ !empty($historyUser['created_by']) ? $historyUser['created_by'] : '-' }}</div>
                    </a>

                    <a href="javascript:void(0)" class="btn btn-light btn-rounded btn-lg font-14 {{ !empty($historyUser['operator_id']) ? 'blue-color-btn' : '' }}">
                        <div class="text-center">Owner</div>
                        <div class="text-center">{{ !empty($historyUser['operator_id']) ? $historyUser['operator_id'] : '-' }}</div>
                    </a>

                    <a href="javascript:void(0)" class="btn btn-light btn-rounded btn-lg font-14 {{ !empty($historyUser['tester_id']) ? 'blue-color-btn' : '' }}">
                        <div class="text-center">Testato da</div>
                        <div class="text-center">{{ !empty($historyUser['tester_id']) ? $historyUser['tester_id'] : '-' }}</div>
                    </a>

                    <a href="javascript:void(0)" class="btn btn-light btn-rounded btn-lg font-14 {{ !empty($historyUser['publisher_id']) ? 'blue-color-btn' : '' }}">
                        <div class="text-center">Pubblicato da</div>
                        <div class="text-center">{{ !empty($historyUser['publisher_id']) ? $historyUser['publisher_id'] : '-' }}</div>
                    </a>
                </div>
                @endif
            </div>
            <div class="col-md-3 pr-0" style="text-align: end;">
                @php
                    $ticketId = is_array($ticketId) ? $ticketId[0] : $ticketId;
                @endphp
                <button class="btn btn-primary message_class comment_class" data-toggle="modal" data-target="#modal-note-ticket-{{$ticketId}}" id="{{$ticketId}}" style="vertical-align: middle;" data-toggle="tooltip" data-original-title="{{ __('main.add') }} {{ __('main.note') }}">{{ __('main.add') }} {{ __('main.note') }}</button>
                @if(\App\Helpers\HostHelper::isScrivania())
                <button class="btn btn-primary message_class edit_ticket_class ticket_history_columns" data-toggle="modal" data-target="#modal-reply-ticket-{{$ticketId}}" id="{{$ticketId}}" style="vertical-align: middle;" data-toggle="tooltip" data-original-title="{{ __('main.status') }} {{ __('ticket::main.ticket') }}">{{ __('main.status') }} {{ __('ticket::main.ticket') }}</button>
                @endif
            </div>
        </div>
        <div class="col-md-12">
            @if(is_array($getLogs))
            @php
              $logsCollection = collect($getLogs);
              $getLogs = $logsCollection->map(function ($item) {
                return new Spatie\Activitylog\Models\Activity($item);
              });
            @endphp
            @endif
            @foreach($getLogs as $singleLog)
            @php
                $getLog = $singleLog;
                $getFiles = $singleLog['files'];
                $ticketStatuses = ['request_submitted'=>'Richiesta inoltrata','open'=>'Aperto','in_process'=>'In lavorazione','testing'=>'In testing','waiting_integration'=>'In attesa integrazione','closed'=>'Chiuso'];
                $ticketStatus = '';
                if (array_key_exists($getLog->properties['status'], $ticketStatuses)) {
                    $ticketStatus = $ticketStatuses[$getLog->properties['status']];
                }

                $typeOperationDisplay = ['close_with_result' => 'Chiuso con esito', 'close_without_result' => 'Chiuso senza esito'];
                $typeOperationValue = @$getLog->properties['type_operation'];
                if (array_key_exists($typeOperationValue, $typeOperationDisplay)) {
                    $typeOperationValue = $typeOperationDisplay[$typeOperationValue];
                }
                $urgeDate = $singleLog->properties['urgeDate'];
                $ticketSource = $singleLog->properties['ticketSource'];
                $user = \App\User::whereRaw("CONCAT(name, ' ', COALESCE(lastname, '')) = ?", [$singleLog['userName']])->first();

                if (\App\Helpers\HostHelper::isScrivania() && $singleLog->properties['ticketSource'] == 'scrivania') {
                    $alignCard = true;
                }elseif (!\App\Helpers\HostHelper::isScrivania() && $singleLog->properties['ticketSource'] == 'other') {
                    $alignCard = true;
                }else{
                    $alignCard = false;
                }

                $showMessage = false;
                $checkScrivania = App\Helpers\HostHelper::isScrivania();
                if ($checkScrivania || ($singleLog->properties['ticketSource'] === 'other' || $getLog->private == 1)) {
                    $showMessage = true;
                } else {
                    $showMessage = false;
                }
                if (!isset($singleLog->properties['ticket_message_id'])) {
                    $showMessage = true;
                }
            @endphp
            {{-- <ul class="timeline clearfix" @if($urgeDate && $ticketSource != 'scrivania') style="display: flex;" @endif> --}}
            @if($showMessage)
            <ul class="timeline clearfix" @if(!$alignCard) style="display: flex;" @endif>
                <li class="timeline-line"></li>
                <div class="clearfix"></div>
                <li class="timeline-item timeline-right mt-4" style="padding-right: 3rem;">
                    <div class="timeline-badge" @if(!$alignCard) style="left: calc(100% - 24px);" @endif>
                        @if(\App\Helpers\HostHelper::isScrivania() && $user)
                            <img class="badge-img" src="{{ $user->getAvatar() }}" style="background: white;border: 2px solid #efeeee;">
                        @else
                            @if($singleLog->properties['ticketSource'] == 'scrivania')
                                <img class="badge-img" src="{{ asset('/assets/images/innover-light.jpeg') }}" style="background: white;border: 2px solid #efeeee;">
                            @else
                                <img class="badge-img" src="{{ asset('/assets/images/default_avatar.png') }}" style="background: white;border: 2px solid #efeeee;">
                            @endif
                        @endif
                    </div>
                    <div class="timeline-card card">
                        <div class="mt-2 mr-2" style="text-align: end;">
                            @include('partials.icons.delete',[
                                'dataUrl' => route('ticket-histories.destroy', ['ticket_history' => $getLog->id] ),
                                'title' => __('main.delete') . ' ' . __('main.document'),
                                'btnSubmitText' => __('main.delete')
                            ])
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-1">
                                <strong class="mr-1">
                                    <span style="font-family: Sans-Serif;color: #e06046;">
                                        {{ $ticketStatus }}
                                    </span> (ID:{{ $getLog->id }})
                                    <span class="pull-right">{{ $singleLog->userName }}</span>
                                </strong>
                                <br>
                                <small class="text-muted">{{ $getLog->created_at->format('d-m-Y H:i') }}</small>
                            </div>
                            <div class="row">
                                @php
                                    $hide = null;
                                    $hideClosed = null;
                                    if ($getLog->properties['status'] == 'open' || $getLog->properties['status'] == 'waiting_integration' || $getLog->properties['status'] == 'testing') {
                                        $hide = false;
                                    }else{
                                        if ($getLog->properties['status'] != 'closed') {
                                            $hide = true;
                                            $hideClosed = true;
                                        }
                                    }

                                    if ($getLog->properties['status'] == 'closed' && @$getLog->properties['type_operation']) {
                                        $hideClosed = true;
                                    }
                                @endphp
                                @if($checkScrivania)
                                <div class="col-md-4 mb-2">
                                    <i class="ti-view-grid"></i>
                                    <strong><small>Progetto</small></strong><br>
                                    <span style="font-family: Sans-Serif;">
                                        {{ $singleLog->projectTask ? $singleLog->projectTask['project']['name'] : '-' }}</span>
                                </div>
                                @endif
                                @if($checkScrivania)
                                <div class="col-md-4 mb-2">
                                    <i class="icon-user"></i>
                                    <small>Creato da</small><br>
                                    <span style="font-family: Sans-Serif;">{{ $getLog->properties['author'] }}</span>
                                </div>
                                @endif
                                @if($checkScrivania)
                                <div class="col-md-4 mb-2">
                                    <i class="icon-calender"></i>
                                    <small>
                                        Data {{ $ticketStatus }}
                                    </small><br>
                                    <span style="font-family: Sans-Serif;">{{ $getLog->properties['updatedAt'] }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                @if($hideClosed)
                                <div class="col-md-4 mb-2">
                                    <i class="ti-ticket"></i>
                                    <strong><small>Tipo intervento:</small></strong><br>
                                    <span style="font-family: Sans-Serif;">
                                        {{ $typeOperationValue??'-' }}</span>
                                </div>
                                @endif
                                @if($checkScrivania)
                                <div class="col-md-4 mb-2">
                                    <i class="ti-ticket"></i>
                                    <strong><small>Data apertura</small></strong><br>
                                    <span style="font-family: Sans-Serif;">
                                        {{ \Carbon\Carbon::parse($ticket['created_at'])->format('d-m-Y') }}
                                    </span>
                                </div>
                                @endif
                                @if($checkScrivania)
                                <div class="col-md-4 mb-2">
                                    <i class="ti-ticket"></i>
                                    <strong><small>Data chiusara ticket</small></strong><br>
                                    <span style="font-family: Sans-Serif;">
                                        @if($getLog->properties['status'] === 'closed')
                                        {{ $getLog->created_at->format('d-m-Y') }}</span>
                                        @else
                                        -
                                        @endif
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                
                                @if($checkScrivania)

                                <div class="col-md-4 mb-2">
                                    <i class="ti-ticket"></i>
                                    <strong><small>Archivio:</small></strong><br>
                                    @foreach($singleLog->ticketAttachments as $archivio)
                                        <a class="text-primary" href="{{ route('ticketAttachmentDownload', ['id' => $archivio['id']]) }}" download="{{ $archivio['file_path'] }}">{{ substr($archivio['file_path'], 0, 17) }}...</a><br>
                                    @endforeach
                                </div>
                                


                                @endif

                            </div>
                            @if($hideClosed)
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <i class="ti-notepad"></i>
                                    <strong><small>Note intervento:</small></strong><br>
                                    <span style="font-family: Sans-Serif;">
                                        {!! $getLog->properties['operation_note']??'-' !!}</span>
                                </div>
                            </div>
                            @endif
                            @if(isset($getLog->properties['comment']))
                            <div class="col-md-12 p-0">
                                <i class=" icon-bubble"></i>
                                <small>Motivazione</small><br>
                                <span style="font-family: Sans-Serif;">
                                    {!! $getLog->properties['comment'] !!}
                                </span>
                            </div>
                            @endif
                            @if($getLog->message)
                            <div class="row mb-1">
                                <div class="col-12 d-flex flex-column">
                                    <div style="align-items: center;" class="d-flex">
                                        <i class="icon-bubble"></i>
                                        <small class="ml-1">Note</small>
                                    </div>
                                    <div class="row col-12">
                                        <span>{!! $getLog->message !!}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if(isset($getLog->properties['task_description']))
                            <div class="row mb-1">
                                <div class="col-12 d-flex flex-column">
                                    <div style="align-items: center;" class="d-flex">
                                        <i class="icon-bubble"></i>
                                        <small class="ml-1">Descrizione</small>
                                    </div>
                                    <div class="row col-12 description">
                                        <span>{!! str_replace(['<a', '<img'], ['<a style="color: mediumblue !important;"', '<img style="max-width: 600px; max-height: 338px;"'], $getLog->properties['task_description']) !!}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            {{-- files --}}
                            @if(!empty($getFiles))
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="list-group">
                                        @foreach( $getFiles as $file )
                                        {{-- @dd($file) --}}
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @if($file['description'])
                                                {{ $file['description'] }}
                                            @else
                                                @if (!is_string($file))
                                                {{ pathinfo($file['file_path'], PATHINFO_FILENAME) }}
                                                @endif
                                            @endif
                                            <span class="pull-right m-0">
                                                @if (!is_string($file))
                                                @if(\App\Helpers\HostHelper::isScrivania())
                                                <a href="{{ route('ticketMessageDownloadFile', ['id' => $file['id'], 'filename' => $file['file_path']]) }}" class="mr-0"><i class="ti-download pointer text-info mr-1" data-toggle="tooltip" title="Scarica"></i></a>
                                                @else
                                                <a href="{{ config('services.tickets.domain_download'). 'module/ticket-message/file/download/'.$file['id'].'/'.$file['file_path'] }}" class="mr-0"><i class="ti-download pointer text-info mr-1" data-toggle="tooltip" title="Scarica"></i></a>
                                                @endif
                                                @endif
                                                @if (!is_string($file))
                                                @include('partials.icons.delete',[
                                                    'dataUrl' => route('ticket-message-files.destroy', $file['id'] ),
                                                    'title' => __('main.delete') . ' ' . __('main.document'),
                                                    'btnSubmitText' => __('main.delete')
                                                ])
                                                @endif
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </li>
            </ul>
            @endif
            @endforeach
        </div>
    </div>
</div>
@php
    $ticket_types = Modules\Ticket\Entities\TicketType::pluck('name','id');
    if(\App\Helpers\HostHelper::isScrivania()) {
        $formOpen = Form::model($ticket,array('url' => route('tickets.update',$ticketId), 'method' => 'PUT','class'=>'save-ticket', 'enctype' => 'multipart/form-data'));
        $fieldsEdit = Modules\Ticket\Entities\Ticket::fieldsEditShow($ticket,$ticket_types);
    }
    $formOpenMessage = Form::open(array('url' => route('ticket-messages.store',['ticket_id'=>$ticketId]), 'method' => 'POST','class'=>'', 'enctype' => 'multipart/form-data'));
    $fieldsTicketMessage = Modules\Ticket\Entities\Ticket::fieldsTicketMessageShow($ticket,$ticket_types);
@endphp
{{-- Reply Ticket --}}
@if(\App\Helpers\HostHelper::isScrivania())
@include('partials.modal-model',[
    'modal_id' => "modal-reply-ticket-". $ticketId,
    'modalClass' => 'history-modal model_message_check modal-reply-ticket-' . $ticketId,
    'title' => __('ticket::main.ticket') . " " . $ticket->subject,
    'formOpen' => $formOpen,
    'fields' => $fieldsEdit,
    'modalSize' => 'lg'
])
@endif

{{-- ticket notes --}}
@include('partials.modal-model',[
    'modal_id' => "modal-note-ticket-". $ticket['id'],
    'modalClass' => 'modal-note-ticket-' . $ticket['id'],
    'title' => __('main.add') .' '. __('main.note'),
    'formOpen' => $formOpenMessage,
    'fields' => $fieldsTicketMessage,
    'modalSize' => 'lg',
])
@endsection
@section('scripts')
    @include("ticket::tickets.ticket-script")
    <script>
        $('body').on('click', '.ticket_history_columns', function() {
            var ticketModal = $($(this).attr('data-target'));
            ticketModal.find('div.col-md-6, div.col-md-12').each(function() {
                var $outerDiv = $(this);
                if ($outerDiv.find('#subject, #ticket_type_id, #priority, #text, .table-responsive').length > 0) {
                    $outerDiv.hide();
                }
            });
            ticketModal.find('.attachment-div').hide();
        });
    </script>
    {{-- lock unlock in ticket history note --}}
    <script>
        $(document).ready(function() {
            $('body').on('click', '.private', function() {
                var private = $(this).data('value');

                $(this).toggleClass('ti-unlock ti-lock');
                $(this).toggleClass('text-success text-danger');

                if (private === 0) {
                    private = 1;
                } else {
                    private = 0;
                }
                $(this).data('value', private);

                var title = (private === 0) ? 'Privato' : 'Pubblico';
                $(this).attr('data-original-title', title);

                $(this).closest('.col-md-12').find('input[name="private"]').val(private);
            });
        });
    </script>
@endsection