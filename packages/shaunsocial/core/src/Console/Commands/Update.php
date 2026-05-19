<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Core\Models\Setting;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\DatabaseManager;
use Packages\ShaunSocial\Core\Support\Facades\Utility;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! setting('site.title')) {
            return;
        }
        
        $newVersion = file_get_contents(public_path('version.txt'));
        $databaseManager = new DatabaseManager;
        $databaseManager->update();
        
        $versionSetting = Setting::where('key', 'site.version')->first();
        $versionSetting->value = $newVersion;
        $versionSetting->save();

        Utility::updateLanguagesExist();
        clearAllCache();
    }
}
