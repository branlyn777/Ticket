<script>
    $('.save_message').click(function(){
        var currentObj = $(this).closest('#send_message');
        var currentObjMain = $(this).closest('.modal-body');
        var message = currentObj.find('#message').val();
        var csrf_token = currentObj.find('#csrf_token').val();
        var ticket_id = currentObj.find('#ticket_id').val();
        var message_file = currentObj.find('.message_file');
        console.log(message_file)
        var myFormData = new FormData();
        myFormData.append('file', message_file[0].files[0]);
        myFormData.append('message',message);
        myFormData.append('_token',csrf_token);
        myFormData.append('ticket_id',ticket_id);
        console.log(myFormData)
        if (message == "") {
            vanillaAlert('Il campo del messaggio è obbligatorio','error');
        }

        $.ajax({
            url: "{{ route('ticket-messages.store') }}",
            cache: false,
            contentType: false,
            processData: false,
            data: myFormData, // Setting the data attribute of ajax with file_data
            type: 'post',
            success: function(data) {
                currentObj.find('#message').val("")
                currentObj.find('.message_file').val("")
                currentObj.find('#file-chosen').text("No file chosen");

                currentObjMain.find('.chat-content').append(data.chat_item_html);
                currentObjMain.find('.chat-content').scrollTop(currentObjMain.find('.chat-content')[0].scrollHeight);
            }
        });
        return false;
    });

    $(document).on('change','.message_file', function() {
        var currentObj = $(this).closest('#send_message');
        var filename = $(this)[0]['files'][0].name;
        currentObj.find('#file-chosen').text(filename);
    });

    $(document).on('click','.message_class', function() {
        var currentClass = $(this).attr('data-target');
        setTimeout(function () {
            $('body').find(currentClass).find('.chat-content').scrollTop($('body').find(currentClass).find('.chat-content')[0].scrollHeight);
        },500);
    });
    @if(\App\Helpers\HostHelper::isScrivania())
    setInterval(function () {
        var check_model = $('body').find('.model_message_check.show');
        var ticket_path = check_model.find('#ticket_path').val();
        if (ticket_path != undefined) {
            $.ajax({
                url: ticket_path,
                cache: false,
                contentType: false,
                processData: false,
                type: 'get',
                success: function(data) {
                    check_model.find('.chat-content').html(data.html);
                    check_model.find('.chat-content').scrollTop(check_model.find('.chat-content')[0].scrollHeight);
                }
            });
            return false;
        }
    }, 30000);
    @endif

    /*$('.ajax-save').change(function(){
        $(this).closest('.modal-dialog').find('form').submit();
    })*/
</script>
{{-- Upload photos --}}
<script>
    let addImageButton = document.getElementById('add-image');
    let imputImg = document.getElementById('file-1');
    //Open input file-1
    addImageButton.addEventListener('click', () => {
        imputImg.click();
    });
    //Show photo on screen
    function addPhotoUploaded(singleHtmlPhoto){
        $("#list-tmp-images").append(singleHtmlPhoto);
        imputImg.nodeValue = undefined;
    }
    //create a temporary photo
    imputImg.addEventListener('change', () => {
        $('#temporary-img-form').submit()
    });
</script>
<script>
    function closeModalTicket(ticket){

        $("."+ticket).modal('toggle');
    }
</script>

{{--
    **************************************
    ********* EDITOR DESCRIPTION *********
    **************************************
--}}

