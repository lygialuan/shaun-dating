<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Packages\ShaunSocial\Core\Models\Setting;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\DatabaseManager;
use Packages\ShaunSocial\Core\Support\Facades\Utility;

class UpdateController extends Controller
{
    /**
     * Display the updater welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        return view('shaun_core::install.update.welcome');
    }

    /**
     * Display the updater overview page.
     *
     * @return \Illuminate\View\View
     */
    public function overview()
    {
        $currentVersion = setting('site.version');
        //TODO get current version from DB
        $newVersion = file_get_contents(public_path('version.txt'));

        return view('shaun_core::install.update.overview', ['newVersion' => $newVersion, 'currentVersion' => $currentVersion]);
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $newVersion = file_get_contents(public_path('version.txt'));
        $databaseManager = new DatabaseManager;
        $response = $databaseManager->update();
        if ($response['status'] == 'error') {
            return view('shaun_core::install.update.error', compact('response'));
        }
        
        $versionSetting = Setting::where('key', 'site.version')->first();
        $versionSetting->value = $newVersion;
        $versionSetting->save();

        Utility::updateLanguagesExist();
        clearAllCache();
        return redirect()->route('update.final');
    }

    /**
     * Update installed file and display finished view.
     *
     * @return \Illuminate\View\View
     */
    public function finish()
    {
        return view('shaun_core::install.update.finished');
    }
}
