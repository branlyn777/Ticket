{{-- not required as of now --}}
<style type="text/css">
    /*.timeline .timeline-item:nth-child(odd) {
        padding: 0;
    }*/
    .card-body {
        padding: 0.5rem;
    }
    .timeline .timeline-item .timeline-badge {
        width: 36px;
        height: 36px;
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
    input[name="file"] {
        opacity: 0;
        position: absolute;
        top: 80%;
        left: 0;
        height: 20%;
        width: 5%;
        cursor: pointer;
    }
    .custom-icon {
        cursor: pointer;
        font-size: 20px;
        color: #6d6e6f;
        position: absolute;
/*        top: 80%;*/
        left: 0px;
        transform: rotate(45deg);
    }
    .message-submit-button {
        position: absolute;
        right: 0px;
    }
    @media screen and (min-width: 1025px){
        .modal-dialog {
             max-width: 50% !important;
            margin: 1.75rem auto;
        }
    }
    .message-width {
        position: relative;
        flex-grow: 1;
        width: 100%;
    }
    #message {
        background-image: none !important;
        border: 1px solid #ced4da !important;
        background: #fff;
        border-radius: 10px;
        padding-top: 10px !important;
        padding-left: 10px !important;
    }
</style>
<div class="modal fade scrollbox {{ isset($modalClass) ? $modalClass : '' }}" id="{{ isset($modal_id) ? $modal_id : '' }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" onclick="removeParams('{{$modal_id}}')">
    <div class="modal-dialog modal-{{ isset($modalSize) ? $modalSize : 'md'}}" role="document">
        {{ $formOpen }}
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ isset($title) ? $title : '' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{----}}
            <div class="modal-body ">
                {{-- <div class="col-md-12 mb-4 hide-message-container">
                    @foreach($getMessages as $getMessage)
                    @php
                        $user = \App\User::where('email', $getMessage->author_email)->first();
                        $userAvatar = $user ? $user->getAvatar() : asset('/assets/images/default_avatar.png');
                        // if ($getMessage->is_scrivania) {
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
                                                {{ \Carbon\Carbon::parse($getMessage->created_at)->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        <div class="col-7">
                                            <span>{{ $getMessage->author_full_name }}</span>
                                        </div>
                                        <div class="col-1 pl-0" style="text-align: right;">
                                            <span class="btn_delete_record pointer " data-url="{{route('ticket-messages.destroy', $getMessage->id )}}" data-delete-text="Sei sicuro di voler eliminare questo ticket message?" data-delete-submit="main.yes_delete">
                                                <i class="ti-trash text-danger icon-size pointer" data-toggle="tooltip" title="Elimina"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-9">
                                            <strong>{{ $getMessage->message }}</strong>
                                        </div>
                                        <div class="col-3" style="max-width: 22%;text-align: center;">
                                            @if(isset($getMessage->ticketMessageFiles) && count($getMessage->ticketMessageFiles)>0)
                                                @foreach($getMessage->ticketMessageFiles as $file)
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
                    @endforeach
                </div> --}}
                <div class="row">
                    <div class="d-flex message-area col-12">
                        <div class="pt-4">
                            @php
                                $authUser = auth()->user();
                                $avatar = $authUser ? $authUser->getAvatar() : '';
                                $operator_fullname = $authUser ? $authUser->getFullName() : '';
                                $userShortName= strtoupper(substr($authUser->name, 0, 1).substr($authUser->lastname, 0, 1));
                                $setAvatar = '';

                                if (empty($avatar) || strpos($avatar, 'default_avatar.png') !== false) {
                                    $setAvatar = "<a href='javascript:void(0)' data-letters='" . $userShortName . "' title='" . $operator_fullname . "' data-toggle='tooltip' draggable='false'></a>";
                                } else {
                                    $setAvatar = "<img src='{$avatar}' alt='{$operator_fullname}' class='operator-data-letters'>";
                                }
                            @endphp
                            {!! $setAvatar !!}
                        </div>
                        <div class="message-width">
                            <span class="text-danger message-error"></span><br>
                            <span class="text-danger file-error"></span>
                            {{ $fields['message']['value'] }}
                            <div class="pb-4 pt-2">
                                <i class="fa fa-paperclip custom-icon" aria-hidden="true"></i>
                                {{ $fields['file']['value'] }}
                                <button type="submit" class="btn btn-primary message-submit-button">Commenta</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 file-preview-container mt-4" style="margin-left: 3rem;">
                        <img class="file-preview" alt="Message Image" style="max-height: 100px;max-width: 150px;">
                        <br>
                        <span class="file-name"></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Close -->
        {{ isset($formClose) ? $formClose : Form::close() }}
    </div>
</div>