<script src="{{ asset('assets/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
{{-- tinymce editor --}}
<script type="text/javascript">
    function openTinyMce(target){
        tinymce.remove();
        setTimeout(function() {
            tinymce.init({
                selector: target,
                // target:target,
                height: 300,
                width: "100%",
                init_instance_callback: function (editor) {
                    var links = editor.dom.select('a');

                    links.forEach(function (link) {
                        var text = link.innerText.trim();
                        link.setAttribute('href', text);
                    });
                },
                menubar: false,
                content_style: 'img {max-width: 600px;max-height: 338px;}',
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks fullscreen',
                    'insertdatetime media table paste', 'image imagetools'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | link image | imagetools | help',
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                },
                imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage',
                statusbar: false,
                automatic_uploads: true,
                paste_data_images: true,
                file_picker_types: 'image',
                images_upload_url: '{{ route("ticket-text-image-upload") }}',
                file_picker_callback: function (cb, value, meta) {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.onchange = function () {
                        var file = this.files[0];

                        var reader = new FileReader();
                        reader.onload = function () {
                            var id = 'blobid' + (new Date()).getTime();
                            var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                            var base64 = reader.result.split(',')[1];
                            var blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);

                            cb(blobInfo.blobUri(), { title: file.name });
                        };
                        reader.readAsDataURL(file);
                    };

                    input.click();
                },

                images_upload_handler: function (blobInfo, success, failure) {
                    var xhr, formData;

                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '{{ route("ticket-text-image-upload") }}');

                    xhr.setRequestHeader('X-CSRF-Token', '{{ csrf_token() }}');

                    xhr.onload = function () {
                        var json;

                        if (xhr.status !== 200) {
                            failure('HTTP Error: ' + xhr.status);
                            return;
                        }

                        json = JSON.parse(xhr.responseText);

                        if (!json || typeof json.location !== 'string') {
                            failure('Invalid JSON: ' + xhr.responseText);
                            return;
                        }

                        success(json.location);
                    };

                    formData = new FormData();

                    /*var img = new Image();
                    img.src = URL.createObjectURL(blobInfo.blob());

                    img.onload = function () {
                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');

                        canvas.width = 400;
                        canvas.height = 400;

                        ctx.drawImage(img, 0, 0, 400, 400);

                        canvas.toBlob(function (resizedBlob) {
                            formData.append('file', resizedBlob, blobInfo.filename());*/
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                        /*});
                    };*/
                }
            });
        }, 50);
    }
</script>
<script>
    $(document).ready(function () {
        $('body').on('click', '.edit_ticket_class', function() {

            var modalId = $(this).attr('id');

            var editTaskModalId = '#modal-reply-ticket-' + modalId;

            // ajax edit modal
            openEditModal(modalId);

            $(editTaskModalId).on('show.bs.modal', function (event) {
                openTinyMce('.description_'+modalId);
            });
        });
    });

    $('#modal-ticket-create').on('show.bs.modal', function (event) {
        openTinyMce('.description_');
    })

    $('body').on('click', '.comment_class', function() {
        openTinyMce('.message-tiny-editor');
    });
</script>

