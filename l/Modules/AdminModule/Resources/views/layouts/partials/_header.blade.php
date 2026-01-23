<header class="header fixed-top">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-between">
            <div class="col-2">
                <div class="header-toogle-menu">
                    <button class="toggle-menu-button aside-toggle border-0 bg-transparent p-0 dark-color">
                        <span class="material-icons">menu</span>
                    </button>
                </div>
            </div>
            <div class="col-10">
                <div class="header-right">
                    <ul class="nav justify-content-end align-items-center gap-3 gap-md-4">
                        <li class="nav-item max-sm-m-0">
                            <button type="button" id="modalOpener" class="title-color bg--secondary border-0 rounded align-items-center py-2 px-2 px-md-3 d-flex gap-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                <span class="material-symbols-outlined">search</span>
                                <span class="d-none d-md-block">{{translate('Search')}}</span>
                                <span class="bg-card text-muted border rounded-3 p-1 fs-12 fw-bold lh-1 ms-1 ctrlplusk d-none d-md-block">Ctrl+K</span>
                            </button>
                        </li>
                        <li class="nav-item max-sm-m-0">
                            <div class="hs-unfold">
                                <div>
                                    @php($local = session()->has('local') ? session('local'):null)
                                    @php($lang = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                    @if ($lang)
                                        <div class="topbar-text dropdown d-flex">
                                            <a class="topbar-link dropdown-toggle d-flex align-items-center title-color gap-1 justify-content-between lagn-drop-btn"
                                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,20">
                                                @foreach ($lang['live_values'] as $data)
                                                    @if(is_null($local) && $data['default'])
                                                        @php($local = $data['code'])
                                                    @endif

                                                    @if($data['code']==$local)
                                                        @php($language = collect(LANGUAGES)->where('code', $data['code'])->first())
                                                        <span class="material-icons">language</span>
                                                        @if($language)
                                                            <span class="d-none d-md-block">{{ $language['nativeName'] }}</span>
                                                            <span class="fz-10 d-none d-md-block">({{ $data['code'] }})</span>
                                                        @else
                                                            <span class="d-none d-md-block">({{ $data['code'] }})</span>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </a>
                                            <ul class="dropdown-menu lang-menu">
                                                @foreach($lang['live_values'] as $key =>$data)
                                                    @if($data['status']==1)
                                                        @php($language = collect(LANGUAGES)->where('code', $data['code'])->first())
                                                        <li>
                                                            <a class="dropdown-item d-flex gap-2 align-items-center py-2 justify-content-between"
                                                               href="{{route('admin.lang',[$data['code']])}}">
                                                                @if($language)
                                                                    <div class="d-flex gap-2 align-items-center">
                                                                        <span class="text-capitalize">{{ $language['nativeName'] }}</span>
                                                                        <span class="fz-10">({{ $data['code'] }})</span>
                                                                    </div>
                                                                    @if($local == $data['code'])
                                                                        <span class="material-symbols-outlined text-muted">check_circle</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-capitalize">{{ $data['code'] }}</span>
                                                                @endif

                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="messages pe--12">
                                <a href="{{route('admin.chat.index', ['user_type' => 'customer'])}}" class="header-icon count-btn">
                                    <span class="material-icons">sms</span>
                                    <span class="count" id="message_count">0</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="user mt-n1">
                                <a href="#" class="header-icon user-icon" data-bs-toggle="dropdown">
                                    <img width="30" height="30"
                                         src="{{auth()->user()->profile_image_full_path}}"

                                         class="rounded-circle" alt="{{ translate('profile_image') }}">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="{{route('admin.profile_update')}}"
                                       class="dropdown-item-text media gap-3 align-items-center">
                                        <div class="avatar">
                                            <img class="avatar-img rounded-circle" width="50" height="50"
                                                 src="{{auth()->user()->profile_image_full_path}}"
                                                 alt="{{ translate('profile-image') }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5 class="card-title">{{ Str::limit(auth()->user()?->first_name, 20) }}</h5>
                                            <span class="card-text">{{ Str::limit(auth()->user()?->email, 20) }}</span>
                                        </div>
                                    </a>
                                    <a class="dropdown-item" href="{{route('admin.profile_update')}}">
                                        <span class="text-truncate" title="{{translate('Settings')}}">{{translate('Settings')}}</span>
                                    </a>
                                    <a class="dropdown-item admin-logout">
                                        <span class="text-truncate cursor-pointer" title="{{translate('Sign Out')}}">{{translate('Sign_Out')}}</span>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="modal fade removeSlideDown" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-content__search border-0">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex gap-2 align-items-center rounded bg-card py-2 px-3">
                    <form class="flex-grow-1" id="searchForm" action="{{ route('admin.search.routing') }}">
                        @csrf
                        <div class="d-flex align-items-center global-search-container">
                            <span class="material-symbols-outlined">search</span>
                            <input class="form-control flex-grow-1 border-0 search-input" id="searchInput" name="search" type="search" placeholder="Search" aria-label="Search" autofocus>
                        </div>
                    </form>
                    <button class="border-0 rounded-3 px-2 py-1" type="button" data-bs-dismiss="modal">{{ translate('Esc') }}</button>
                </div>

                <div class="bg-card p-4 rounded-3 min-h-350">
                    <div class="search-result" id="searchResults">
                        <div class="text-center text-muted py-5">{{translate('It appears that you have not yet searched.')}}.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
