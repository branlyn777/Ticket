<div class="table-responsive">
    <div class="table">
        <table class="table text-center">
            <thead>
                <tr>
                    <th style="width: 40%;">{{ __('managementfile::main.description') }}</th>
                    <th>{{ 'Data inserimento' }}</th>
                    <th>{{ __('main.file') }}</th>
                    <th>{{ __('managementfile::main.action') }}</th>
                </tr>
            </thead>
            <tbody>
                {{-- @if($ticket->ticketAttachments) --}}
                @php
                    $checkScrivania = \App\Helpers\HostHelper::isScrivania();
                    if ($checkScrivania) {
                        $ticket_attachments = $ticket->ticketAttachments;
                    }else{
                        $ticket_attachments = $ticket->ticket_attachments;
                    }
                @endphp
                @if($ticket_attachments != null)
                @foreach($ticket_attachments as $ticketAttachment)
                <tr>
                    <td>{{ $ticketAttachment->description ? $ticketAttachment->description : pathinfo($ticketAttachment->attachment, PATHINFO_FILENAME) }}</td>
                    <td>
                        <div>{{   \Carbon\Carbon::parse($ticketAttachment->created_at)->format('d-m-Y H:i') }}</div>
                        @if($checkScrivania)
                        <div>{{ $ticketAttachment->author ? $ticketAttachment->author->getFullName() : '-' }}</div>
                        @endif
                    </td>
                    <td>{{ pathinfo($ticketAttachment->attachment, PATHINFO_EXTENSION) }}</td>
                    <td>
                        @if(\App\Helpers\HostHelper::isScrivania())
                        <a href="{{ route('ticketAttachmentDownload',['id' => $ticketAttachment->id]) }}"><i class="icon-size ti-download text-info mr-2" data-toggle="tooltip" data-title={{ __('main.download') }}></i></a>
                        @else
                        <a href="{{ config('services.tickets.download_url'). 'module/ticket-attachment/download/'.$ticketAttachment->id }}"><i class="icon-size ti-download text-info mr-2" data-toggle="tooltip" data-title={{ __('main.download') }}></i></a>
                        @endif
                        <a data-url="{{ route('ticket-attachments.destroy', $ticketAttachment->id ) }}" class="btn_delete_record pointer"><i class="fa fa-trash text-danger icon-size" data-toggle="tooltip" data-title={{ __('main.delete') }}></i></a>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>