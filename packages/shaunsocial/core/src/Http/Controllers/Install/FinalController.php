<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\FinalInstallManager;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @param  \Packages\LaravelInstaller\Helpers\FinalInstallManager  $finalInstall
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(FinalInstallManager $finalInstall)
    {
        $finalMessages = $finalInstall->runFinal();

        @unlink(storage_path('installed'));
        Artisan::call('storage:link');
        Artisan::call('cache:clear');

        $dateStamp = date('Y/m/d h:i:sa');
        $finalStatusMessage = $message = __('ShaunSocial Installer has been INSTALLED successfully on ').$dateStamp.'.';

        //create file language
        copy(getServerLanguagePath('install'),getServerLanguagePath('en'));
        copy(getClientLanguagePath('install'),getClientLanguagePath('en'));

        return view('shaun_core::install.finished', compact('finalMessages', 'finalStatusMessage'));
    }
}
