<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Api\V1\Admin;

use Illuminate\Routing\Controller;

class FileManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return void
     */
    public function index()
    {
        $this->listFolderFiles(base_path('storage/app/public'));
    }

    function listFolderFiles($directory)
    {
        $file_and_folders = scandir($directory);

        unset($file_and_folders[array_search('.', $file_and_folders, true)]);
        unset($file_and_folders[array_search('..', $file_and_folders, true)]);

        // prevent empty ordered elements
        if (count($file_and_folders) < 1)
            return;
        echo '<ol>';

        foreach ($file_and_folders as $item) {
            echo '<li>' . $item;
            if (is_dir($directory . '/' . $item)) $this->listFolderFiles($directory . '/' . $item);
            echo '</li>';
        }
        echo '</ol>';
    }
}
