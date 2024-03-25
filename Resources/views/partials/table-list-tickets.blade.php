<div class="table-responsive">
    <table data-order='[[ 1, "desc" ]]' id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="width: 80px;">{{ __('ticket::main.ticket_id') }}</th>
            <th>{{ __('ticket::main.opened_date') }}</th>
            <th>{{ __('main.author') }}</th>
            @if($checkScrivania)
                <th>{{ __('main.client') }}</th>
                {{--<th>{{ __('ticket::main.operator') }}</th>--}}
            @endif
            <th>{{ __('main.subject') }}</th>
            <th>{{ __('main.type') }}</th>
            @if($checkScrivania)
                <th>{{ __('ticket::main.priority') }}</th>
                <th>{{ __('ticket::main.customer_priority') }} Int.</th>
            @endif
            <th>Data aggiornamento</th>
            <th class="text-center">{{ __('main.status') }}</th>
            <th class="text-center" style="width: 10%">{{ __('main.actions') }}</th>
        </tr>
        </thead>
        <tbody>

        @foreach($tickets as $ticket)
            <tr>
                <td>#{{ $ticket->id }}</td>
                <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d-m-Y H:i') }}</td>
                <td>{{ $ticket->author_full_name }}</td>

                @if($checkScrivania)
                    <td>{{$ticket->project ? $ticket->project->name : null}}</td>
                    {{--<td>{{!empty($ticket->operator) ? $ticket->operator->getFullName() : null }}</td>--}}
                @endif
                <td data-toggle="modal" data-target=".modal-subject-ticket-{{ $ticket->id }}" style="cursor: pointer;">
                    {{ $ticket->subject}}
                </td>
                <td>{{ $checkScrivania ? $ticket->ticketType->name : $ticket->ticket_type->name }}</td>
                @if($checkScrivania)
                    <td>
                        @if(!empty($ticket->priority))
                            {{ __('ticket::main.'.$ticket->priority) }}
                        @endif
                    </td>
                    <td>
                        @if(!empty($ticket->customer_priority))
                            {{ __('ticket::main.'.$ticket->customer_priority) }}
                        @endif
                    </td>
                @endif
                <td>
                    {{ \Carbon\Carbon::parse($ticket->updated_at)->format('d-m-Y H:i') }}
                </td>
                <td class="text-center">
                    <span class="badge badge-{{ $ticket->status_label }}">{{ __('ticket::main.'.$ticket->status) }}</span>
                </td>
                <td class="text-center align-middle" style="vertical-align: middle;">
                    @if($ticket->status != "closed")
                    <div class="mb-2">
                        <span class="btn btn-outline-danger waves-effect btn-sm pointer message_class"  data-toggle="modal" data-target="#modal-closed-ticket-{{$ticket->id}}" id="{{$ticket->id}}" style="vertical-align: middle;padding: 2px 8px !important;">
                            Chiudi ticket
                        </span>
                    </div>
                    @endif
                    @if($ticket->status !== 'closed')
                        @php
                            $urgeDate = $ticket->urge_date ?? $ticket->created_at;
                            $addDays = $ticket->priority === 'high' ? 1 : 2;
                            $openDate = Modules\Ticket\Helpers\TicketHelper::calculateUrgeDate($urgeDate, $addDays)->format('Y-m-d');
                            $today = now()->format('Y-m-d');
                        @endphp
                        @if( $openDate <= $today)
                            <span class="pointer message_class " data-toggle="tooltip" style="vertical-align: middle;" title="Sollecita" onclick="updateTicket(this, {{ $ticket->id }})">
                                <i class="ti-bell icon-size pointer" style="vertical-align: middle;"></i>
                            </span>
                        @endif
                    @endif
                    {{-- <span class="pointer " data-toggle="tooltip" style="vertical-align: middle;" title="Note"> --}}
                    {{-- <span class="pointer message_class comment_class" data-toggle="modal" data-target="#modal-note-ticket-{{$ticket->id}}" id="{{$ticket->id}}" style="vertical-align: middle;"  data-id="{{ base64_encode($ticket->id) }}">
                        <i class="fa fa-comment-o icon-size pointer" style="vertical-align: middle;" title="Note"></i>
                    </span> --}}
                    @if($checkScrivania)
                    {{-- <span class="pointer message_class edit_ticket_class " data-toggle="modal" data-target="#modal-reply-ticket-{{$ticket->id}}" id="{{$ticket->id}}" style="vertical-align: middle;">
                        <i class="ti-pencil icon-size pointer" data-toggle="tooltip" data-original-title="Aggiorna" style="vertical-align: middle;"></i>
                    </span> --}}
                    <span class="btn_delete_record pointer text-danger m-10 " data-url="{{route('tickets.destroy', $ticket->id )}}" data-delete-text="Sei sicuro di voler eliminare questo ticket?" data-delete-submit="main.yes_delete">
                       <i class="ti-trash text-default icon-size pointer" data-toggle="tooltip" title="" data-original-title="Elimina" style="vertical-align: middle;"></i>
                    </span>
                    @endif
                    <a href="{{ route('ticket-histories.index',['ticket' => base64_encode($ticket->id)]) }}" class="pointer " data-toggle="tooltip" style="vertical-align: middle;" title="Storico ticket">
                        <i class="ti-search text-default icon-size pointer" style="vertical-align: middle;"></i>
                    </a>

                </td>
                @php
                    $fieldsShow['messages'] = ['value' => view('ticket::partials.ticket-show', ['ticket' => $ticket ])->render() ];
                @endphp
                @include('partials.modal-model',[
                    'modalClass' => 'modal-subject-ticket-' . $ticket->id,
                    'title' => __('ticket::main.ticket') . " " . $ticket->subject,
                    'formOpen' => '',
                    'btnCancel'=>'',
                    'btnSubmit'=>'',
                    'fields' => $fieldsShow,
                    'modalSize' => 'md'
                ])
                @php
                    $formOpen = Form::model($ticket,array('url' => route('tickets.update',$ticket->id), 'method' => 'PUT','class'=>''));
                    $fieldsEdit = Modules\Ticket\Entities\Ticket::fieldsEditShow($ticket,$ticket_types);
                    $formOpenMessage = Form::open(array('url' => route('ticket-messages.store',['ticket_id'=>$ticket->id]), 'method' => 'POST','class'=>''));
                    $fieldsTicketMessage = Modules\Ticket\Entities\Ticket::fieldsTicketMessageShow($ticket,$ticket_types);
                    //$fieldsEdit['messages'] = ['value' => view('ticket::partials.chat', ['ticket' => $ticket ])->render() ];
                @endphp

                @php
                    // $getMessages = Modules\Ticket\Entities\TicketMessage::where('ticket_id', $ticket->id)->with('ticketMessageFiles')->get();
                    // $getMessages =  $ticket->ticket_messages;
                    $getMessages =  "";
                @endphp
                {{-- Ticket message --}}
                @include('ticket::partials.ticket-message-modal',[
                    'modal_id' => "modal-note-ticket-". $ticket->id,
                    'modalClass' => 'modal-note-ticket-' . $ticket->id,
                    'title' => "Note",
                    'formOpen' => $formOpenMessage,
                    'fields' => $fieldsTicketMessage,
                    //'btnCancel'=>'',
                    //'btnSubmit'=>'',
                    'modalSize' => 'md',
                    'getMessages' => $getMessages
                ])

                @include('partials.modal-model',[
                    'modal_id' => "modal-closed-ticket-". $ticket->id,
                    'modalClass' => 'modal-reply-ticket-' . $ticket->id,
                    'title' => "Desidera confermare la chiusura di questo Ticket?",
                    'formOpen' => $formOpen,
                    'fields' => ['status' => array('value' => Form::hidden('status',"closed"))],
                    'btnCancel'=>'<button type="button" class="btn btn-block btn-outline-danger waves-effect" data-dismiss="modal"><i class="i-Close-Window"></i> No</button>',
                    'btnSubmit'=>'<button type="submit" class="btn btn-block btn-primary btn-white"> <i class="i-Yes"></i> Si</button>',
                    'modalSize' => 'sm'
                ])
            </tr>
        @endforeach
        </tbody>
    </table>
    @foreach($tickets as $ticket)
        @php
            $formOpen = Form::model($ticket,array('url' => route('tickets.update',$ticket->id), 'method' => 'PUT','class'=>'save-ticket', 'enctype' => 'multipart/form-data'));
            $fieldsEdit = Modules\Ticket\Entities\Ticket::fieldsEditShow($ticket,$ticket_types);
            $formOpenMessage = Form::open(array('url' => route('ticket-messages.store',['ticket_id'=>$ticket->id]), 'method' => 'POST','class'=>''));
            $fieldsTicketMessage = Modules\Ticket\Entities\Ticket::fieldsTicketMessageShow($ticket,$ticket_types);
            //$fieldsEdit['messages'] = ['value' => view('ticket::partials.chat', ['ticket' => $ticket ])->render() ];
        @endphp
        {{-- Reply Ticket --}}
        @include('partials.modal-model',[
            'modal_id' => "modal-reply-ticket-". $ticket->id,
            'modalClass' => 'model_message_check modal-reply-ticket-' . $ticket->id,
            'title' => __('ticket::main.ticket') . " " . $ticket->subject,
            'formOpen' => $formOpen,
            'fields' => $fieldsEdit,
            //'btnCancel'=>'',
            //'btnSubmit'=>'',
            'modalSize' => 'lg'
        ])
    @endforeach
</div>