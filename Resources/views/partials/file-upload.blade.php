<form method="POST" enctype="multipart/form-data" id="temporary-img-form" class="form-ajax" action="{{route('tickets.save-tmp-file')}}">
@csrf
<input id="file-1" type="file" name="files" style="display: none;" class="file" required>
</form>