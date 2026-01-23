
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
                    <div class="grid-columns mt-4 pb-2">
                        @php $tableRendered = false; @endphp
                        @foreach($module['submodules'] as $submodule)
                            @php
                                $matchedRoleSection = $roleAccess->where('section_name', $submodule['key'])->first();
                            @endphp
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
                                            <div class="table-responsive">
                                                <table class="table align-middle border-bottom">
                                                    <thead class="text-nowrap">
                                                    <tr>
                                                        @foreach($buttonPermission as $permission)
                                                            @php
                                                                $permissionWords = explode('_', $permission);
                                                                $lastWord = end($permissionWords);
                                                            @endphp
                                                            <th class="text-center">{{ translate(ucfirst(str_replace('_', ' ', $lastWord))) }}</th>
                                                        @endforeach
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        @foreach($buttonPermission as $permission)
                                                            <td>
                                                                <label class="switcher mx-auto">
                                                                    <input class="switcher_input" name="modules[{{$module['key']}}][{{ $permission }}]" type="checkbox" @if($matchedRoleSection->$permission) checked @endif> {{--@if($matchedRoleSection->$permission) checked @endif--}}
                                                                    <span class="switcher_control"></span>
                                                                </label>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            @php $tableRendered = true; @endphp
                                        @endif
                                    </div>
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
                <hr>
                <h4 class="mb-3 mt-4">{{ translate('Manage Access') }}</h4>
                <div class="table-responsive">
                    <table class="table align-middle border-bottom">
                        <thead class="text-nowrap">
                        <tr>
                            @foreach($buttonPermission as $permission)
                                    @php
                                        $permissionWords = explode('_', $permission);
                                        $lastWord = end($permissionWords);
                                    @endphp
                                    <th class="text-center">{{ translate(ucfirst(str_replace('_', ' ', $lastWord))) }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            @foreach($buttonPermission as $permission)
                                <td>
                                    <label class="switcher mx-auto">
                                        <input class="switcher_input" name="modules[{{$module['key']}}][{{ $permission }}]" type="checkbox" @if($matchedRoleBtn->$permission) checked @endif>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endforeach
