@php
    $user = \App\User::where('email', $message['author_email'])->first();
    $userAvatar = $user->getAvatar();
    // if ($message['is_scrivania']) {
        // $alignOfTimeline = (Auth::user()->email == $user->email) ? true : false;
        $userEmail = $user ? $user->email : null;
        $alignOfTimeline = Auth::user()->email == $userEmail;
    // } else {
    //     $alignOfTimeline = false;
    // }
@endphp
<ul class="timeline clearfix" @if(!$alignOfTimeline) style="display: flex;" @endif>
    <li class="timeline-line"></li>
    <div class="clearfix"></div>
    <li class="timeline-item timeline-right mt-4" @if($alignOfTimeline) style="padding-left: 3rem;" @else style="padding-right: 3rem;" @endif>
        <div class="timeline-badge" @if(!$alignOfTimeline) style="left: calc(100% - 18px);" @endif>
            <img class="badge-img" src="{{ $userAvatar }}" style="background: white;border: 2px solid #efeeee;">
        </div>
        <div class="timeline-card card">
            <div class="card-body">
                <div class="row mb-1">
                    <div class="col-4 pr-0">
                        <span class="mr-1" style="font-family: Sans-Serif;">
                            {{ \Carbon\Carbon::parse($message['created_at'])->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="col-7" style="text-align: right;">
                        <span>{{ $message['author_full_name'] }}</span>
                    </div>
                    <div class="col-1 pl-0" style="text-align: right;">
                        <span class="btn_delete_record pointer " data-url="{{route('ticket-messages.destroy', $message['id'] )}}" data-delete-text="Sei sicuro di voler eliminare questo ticket message?" data-delete-submit="main.yes_delete">
                            <i class="ti-trash text-danger icon-size pointer" data-toggle="tooltip" title="Elimina"></i>
                        </span>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-9">
                        <strong>{{ $message['message'] }}</strong>
                    </div>
                    <div class="col-3" style="max-width: 22%;text-align: center;">
                        @if(isset($message['ticket_message_files']) && count($message['ticket_message_files'])>0)
                            @foreach($message['ticket_message_files'] as $file)
                                <a href="{{ $file['file_path']['path'] }}" download target="_blank">
                                    @if($file['file_path']['image'])
                                        <img src="{{ $file['file_path']['path'] }}" alt="" style="width:45px">
                                    @else
                                        <i class="ti-file"></i>
                                    @endif
                                </a>
                            @endforeach
                        @endif  
                    </div>
                </div>
            </div>
        </div>
    </li>
</ul>