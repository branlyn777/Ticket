<p>Abbiamo ricevuto il seguente messaggio ticket :<br> <br>
    <strong>Ticket:</strong> ID: #{{ $ticket->id }}<br>
    <strong>Categoria:</strong> {{ $ticket->ticketType->name  }}<br>
    <strong>Autore:</strong> {{ $ticket->author_full_name  }}<br>
    <strong>Stato:</strong> {{ $ticket->getStatusName()  }}<br>
    <strong>Oggetto:</strong> {{ $ticket->subject  }}<br><br>
    <strong>Messaggio:</strong> {!! $ticket->text !!}<br>
</p>
<p>Per gestire questo ticket ti invitiamo ad accedere alla tua area riservata</p>