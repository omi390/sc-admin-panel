<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin;


use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Http\Requests\ProviderStoreRequest;
use Modules\UserManagement\Entities\EmployeeRoleAccess;
use Modules\UserManagement\Entities\EmployeeRoleSection;
use Modules\UserManagement\Entities\Role;
use Modules\UserManagement\Entities\RoleAccess;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use function bcrypt;
use function file_remover;
use function file_uploader;
use function response;
use function response_formatter;

class EmployeeController extends Controller
{
    protected User $employee;
    protected UserAddress $address;
    protected Role $role;
    protected Zone $zone;
    protected EmployeeRoleSection $employeeRoleSection;
    protected EmployeeRoleAccess $employeeRoleAccess;
    protected RoleAccess $roleAccess;
    use AuthorizesRequests;

    public function __construct(User $employee, UserAddress $address, Role $role, Zone $zone, EmployeeRoleSection $employeeRoleSection, EmployeeRoleAccess $employeeRoleAccess, RoleAccess $roleAccess)
    {
        $this->employee = $employee;
        $this->address = $address;
        $this->role = $role;
        $this->zone = $zone;
        $this->employeeRoleSection = $employeeRoleSection;
        $this->employeeRoleAccess = $employeeRoleAccess;
        $this->roleAccess = $roleAccess;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): Application|Factory|View
    {
        $this->authorize('employee_add');
        $roles = $this->role->where(['is_active' => 1])->get();
        $zones = $this->zone->where(['is_active' => 1])->get();

        return view('adminmodule::admin.employee.create', compact('roles', 'zones'));
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View
    {
        $this->authorize('employee_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParams = ['search' => $search, 'status' => $status];

        $employees = $this->employee->OfType(['admin-employee'])->with(['roles', 'zones', 'addresses'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('roles', function ($roleQuery) use ($key) {
                                $roleQuery->where('role_name', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($status != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.employee.list', compact('employees', 'status', 'search'));
    }


    /**
     * Store a newly created resource in storage.
     * @param ProviderStoreRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('employee_add');
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required',
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000',
            'identity_type' => 'required|in:passport,driving_license,nid,trade_license',
            'identity_number' => 'required',
            'identity_images' => 'required|array',
            'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'role_id' => 'required|uuid',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'address' => 'required|string'
        ]);

        if (!$request->modules){
            Toastr::error(translate('Please select at latest one module'));
            return back();
        }

        $identityImages = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('employee/identity/', 'png', $image);
            $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
        }

        if (User::where('email', $request['email'])->first()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['phone'])->first()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        DB::transaction(function () use ($request, $identityImages) {

            $employee = $this->employee;
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->profile_image = file_uploader('employee/profile/', 'png', $request->file('profile_image'));
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            $employee->identification_image = $identityImages;
            $employee->password = bcrypt($request->password);
            $employee->user_type = 'admin-employee';
            $employee->is_active = 1;
            $employee->save();

            $employee->zones()->sync($request['zone_ids']);

            $address = $this->address;
            $address->user_id = $employee->id;
            $address->address = $request->address;
            $address->save();


            $employeeRoleSection = $this->employeeRoleSection;
            $employeeRoleSection->employee_id = $employee->id;
            $employeeRoleSection->role_id = $request->role_id;
            $employeeRoleSection->save();

            foreach ($request->modules as $section => $values) {
                if (isset($values['access_role'])) {
                    foreach ($values['access_role'] as $key => $value) {
                        EmployeeRoleAccess::create([
                            'employee_id' => $employee->id,
                            'role_id' => $request->role_id,
                            'section_name' => $key,
                            'can_add' => isset($values['can_add']) ? 1 : 0,
                            'can_update' => isset($values['can_update']) ? 1 : 0,
                            'can_delete' => isset($values['can_delete']) ? 1 : 0,
                            'can_export' => isset($values['can_export']) ? 1 : 0,
                            'can_manage_status' => isset($values['can_manage_status']) ? 1 : 0,
                            'can_assign_serviceman' => isset($values['can_assign_serviceman']) ? 1 : 0,
                            'can_give_feedback' => isset($values['can_give_feedback']) ? 1 : 0,
                            'can_take_backup' => isset($values['can_take_backup']) ? 1 : 0,
                            'can_change_status' => isset($values['can_change_status']) ? 1 : 0,
                        ]);
                    }
                }
            }
        });

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return redirect('/admin/employee/list');
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $id): Application|Factory|View
    {
        $this->authorize('employee_update');
        $roleAccess = $this->employeeRoleAccess->where('employee_id', $id)->get();
        $employee = $this->employee->with(['roles', 'zones', 'addresses'])->where(['id' => $id, 'user_type' => 'admin-employee'])->first();
        $roles = $this->role->where(['is_active' => 1])->get();
        $zones = $this->zone->where(['is_active' => 1])->get();

        return view('adminmodule::admin.employee.edit', compact('roleAccess','roles', 'zones', 'employee'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function setPermission(string $id): Application|Factory|View
    {
        $this->authorize('employee_update');
        $roleAccess = $this->employeeRoleAccess->where('employee_id', $id)->get();
        $employee = $this->employee->with(['roles', 'zones', 'addresses'])->where(['id' => $id, 'user_type' => 'admin-employee'])->first();
        $roles = $this->role->where(['is_active' => 1])->get();
        $zones = $this->zone->where(['is_active' => 1])->get();
        return view('adminmodule::admin.employee.set-permission', compact('roleAccess', 'roles', 'zones', 'employee'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return Redirector|Application|RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): Application|RedirectResponse|Redirector
    {
        $this->authorize('employee_update');
        $employee = $this->employee->where(['id' => $id, 'user_type' => 'admin-employee'])->first();

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => !is_null($request->password) ? 'string|min:8' : '',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : '',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'identity_type' => 'required|in:passport,driving_license,nid,trade_license',
            'identity_number' => 'required',
            'identity_images' => 'array',
            'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'role_id' => 'required|uuid',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'address' => 'required|string'
        ]);

        if (!$request->modules){
            Toastr::error(translate('Please select at latest one module'));
            return back();
        }

        if (User::where('email', $request['email'])->where('id', '!=', $employee->id)->exists()) {
            Toastr::error(translate('Email already taken'));
            return back();
        }
        if (User::where('phone', $request['phone'])->where('id', '!=', $employee->id)->exists()) {
            Toastr::error(translate('Phone already taken'));
            return back();
        }

        $identityImages = [];
        if ($request->has('identity_images')) {
            foreach ($request['identity_images'] as $image) {
                $imageName = file_uploader('employee/identity/', 'png', $image);
                $identityImages[] = ['image'=>$imageName, 'storage'=> getDisk()];
            }
            $employee->identification_image = $identityImages;
        }
        DB::transaction(function () use ($id, $employee, $request, $identityImages) {
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            if ($request->has('profile_image')) {
                $employee->profile_image = file_uploader('employee/profile/', 'png', $request->file('profile_image'), $employee->profile_image);;
            }
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            if (!is_null($request->password)) {
                $employee->password = bcrypt($request->password);
            }
            $employee->user_type = 'admin-employee';
            $employee->password = !is_null($request->password) ? bcrypt($request->password) : $employee->password;
            $employee->save();

            $employee->roles()->sync([$request['role_id']]);
            $employee->zones()->sync($request['zone_ids']);

            $address = $this->address->where('user_id', $id)->first();
            $address->address = $request->address;
            $address->save();

            $employeeRoleSection = $this->employeeRoleSection->where('employee_id', $id)->first();
            $employeeRoleSection->employee_id = $id;
            $employeeRoleSection->role_id = $request->role_id;
            $employeeRoleSection->save();

            $employeeRoleAccess = $this->employeeRoleAccess->where('employee_id', $id)->first();
            if ($employeeRoleAccess) {
                $existingRoleId = $employeeRoleAccess->role_id;

                if ($existingRoleId !== $request->role_id) {
                    $this->employeeRoleAccess
                        ->where('employee_id', $id)
                        ->where('role_id', $existingRoleId)
                        ->delete();
                }
            }
            foreach ($request->modules as $section => $values) {
                if (isset($values['access_role'])) {
                    foreach ($values['access_role'] as $key => $value) {
                        $accessData = [
                            'employee_id' => $employee->id,
                            'role_id' => $request->role_id,
                            'section_name' => $key,
                            'can_add' => isset($values['can_add']) ? 1 : 0,
                            'can_update' => isset($values['can_update']) ? 1 : 0,
                            'can_delete' => isset($values['can_delete']) ? 1 : 0,
                            'can_export' => isset($values['can_export']) ? 1 : 0,
                            'can_manage_status' => isset($values['can_manage_status']) ? 1 : 0,
                            'can_assign_serviceman' => isset($values['can_assign_serviceman']) ? 1 : 0,
                            'can_give_feedback' => isset($values['can_give_feedback']) ? 1 : 0,
                            'can_take_backup' => isset($values['can_take_backup']) ? 1 : 0,
                            'can_change_status' => isset($values['can_change_status']) ? 1 : 0,
                        ];

                        $existingAccess = $this->employeeRoleAccess->where('employee_id', $employee->id)
                            ->where('role_id', $request->role_id)
                            ->where('section_name', $key)
                            ->first();
                        $roleAccess = $this->roleAccess->where('role_id', $request->role_id)->with('role')->get();
                        foreach ($roleAccess as $access){
                            if ($access->can_add == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_add'] == 1) {
                                $message = "Permission 'add' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_update == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_update'] == 1) {
                                $message = "Permission 'update' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_delete == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_delete'] == 1) {
                                $message = "Permission 'delete' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_export == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_export'] == 1) {
                                $message = "Permission 'export' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_manage_status == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_manage_status'] == 1) {
                                $message = "Permission 'status' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_assign_serviceman == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_assign_serviceman'] == 1) {
                                $message = "Permission 'assign serviceman' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_give_feedback == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_give_feedback'] == 1) {
                                $message = "Permission 'give feedback' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                            if ($access->can_take_backup == 0 && $accessData['section_name'] == $access->section_name && $accessData['can_take_backup'] == 1) {
                                $message = "Permission 'take backup' is not allowed for role {$access->role->role_name} in section {$access->section_name}.";
                                $this->alertMessage($message);
                            }
                        }
                        if ($existingAccess && empty($message)) {
                            $existingAccess->update($accessData);
                        } else {
                            $this->employeeRoleAccess->create($accessData);
                        }
                    }
                }
            }
            if(empty($message)) {
                Toastr::success(translate(DEFAULT_UPDATE_200['message']));
            }
        });
        return redirect('/admin/employee/list');
    }
    function alertMessage ($message){
        Toastr::error(translate($message));
        return redirect('/admin/employee/list');
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('employee_delete');
        $user = $this->employee->where('id', $id)->first();
        if (isset($user)) {
            file_remover('employee/profile_image/', $user->profile_image);
            foreach ($user->identification_image as $image_name) {
                file_remover('employee/identity/', $image_name);
            }
            $user->delete();

            $this->employeeRoleAccess->where('employee_id', $id)->delete();
            $this->employeeRoleSection->where('employee_id', $id)->delete();

            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }

        Toastr::success(translate(DEFAULT_204['message']));
        return back();
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('employee_manage_status');
        $user = $this->employee->where('id', $id)->first();
        $this->employee->where('id', $id)->update(['is_active' => !$user->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_image(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|uuid',
            'image_name' => 'required|string',
            'image_type' => 'required|in:logo,identity_image'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $employee = $this->employee->where('id', $request['employee_id'])->first();
        if ($request['image_type'] == 'identity_image') {
            file_remover('employee/identity/', $request['image_name']);
            $employee->identification_image = array_diff($employee->identification_image, $request['image_name']);
            $employee->save();
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws AuthorizationException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('employee_export');
        $items = $this->employee->OfType(['admin-employee'])->with(['roles', 'zones', 'addresses'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function ajaxRoleAccess(Request $request)
    {
        $roleAccess = $this->roleAccess->where('role_id', $request->role_id)->get();
        $view = view('adminmodule::layouts.partials.employee-role-access', compact('roleAccess'))->render();
        return response()->json(['html' => $view], 200);
    }

    public function ajaxEmployeeRoleAccess(Request $request)
    {
        $employeeRoleSection = $this->employeeRoleSection->where('employee_id', $request->id)->where('role_id', $request->role_id)->first();
        if ($employeeRoleSection) {
            $roleAccess = $this->employeeRoleAccess->where('employee_id', $request->id)->where('role_id', $request->role_id)->get();
            $view = view('adminmodule::layouts.partials.employee-update-access', compact('roleAccess'))->render();
            return response()->json(['html' => $view], 200);
        }

        $roleAccess = $this->roleAccess->where('role_id', $request->role_id)->get();
        $view = view('adminmodule::layouts.partials.employee-role-access', compact('roleAccess'))->render();
        return response()->json(['html' => $view], 200);
    }

}
