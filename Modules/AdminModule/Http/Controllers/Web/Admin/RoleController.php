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
use Modules\UserManagement\Entities\Role;
use Modules\UserManagement\Entities\RoleAccess;
use Modules\UserManagement\Entities\RoleSection;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function response;
use function response_formatter;

class RoleController extends Controller
{
    private Role $role;
    private RoleAccess $roleAccess;
    private RoleSection $roleSection;

    use AuthorizesRequests;

    public function __construct(Role $role, RoleAccess $roleAccess, RoleSection $roleSection)
    {
        $this->role = $role;
        $this->roleAccess = $roleAccess;
        $this->roleSection = $roleSection;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View
    {
        $this->authorize('role_view');
        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $queryParams = ['search' => $search, 'status' => $status];

        $roles = $this->role->when($request->has('search'), function ($query) use ($request) {
            $keys = explode(' ', $request['search']);
            foreach ($keys as $key) {
                $query->orWhere('role_name', 'LIKE', '%' . $key . '%');
            }
        })
        ->when($status != 'all', function ($query) use ($status) {
            $query->ofStatus($status == 'active' ? 1 : 0);
        })->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.employee.role-index', compact('roles', 'search','status'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): Application|Factory|View
    {
        $this->authorize('role_add');
        return view('adminmodule::admin.employee.role-create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('role_add');
        $request->validate([
            'role_name' => 'required|unique:roles|max:191',
            'modules'=>'required|array|min:1'
        ],[
            'name.required'=>translate('Role name is required!'),
            'modules.required'=>translate('Please select at latest one module')
        ]);

        $role = $this->role;
        $role->role_name = $request['role_name'];
        $role->save();

        foreach ($request->modules as $key => $section){

            $roleAccess = new RoleAccess();
            $roleAccess->role_id = $role->id;
            $roleAccess->section_name = $key;
            $roleAccess->can_add = $request->add ? 1:0;
            $roleAccess->can_update = $request->update ? 1:0;
            $roleAccess->can_delete = $request->delete ? 1:0;
            $roleAccess->can_export = $request->export ? 1:0;
            $roleAccess->can_manage_status = $request->status ? 1:0;
            $roleAccess->can_approve_or_deny = $request->approve_or_deny ? 1:0;
            $roleAccess->can_assign_serviceman = $request->assign_serviceman ? 1:0;
            $roleAccess->can_give_feedback = $request->give_feedback ? 1:0;
            $roleAccess->can_take_backup = $request->take_backup ? 1:0;
            $roleAccess->can_change_status = $request->change_status ? 1:0;
            $roleAccess->save();
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return redirect('/admin/role/list');
    }

    public function edit(string $id): View|\Illuminate\Foundation\Application|Factory|RedirectResponse|Application
    {
        $this->authorize('role_update');
        $role = $this->role->where('id', $id)->first();
        if ($role){
            $roleAccess = RoleAccess::where('role_id',$id)->get();
            $roleAccessBtn = RoleAccess::where('role_id',$id)->first();
            return view('adminmodule::admin.employee.role-edit', compact('role','roleAccess', 'roleAccessBtn'));
        }

        Toastr::error(translate('Role not found'));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('role_update');
        $request->validate([
            'role_name' => 'required|max:191|unique:roles,role_name,'.$id,
            'modules'=>'required|array|min:1'
        ],[
            'name.required'=>translate('Role name is required!'),
            'modules.required'=>translate('Please select at latest one module')
        ]);

        $role = Role::findOrFail($id);
        $role->role_name = $request['role_name'];
        $role->save();


        foreach ($request->modules as $key => $section) {
            $roleAccess = RoleAccess::where('role_id', $role->id)->where('section_name', $key)->first();
            if ($roleAccess) {
                $roleAccess->can_add = $request->add ? 1 : 0;
                $roleAccess->can_update = $request->update ? 1 : 0;
                $roleAccess->can_delete = $request->delete ? 1 : 0;
                $roleAccess->can_export = $request->export ? 1 : 0;
                $roleAccess->can_manage_status = $request->status ? 1 : 0;
                $roleAccess->can_approve_or_deny = $request->approve_or_deny ? 1 : 0;
                $roleAccess->can_assign_serviceman = $request->assign_serviceman ? 1 : 0;
                $roleAccess->can_give_feedback = $request->give_feedback ? 1 : 0;
                $roleAccess->can_take_backup = $request->take_backup ? 1 : 0;
                $roleAccess->can_change_status = $request->change_status ? 1 : 0;
                $roleAccess->save();
            }else {
                $roleAccess = new RoleAccess();
                $roleAccess->role_id = $role->id;
                $roleAccess->section_name = $key;
                $roleAccess->can_add = $request->add ? 1 : 0;
                $roleAccess->can_update = $request->update ? 1 : 0;
                $roleAccess->can_delete = $request->delete ? 1 : 0;
                $roleAccess->can_export = $request->export ? 1 : 0;
                $roleAccess->can_manage_status = $request->status ? 1 : 0;
                $roleAccess->can_approve_or_deny = $request->approve_or_deny ? 1 : 0;
                $roleAccess->can_assign_serviceman = $request->assign_serviceman ? 1 : 0;
                $roleAccess->can_give_feedback = $request->give_feedback ? 1 : 0;
                $roleAccess->can_take_backup = $request->take_backup ? 1 : 0;
                $roleAccess->can_change_status = $request->change_status ? 1 : 0;
                $roleAccess->save();
            }
        }

        RoleAccess::where('role_id', $role->id)->whereNotIn('section_name', array_keys($request->modules))->delete();

        Toastr::success(translate(USER_ROLE_UPDATE_200['message']));
        return redirect('/admin/role/list');
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
        $this->authorize('role_delete');
        $this->role->where('id', $id)->delete();
        $this->roleAccess->where('role_id', $id)->delete();

        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
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
        $this->authorize('role_export');
        $status = $request->has('status') ? $request['status'] : 'all';
        $items = $this->role->with('roleAccess')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('role_name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($status != 'all', function ($query) use ($status) {
                $query->ofStatus($status == 'active' ? 1 : 0);
            })
            ->latest()->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
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
        $this->authorize('role_manage_status');
        $role = $this->role->where('id', $id)->first();
        $this->role->where('id', $id)->update(['is_active' => !$role->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }
}
