<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BusinessSettingsModule\Entities\ErrorLog;
use Modules\BusinessSettingsModule\Entities\SeoSetting;

class SEOSettingController extends Controller
{
    private SeoSetting $seoSetting;
    private ErrorLog $errorLog;
    public function __construct(SeoSetting $seoSetting, ErrorLog $errorLog)
    {
        $this->seoSetting = $seoSetting;
        $this->errorLog = $errorLog;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $webPage = $request->has('page_type') ? $request['page_type'] : 'error_logs';
        if($webPage == 'error_logs'){
            $errorLogs = $this->errorLog->latest()->paginate(pagination_limit());
            return view('businesssettingsmodule::admin.seo-pages.seo-404-logs', compact('errorLogs','webPage'));
        }
    }

    /**
     * @param Request $request
     * @param $pageName
     * @return RedirectResponse
     */
    public function redirectLink(Request $request, $id): RedirectResponse
    {
        $errorLog = $this->errorLog->where('id', $id)->first();
        if ($errorLog){
            $errorLog->redirect_url = $request->redirection_link;
            $errorLog->save();

            Toastr::success(translate(DEFAULT_UPDATE_200['message']));
            return back();
        }

        Toastr::success(translate(DEFAULT_404['message']));
        return back();
    }

    public function errorLogDestroy(Request $request, $id): RedirectResponse
    {
        $errorLog = $this->errorLog->where('id', $id)->first();
        if (isset($errorLog)) {
            $errorLog->delete();

            Toastr::success(translate(DEFAULT_DELETE_200['message']));
            return back();
        }
        Toastr::success(translate(DEFAULT_404['message']));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        // Get the selected log IDs
        $logIds = $request->input('log_ids', []);

        // Perform bulk deletion
        if (!empty($logIds)) {
            ErrorLog::whereIn('id', $logIds)->delete();

            return back()->with('success', 'Selected logs deleted successfully.');
        }

        return back()->with('error', 'No logs selected.');
    }
}
