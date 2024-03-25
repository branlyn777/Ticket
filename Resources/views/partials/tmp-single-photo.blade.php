<div class="col-md-2 mt-5">
    <a href="{{asset("storage/module-ticket/tmp/$filename")}}" target="_blank" download>
        @php
            $checkImage = \App\Helpers\FileHelper::isImage(storage_path('app/public/module-ticket/tmp/'.$filename));
        @endphp
            @if($checkImage)
                <img alt="{{$filename}}" src="{{asset("storage/module-ticket/tmp/$filename")}}" width="100%">
            @else
                <i class="mt-5 preview ti-file" style="font-size: 99px;" width="100%"></i>
            @endif
        <input type="hidden" name="files[]" value="{{$filename}}">
    </a>
    <button onclick="$(this).closest('div').remove()" class="link_style btn btn-danger btn-sm btn-block" type="button" style="padding: 3px">
        Elimina <i class="fa fa-trash"></i>
    </button>
</div>