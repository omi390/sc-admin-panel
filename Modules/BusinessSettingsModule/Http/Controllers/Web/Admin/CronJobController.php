<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Modules\BusinessSettingsModule\Entities\CronJob;

class CronJobController extends Controller
{
    private CronJob $cronJob;
    public function __construct(CronJob $cronJob)
    {
        $this->cronJob = $cronJob;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $lists = $this->cronJob->latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->paginate(pagination_limit());

        return view('businesssettingsmodule::admin.cron-job.list', compact('lists'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $cronjob = $this->cronJob->findOrFail($id);
        return view('businesssettingsmodule::admin.cron-job.edit', compact('cronjob'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $cronJob = $this->cronJob->findOrFail($id);

        if ($cronJob) {
            $cronJob->send_mail_type = $request->send_mail_type;
            $cronJob->send_mail_day = $request->send_mail_day;
            $newPhpFilePath = $request->php_file_path;

            if ($cronJob->php_file_path !== $newPhpFilePath) {
                $cronJob->php_file_path = $newPhpFilePath;

                $data = $this->generateCronCommand(
                    phpFilePath: $cronJob->php_file_path,
                    type: $cronJob->type
                );

                $cronJob->command = $data['addCronCommand'];

                if (function_exists('exec')) {
                    $scriptPath = 'script.sh';
                    exec('sh ' . $scriptPath);

                    $cronJob->activity = 'running';
                    $cronJob->status = 1;
                    $cronJob->save();

                    Toastr::success(translate('successfully_updated_cron_job_functionality'));
                    return back();
                } else {
                    $cronJob->activity = 'error';
                    $cronJob->save();

                    Session::flash('function_exec', true);
                    Toastr::warning(translate('Servers_PHP_exec_function_is_disabled_check_dependencies_&_start_cron_job_manually_in_server'));
                }
            } else {
                $cronJob->save();

                Toastr::success(translate('successfully_updated'));
                return back();
            }
        }

        Toastr::error(translate('Please update valid cronjob'));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function status($id): JsonResponse
    {
        $cronJob = $this->cronJob->findOrFail($id);
        if ($cronJob && $cronJob->status == 1) {
            $this->removeCronJob(
                phpFilePath: $cronJob->php_file_path,
                type: $cronJob->type
            );

            $cronJob->activity = 'disable';
            $cronJob->status = 0;
            $cronJob->save();

            return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
        }else{
            $this->generateCronCommand(
                phpFilePath: $cronJob->php_file_path,
                type: $cronJob->type
            );

            if (function_exists('exec')) {
                $scriptPath = 'script.sh';
                exec('sh ' . $scriptPath);

                $cronJob->activity = 'running';
                $cronJob->status = 1;
                $cronJob->save();

                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }

            return response()->json(response_formatter(CRONJOB_SETUP_MANUALLY), 200);
        }
    }

    /**
     * @return array{addCronCommand: string}
     */
    protected function generateCronCommand($phpFilePath, $type): array
    {
        $phpPath = $phpFilePath ?? "/usr/bin/php";
        $cronSchedule = "0 0 * * *";

        $scriptFilename = $_SERVER['SCRIPT_FILENAME'];
        $rootPath = dirname($scriptFilename);

        if ($type === 'subscription_renewal_reminder'){
            $disbursementScriptPath = $rootPath . "/artisan email:send-renewal-reminder";
        }elseif ($type === 'free_trial_end'){
            $disbursementScriptPath = $rootPath . "/artisan email:free-trial-end-mail";
        }elseif ($type === 'subscription_time_end'){
            $disbursementScriptPath = $rootPath . "/artisan email:subscription-time-end-mail";
        }

        $clearCronCommand = "(crontab -l | grep -v \"$phpPath $disbursementScriptPath\") | crontab -";

        $addCronCommand = "(crontab -l; echo \"$cronSchedule $phpPath $disbursementScriptPath\") | crontab -";

        $cronCommand = $cronSchedule . ' ' . 'root ' . $phpPath . ' ' . $disbursementScriptPath;

        $scriptContent = "#!/bin/bash\n";
        $scriptContent .= $clearCronCommand . "\n";
        $scriptContent .= $addCronCommand . "\n";

        $scriptFilePath = $rootPath . "/script.sh";

        file_put_contents($scriptFilePath, $scriptContent);

        return [
            'addCronCommand' => $cronCommand,
        ];
    }

    protected function removeCronJob($phpFilePath, $type): void
    {
        $scriptFilename = $_SERVER['SCRIPT_FILENAME'];
        $rootPath = dirname($scriptFilename);

        if ($type === 'subscription_renewal_reminder'){
            $disbursementScriptPath = $rootPath . "/artisan email:send-renewal-reminder";
        } elseif ($type === 'free_trial_end') {
            $disbursementScriptPath = $rootPath . "/artisan email:free-trial-end-mail";
        } elseif ($type === 'subscription_time_end') {
            $disbursementScriptPath = $rootPath . "/artisan email:subscription-time-end-mail";
        }

        $phpPath = $phpFilePath ?? "/usr/bin/php";
        $clearCronCommand = "#!/bin/bash\n";
        $clearCronCommand .= "(crontab -l | grep -v \"$phpPath $disbursementScriptPath\") | crontab -";

        $scriptFilePath = $rootPath . "/script.sh";
        file_put_contents($scriptFilePath, $clearCronCommand);

        exec($clearCronCommand);
    }

}
