<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Routing\Controller;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\PermissionsChecker;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\RequirementsChecker;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\WriteFoldersChecker;

class RequirementsController extends Controller
{
    /**
     * @var RequirementsChecker
     */
    protected $requirements;

    /**
     * @var PermissionsChecker
     */
    protected $permissions;

    /**
     * @var WritesFolderChecker
     */
    protected $writeFoldersChecker;

    /**
     * @param  RequirementsChecker  $checker
     */
    public function __construct(RequirementsChecker $checker, PermissionsChecker $permissionsChecker, WriteFoldersChecker $writeFoldersChecker)
    {
        $this->requirements = $checker;
        $this->permissions = $permissionsChecker;
        $this->writeFoldersChecker = $writeFoldersChecker;
    }

    /**
     * Display the requirements page.
     *
     * @return \Illuminate\View\View
     */
    public function requirements()
    {
        $phpSupportInfo = $this->requirements->checkPHPversion(
            config('shaun_core_install.core.minPhpVersion')
        );
        $requirements = $this->requirements->check(
            config('shaun_core_install.requirements')
        );

        $permissions = $this->permissions->check(
            config('shaun_core_install.permissions')
        );

        $writeFolders = $this->writeFoldersChecker->check(
            config('shaun_core_install.writeFolders')
        );

        return view('shaun_core::install.requirements', compact('requirements', 'phpSupportInfo', 'permissions', 'writeFolders'));
    }
}
