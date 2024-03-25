<div class="card-body">
	<div class="col-md-12">
	    <div class="form-group">
	        <label>Oggetto</label>
	        <br><br>
	        {{ $ticket->subject }}
	    </div>
	    <div class="form-group">
	        <label>Messaggio</label>
	        <br><br>
	        {!! $ticket->text !!}
	    </div>
	</div>
</div>