{{-- function for ajax modal call --}}
<script>
    function openEditModal(modalId) {
        $.ajax({
            url: "",
            type: 'GET',
            data: {modalId: modalId},
            success: function (response) {
                $('body').append(response.modalContent);

                $('#modal-reply-ticket-' + modalId).modal('show');

                openTinyMce('.description_' + modalId);
                
                var typeOperationValue = $('#modal-reply-ticket-' + modalId + ' select[name="type_operation"]').val();
                // if (typeOperationValue === 'close_without_result') {
                //     $('#modal-reply-ticket-' + modalId + ' .operation-note').show();
                // } else {
                //     $('#modal-reply-ticket-' + modalId + ' .operation-note').hide();
                // }
                if (typeOperationValue != '') {
                    $('#modal-reply-ticket-' + modalId + ' .operation-note').show();
                }else{
                    $('#modal-reply-ticket-' + modalId + ' .operation-note').hide();
                }
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
</script>
{{-- toggle required attribute of Tipo intervento when status change --}}
<script>
    $(document).on('change', 'select[name^="status"]', function() {
        var selectedValue = $(this).val();
        var modalDialog = $(this).closest('.modal-dialog');
        var typeIntervation = modalDialog.find('label[for="operation_note"]');
        var typeOperation = modalDialog.find('.type-operation');
        typeOperationInput = modalDialog.find('#type_operation');

        if (selectedValue === 'closed') {
            typeIntervation.addClass('required');
            typeOperation.removeClass('d-none');
            typeOperationInput.prop('required', true);
            $(".operation-note").show();
        } else {
            typeOperation.addClass('d-none');
            typeOperationInput.prop('required', false);
        }
    });
</script>
{{-- update date when click on bell icon in ticket --}}
<script>
    function updateTicket(element, ticketId) {
        var hideIcon = $(element);
        $.ajax({
            type: 'POST',
            url: '{{ route('update.ticket', ['ticket' => 'id']) }}'.replace('id', ticketId),
            data: {
                _token: '{{ csrf_token() }}',
            },
            success: function(response) {
                if (response.success == true) {
                    hideIcon.hide();
                }
            },
        });
    }
</script>

{{-- open edit ticket modal from email link --}}
<script>
    $(window).on('load', function() {
        var urlParams = new URLSearchParams(window.location.search);

        var ticketId = urlParams.get('ticket_id');
        if (ticketId) {
            modalId = atob(ticketId);
            openEditModal(modalId);
        }
    });
</script>
{{-- Note intervento show/hide on change of Tipo intervento --}}
{{-- <script>
    $(document).ready(function () {
        // $(".operation-note").hide();
        $('body').on('change', '#type_operation', function() {
            var typeOperation = $(this).val();
            console.log(typeOperation)

            if (typeOperation == 'close_without_result') {
                $(".operation-note").show();
            }else{
                $(".operation-note").hide();
            }
        });
    });
</script> --}}
{{-- ticket add message --}}
<script>
    $('.file-preview-container').hide();
    // $('body').on('click', '.message-submit-button', function() {
    //     var modalDialog = $(this).closest('.modal-dialog');
    //     var form = modalDialog.find('form');
    //     var messageError = form.find('.message-error');
    //     var fileError = form.find('.file-error');

    //     var formData = new FormData(form[0]);
    //     var csrfToken = $('meta[name="csrf-token"]').attr('content');

    //     $.ajax({
    //         type: form.attr('method'),
    //         url: form.attr('action'),
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         headers: {
    //             'X-CSRF-TOKEN': csrfToken
    //         },
    //         success: function(response) {
    //             var messageContainer = modalDialog.find('.hide-message-container');
    //             messageContainer.append(response.chat_item_html);
                
    //             modalDialog.find('.message-error').text('');
    //             modalDialog.find('.file-error').text('');
    //             modalDialog.find('.file-preview-container').css('display', 'none');
    //             modalDialog.find('.file-preview').attr('src','#');
    //             modalDialog.find('#message').val('');
    //             modalDialog.find('[name="file"]').val('');

    //             vanillaAlert(response.message, 'success');
    //             modalDialog.closest('.modal').modal('hide');
    //         },
    //         error: function(xhr) {
    //             if (xhr.status === 422) {
    //                 var errors = xhr.responseJSON.errors;
    //                 $.each(errors, function(key, value) {
    //                     modalDialog.find('.'+key+'-error').text(value);
    //                 });
    //             } else {
    //                 console.log('Something went wrong!');
    //             }
    //         }
    //     });
    // });
</script>
{{-- ticket message preview image --}}
<script>
    $(document).ready(function() {
        function readURL(input, preview, fileNameContainer) {
            if (input.files && input.files[0] && ((input.files[0].type == 'image/png') || (input.files[0].type == 'image/jpg') || (input.files[0].type == 'image/jpeg'))) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(preview).attr('src', e.target.result);
                    $(fileNameContainer).text(input.files[0].name);
                    $(preview).parent().show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('body').on('change', "input[name='file']", function() {
            var modalDialog = $(this).closest('.modal-dialog');
            var messageContainer = modalDialog.find('.hide-message-container');
            var filePreview = modalDialog.find('.file-preview');
            var fileNameContainer = modalDialog.find('.file-name');

            readURL(this, filePreview, fileNameContainer);
        });
    });
</script>
{{-- delete ticket message --}}
<script>
    var $getClosetrow;

    $(document).ready(function () {
        $('body').on('click', '.btn-delete-message', function() {
            var $this = $(this);
            $this.toggleClass('clicked');
            var iconContainer = $this.find(".icon-container").toggle();
            
            if ($this.hasClass('clicked')) {
                $this.css({
                    'background': 'rgba(0, 0, 0, 0.03)',
                    // 'opacity': '0.5',
                });
            } else {
                $this.css({
                    'background': '',
                });
            }
        });

        $(document).on('click', '.btn-delete-message .click-ti-check', function () {
            // var $getClosetDiv = $(this).closest('.col-md-2');
            var deleteUrl = $(this).closest('.btn-delete-message').data('url');

            $.ajax({
                type: 'DELETE',
                url: deleteUrl,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success == true) {
                        // vanillaAlert(response.message, 'success');
                        $getClosetrow.remove();
                    }
                },
                error: function (error) {
                    vanillaAlert('Something went wrong!', 'error');
                }
            });
        });
    });
</script>
{{-- open ticket message modal after delete message --}}
{{-- <script>
    $(document).ready(function() {
        $(".comment_class").on("click", function() {
            var ticketId = $(this).data("id");
            var currentUrl = window.location.href.split('?')[0];
            var separator = currentUrl.indexOf('?') !== -1 ? '&' : '?';
            var newUrl = currentUrl + separator + 'message_id=' + ticketId;
            history.pushState(null, null, newUrl);
        });
    });
</script> --}}
<script>
    $(window).on('load', function() {
        var urlParams = new URLSearchParams(window.location.search);

        var messageId = urlParams.get('message_id');
        if (messageId) {
            modalId = atob(messageId);
            $("#modal-note-ticket-"+modalId).modal('show');
        }
    });
</script>
{{-- remove parameters click outside modal --}}
<script>
    function removeParams(modalId) {
        var url = new URL(window.location.href);
        var ticketId = modalId.replace(/\D/g, '');
        var modalDialog = $('.modal-dialog');
        if ($(event.target).closest(modalDialog).length > 0) {

        } else {
            url.searchParams.delete('message_id');
            history.pushState(null, null, url.toString());
        }
    };
</script>
<script>
    function displayAttachmentName(input) {
        var fileNameDisplay = $(input).closest('.attachment-div');
        fileNameDisplay.find('.file-name').text(input.files[0] ? input.files[0].name : '');
    }
</script>
{{-- START add note files in ticket history --}}
<script>
$('.add_file_btn').on('click', function () {
    var filesSection = $('#'+$(this).attr('data-file-section-id'));
    if (filesSection.is(':visible')) {
        filesSection.hide();
        filesSection.find("input").val("");
        filesSection.find('input').attr('required',false);
    } else {
        filesSection.show();
        filesSection.find('input').attr('required',true);
    }
});

$('.add-file-to-list').on('click', function () {
    var fileList = $('#'+$(this).attr('data-files-list'));
    var fileColumn = fileList.find('.file-item').first();
    fileColumn = fileColumn.clone();
    fileColumn.find('.remove-file').show();
    fileColumn.find('input').val("");
    fileColumn.attr('required',true);
    fileList.append(fileColumn);
});

$(document).delegate(".remove-file", "click", function() {
    $(this).closest('.file-item').remove();
});
</script>
{{-- END add note files in ticket history --}}
{{-- save ticket ajax --}}
<script>
    $('.save-ticket').submit(function(event) {
        event.preventDefault();
        
        $('#preloader').css({
            'display': 'block',
            'z-index': '1100'
        });

        var formData = new FormData($(this)[0]);

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.vanillaStatus == 'success') {
                    $('.save-ticket').find('button[type="submit"]').prop('disabled', true);
                    // vanillaAlert(response.vanillaTitle, 'success');
                    location.reload();
                }else{
                    vanillaAlert(response.vanillaTitle, 'error');
                }
                $('#preloader').css('display', 'none');
            },
            error: function(xhr, status, error) {
                $('#preloader').css('display', 'none');
                vanillaAlert(xhr.responseJSON.message, 'error');
            }
        });
    });
</script>