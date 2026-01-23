
<hr class="mb-30">
@foreach(SYSTEM_MODULES as $module)
    @php
        $buttonPermission = ['can_add', 'can_update', 'can_delete', 'can_export', 'can_manage_status','can_approve_or_deny','can_assign_serviceman','can_give_feedback','can_take_backup'];
        $matchedRoleBtn = $roleAccess->where('section_name', $module['key'])->first();
        $hasMatchingSubmodules = false;
    @endphp
    @if(isset($module['submodules']))
        @foreach($module['submodules'] as $submodule)
            @php
                $matchedRoleSection = $roleAccess->where('section_name', $submodule['key'])->first();
                if($matchedRoleSection) {
                    $hasMatchingSubmodules = true;
                    break;
                }
            @endphp
        @endforeach
        @if($hasMatchingSubmodules)
            <div class="rounded border mb-3">
                <div class="card-body">
                    <h4>{{ $module['value'] }}</h4>
                    <hr>
                    <div class="grid-columns mt-4">
                        @php $tableRendered = false; @endphp
                        @foreach($module['submodules'] as $submodule)
                            @php
                                $matchedRoleSection = $roleAccess->where('section_name', $submodule['key'])->first();
                            @endphp
                            @if($matchedRoleSection)
                                <div class="d-flex gap-1 align-items-center">
                                    <input class="mb-1" type="checkbox" name="modules[{{$module['key']}}][access_role][{{ $submodule['key'] }}]" id="{{ $submodule['key'] }}" @if($matchedRoleSection) checked @endif>
                                    <label class="user-select-none flex-grow-1" for="{{ $submodule['key'] }}">{{ $submodule['value'] }}</label>
                                </div>
                                @if($hasMatchingSubmodules && !$tableRendered)
                                    <div class="span-full">
                                        @php
                                            $showManageAccess = false;

                                            foreach ($buttonPermission as $permission) {
                                                if ($permission === 'can_view') {
                                                    continue;
                                                }

                                                if (isset($matchedRoleSection[$permission]) && $matchedRoleSection[$permission] === 1) {
                                                    $showManageAccess = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if ($showManageAccess)
                                            <h4 class="mb-3 mt-4">{{ translate('Manage Access') }}</h4>
                                            @php $tableRendered = true; @endphp
                                            <div class="table-responsive">
                                                <table class="table align-middle border-bottom">
                                                    <thead class="text-nowrap">
                                                    <tr>
                                                        @foreach($buttonPermission as $permission)
                                                            @if($matchedRoleSection->$permission)
                                                                @php
                                                                    $permissionWords = explode('_', $permission);
                                                                    $lastWord = end($permissionWords);
                                                                @endphp
                                                                <th class="text-center">{{ translate(ucfirst(str_replace('_', ' ', $lastWord))) }}</th>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        @foreach($buttonPermission as $permission)
                                                            @if($matchedRoleSection->$permission)
                                                                <td>
                                                                    <label class="switcher mx-auto">
                                                                        <input class="switcher_input" name="modules[{{$module['key']}}][{{ $permission }}]" type="checkbox" @if($matchedRoleSection->$permission) checked @endif>
                                                                        <span class="switcher_control"></span>
                                                                    </label>
                                                                </td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @elseif(!$tableRendered)
                                            @php $tableRendered = true; @endphp
                                            <div>
                                                <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show mt-3 mb-0" role="alert">
                                                    <div class="media gap-2">
                                                        <img src="{{asset('public/assets/admin-module/img/WarningOctagon.svg')}}" class="svg" alt="">
                                                        <div class="media-body">
                                                            {{translate('Employee this role can only view the section')}}
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @elseif($matchedRoleBtn)
        <div class="rounded border mb-3">
            <div class="card-body">
                <h4>{{ $module['value'] }}</h4>
                <input type="hidden" name="modules[{{ $module['key'] }}][access_role][{{ $module['key'] }}]">
                @php
                    $showManageAccess = false;

                    foreach ($buttonPermission as $permission) {
                        if ($permission === 'can_view') {
                            continue;
                        }

                        if (isset($matchedRoleBtn[$permission]) && $matchedRoleBtn[$permission] === 1) {
                            $showManageAccess = true;
                            break;
                        }
                    }
                @endphp
                @if ($showManageAccess)
                    <hr>
                    <h4 class="mb-3 mt-4">{{ translate('Manage Access') }}</h4>

                    <div class="table-responsive">
                        <table class="table align-middle border-bottom">
                            <thead class="text-nowrap">
                            <tr>
                                @foreach($buttonPermission as $permission)
                                    @if($matchedRoleBtn->$permission)
                                        @php
                                            $permissionWords = explode('_', $permission);
                                            $lastWord = end($permissionWords);
                                        @endphp
                                        <th class="text-center">{{ translate(ucfirst(str_replace('_', ' ', $lastWord))) }}</th>
                                    @endif
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                @foreach($buttonPermission as $permission)
                                    @if($matchedRoleBtn->$permission)
                                        <td>
                                            <label class="switcher mx-auto">
                                                <input class="switcher_input" name="modules[{{$module['key']}}][{{ $permission }}]" type="checkbox" @if($matchedRoleBtn->$permission) checked @endif>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div>
                        <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show mt-3 mb-0" role="alert">
                            <div class="media gap-2">
                                <img src="{{asset('public/assets/admin-module/img/WarningOctagon.svg')}}" class="svg" alt="">
                                <div class="media-body">
                                    {{translate('Employee this role can only view the section')}}
                                </div>
                            </div>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endforeach
