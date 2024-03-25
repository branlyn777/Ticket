<div class="col-md-12"> 
    <input type="hidden" id="ticket_path" value="{{ route('tickets.show',$ticket->id) }}">
    <div data-sidebar-container="chat" class="card chat-sidebar-container">
        <div data-sidebar-content="chat" class="chat-content-wrap">
            <div class="chat-content perfect-scrollbar" data-suppress-scroll-x="true">
                @include('ticket::partials.chat-content',['ticket'=>$ticket])
            </div>
            <div id="send_message">
                <div class="pl-3 pr-3 pt-3 pb-3 box-shadow-1 chat-input-area">
                    <div class="form-group">
                        <textarea style="padding-top: 14px !important;padding-left: 17px !important;" class="form-control form-control-rounded" placeholder="Type your message" name="message" id="message" cols="30" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="_token" id="csrf_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="ticket_id" id="ticket_id" value="{{ $ticket->id }}">
                    <input type="file" class="message_file" id="message_file_{{ $ticket->id }}" hidden/>
                    <div class="d-flex">
                        <div class="flex-grow-1"><span id="file-chosen">No file chosen</span></div>
                        <button type="button" class="btn btn-icon btn-rounded btn-primary mr-2 save_message">
                            <i class="i-Paper-Plane"></i>
                        </button>
                        <label for="message_file_{{ $ticket->id }}" style="width: 30px;color: black;font-family: sans-serif;border-radius: 0.3rem;cursor: pointer;font-size: 25px;padding-top: 7px;">
                            <i class="fa fa-paperclip" style="font-size:25px"></i>
                        </label>
                    </div>
                </div>  
            </div>
        </div>
    </div>
</div>