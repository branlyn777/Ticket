
@foreach($ticket->ticket_messages as $message)
    @include('ticket::partials.chat-item',['message'=>$message])
@endforeach
