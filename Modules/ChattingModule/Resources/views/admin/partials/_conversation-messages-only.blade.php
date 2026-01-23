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
                                <a data-lightbox="mygallery" href="{{$file->stored_file_name_full_path}}">
                                    <img width="150" src="{{$file->stored_file_name_full_path}}" alt="{{translate('image')}}">
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
                        <a data-lightbox="mygallery"
                           href="{{$file->stored_file_name_full_path}}">
                            <img width="150" src="{{$file->stored_file_name_full_path}}"></a>
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
