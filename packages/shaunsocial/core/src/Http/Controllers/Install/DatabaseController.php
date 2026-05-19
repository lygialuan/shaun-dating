<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Routing\Controller;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\DatabaseManager;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param  DatabaseManager  $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $response = $this->databaseManager->install();

        if ($response['status'] == 'error') {
            //rollback save .env action
            @unlink(base_path('.env'));

            return view('shaun_core::install.error', compact('response'));
        } else {
            return redirect()->route('install.setting');
        }
    }
}
