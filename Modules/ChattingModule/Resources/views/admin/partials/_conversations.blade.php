<div
    class="inbox_msg_header d-flex flex-wrap gap-3 justify-content-between align-items-center border px-3 py-2 rounded mb-4">
    <div class="media align-items-center gap-3">
        <div class="position-relative">
            <img class="avatar rounded-circle"
                 @if(isset($fromUser->user) && $fromUser->user->user_type == 'customer')
                     src="{{$fromUser->user->profile_image_full_path}}"
                 @elseif(isset($fromUser->user) && $fromUser->user->user_type == 'provider-admin')
                     src="{{$fromUser->user->provider->logo_full_path}}"
                 @elseif(isset($fromUser->user) && $fromUser->user->user_type == 'provider-serviceman')
                     src="{{onErrorImage(
                                $fromUser->user->profile_image,
                                asset('storage/app/public/serviceman/profile').'/' .$fromUser->user->profile_image,
                                asset('public/assets/admin-module/img/media/user.png') ,
                                'serviceman/profile/'
                                )}}"
                 @else
                     src="{{onErrorImage('null',
                        asset('storage/app/public/serviceman/profile').'/',
                        asset('public/assets/admin-module/img/media/user.png') ,
                        'serviceman/profile/')}}"
                 @endif
                 alt="{{ translate('profile_image') }}">
            <span class="avatar-status bg-success"></span>
        </div>
        <div class="media-body">
            @if(isset($fromUser->user) && isset($fromUser->user->provider))
                <h5 class="profile-name">{{ $fromUser->user->provider->company_name }}</h5>
                <span class="fz-12">{{$fromUser->user->provider->company_phone}}</span>
            @else
                <h5 class="profile-name">{{ isset($fromUser->user) ? $fromUser->user->first_name : translate('no_user_found') }}</h5>
                <span class="fz-12">{{isset($fromUser->user)?$fromUser->user->phone:''}}</span>
            @endif
        </div>
    </div>
</div>


<div class="messaging">
    <div class="inbox_msg d-flex flex-column-reverse" data-trigger="scrollbar">
        <div class="upload_img"></div>
        <div class="upload_file"></div>
        @php($format=['jpg','png','jpeg','JPG','PNG','JPEG'])
        @foreach($conversation as $chat)
            @if($chat->user->id==auth()->user()->id)
                <div class="outgoing_msg">
                    @if($chat->message!=null)
                        <p class="message_text">
                            {{$chat->message}}
                        </p>
                    @endif

                    @if(count($chat->conversationFiles)>0)
                        <div class="inbox-img-grid">
                            @foreach($chat->conversationFiles as $file)
                                @if(in_array($file->file_type,$format))
                                    <div class="conv-img-wrap">
                                        <a data-lightbox="mygallery"
                                           href="{{$file->stored_file_name_full_path}}">
                                        <img width="150"
                                             src="{{$file->stored_file_name_full_path}}">
                                        </a>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center flex-column gap-1">
                                        <img width="50" src="{{asset('public/assets/admin-module/img/icons/folder.png')}}" alt="">
                                        <a class="fs-12" href="{{$file->stored_file_name_full_path}}" download>
                                            {{$file->original_file_name}}
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <span class="time_date d-flex justify-content-end">
                        {{date('H:i a | M d',strtotime($chat->created_at))}}
                    </span>
                </div>
            @else
                <div class="received_msg">
                    @if($chat->message!=null)
                        <p class="message_text">
                            {{$chat->message}}
                        </p>
                    @endif

                    @if(count($chat->conversationFiles)>0)
                        @foreach($chat->conversationFiles as $file)
                                @if(in_array($file->file_type,$format))
                                <img width="150"
                                     src="{{$file->stored_file_name_full_path}}" alt="{{translate('image')}}">
                            @else
                                <a href="{{$file->stored_file_name_full_path}}"
                                   download>{{$file->original_file_name}}</a>
                            @endif
                        @endforeach
                    @endif
                    <span class="time_date"> {{date('H:i a | M d',strtotime($chat->created_at))}}</span>
                </div>
            @endif
        @endforeach

    </div>

    <div class="type_msg">
        <form class="mt-4" id="send-sms-form">
            <div class="input_msg_write border rounded p-3">
                <input name="channel_id" class="hide-div" value="{{$channelId}}"
                       id="chat-channel-id">
                <textarea class="border-0 w-100 resize-none pb-0" id="msgInputValue" type="text"
                          placeholder="{{translate('type_here...')}}"
                          aria-label="Search" name="message"></textarea>


                <div class="d-flex justify-content-between gap-3">
                    <div class="">
                        <div class="d-flex gap-3 flex-wrap filearray"></div>
                        <div id="selected-files-container"></div>
                    </div>
                    <div class="send-msg-btns d-flex justify-content-end mt-3 gap-3">
                        <div class="position-relative">
                            <label class="cursor-pointer">
                                <img src="{{asset('public/assets/admin-module/img/icons/img-icon.svg')}}" alt="">
                                <input type="file" id="msgfilesValue" class="h-100 position-absolute w-100 " hidden multiple
                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                            </label>
                        </div>
                        <div class="add-attatchment">
                            <img src="{{asset('public/assets/admin-module/img/icons/clip-icon.svg')}}" alt="">
                            <input type="file" class="file_input document_input" name="files[]" multiple>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="p-0 lh-1" type="button" id="btnSendData">
                                <span class="material-icons">send</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    if (typeof selectedFiles === 'undefined') {
        var selectedFiles = [];
    }
    $("#msgfilesValue").on('change', function () {
        for (let i = 0; i < this.files.length; ++i) {
            selectedFiles.push(this.files[i]);
        }
        console.log('hi')
        displaySelectedFiles();
    });

    function displaySelectedFiles() {
        /*start*/
        const container = document.getElementById("selected-files-container");
        container.innerHTML = ""; // Clear previous content
        selectedFiles.forEach((file, index) => {
            const input = document.createElement("input");
            input.type = "file";
            input.name = `files[${index}]`;
            input.classList.add(`image_index${index}`);
            input.hidden = true;
            container.appendChild(input);

            const blob = new Blob([file], {type: file.type});
            const file_obj = new File([file], file.name);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file_obj);
            input.files = dataTransfer.files;
        });

        $(".filearray").empty();
        for (let i = 0; i < selectedFiles.length; ++i) {
            let filereader = new FileReader();
            let $uploadDiv = jQuery.parseHTML("<div class='upload_img_box'><span class='img-clear'><span class='material-icons m-0 fs-10'>close</span></span><img src='' alt=''></div>");

            filereader.onload = function () {
                // Set the src attribute of the img tag within the created div
                $($uploadDiv).find('img').attr('src', this.result);
                let imageData = this.result;
            };

            filereader.readAsDataURL(selectedFiles[i]);
            $(".filearray").append($uploadDiv);
            // Attach a click event handler to the "tio-clear" icon to remove the associated div and file from the array
            $($uploadDiv).find('.img-clear').on('click', function () {
                $(this).closest('.upload_img_box').remove();

                selectedFiles.splice(i, 1);
                $('.image_index' + i).remove();
            });
        }
    }
</script>

<script>
    "use strict";

    $('#btnSendData').on('click', function () {
        var form = $('#send-sms-form')[0];
        var formData = new FormData(form);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.chat.send-message')}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                $('.inbox_msg').html(response.template);
                $(".file_input").val("");
                $("#send-sms-form")[0].reset();
                $('.upload__img-wrap').html('')
                $(".filearray").empty();
                selectedFiles = [];
                toastr.success("{{translate('Message sent successfully')}}", {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            error: function (jqXHR, exception) {
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    toastr.error(jqXHR.responseJSON.errors[0]['message']);
                } else {
                    toastr.error("An unexpected error occurred.");
                }
            }
        });
    });

    $(".type_msg .document_input").on("change", function (e) {
        var filename = $(e.target).val().split('\\').pop();
        $(".messaging .upload_file").html("<div class='d-flex justify-content-between gap-2 align-items-center show-upload-file'><span class=''>" + filename + "</span><span class='material-icons upload-file-close'>close</span></div>");
        $(".messaging .inbox_msg").scrollTop(0);
        $('.upload-file-close').on('click', function () {
            $(this).parents('.show-upload-file').remove();
            $(".type_msg .document_input").val(null);
        });
    });

</script>
