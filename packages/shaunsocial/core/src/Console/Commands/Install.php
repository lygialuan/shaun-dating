<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Core\Models\Setting;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\User;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:install {site_name?} {site_url?} {site_admin_email?} {site_admin_password?}';

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
        if (setting('site.title')) {
            return;
        }
        $siteName = $this->argument('site_name') ?? 'shaunSocial';
        $siteUrl = $this->argument('site_url') ?? 'http://localhost';
        $siteAdminEmail = $this->argument('site_admin_email') ?? 'admin@admin.com';
        $siteAdminPassword = $this->argument('site_admin_password') ?? 'admin';
        $timezoneList = getTimezoneList();
        
        //run sql install
        $path = base_path('packages/shaunsocial');
        $directories = scandir($path);
        $packages = ['core', 'wallet'];
        foreach ($directories as $name) {
            if ($name != '.' && $name != '..' && $name != 'core' && $name != 'wallet') {
                $packages[] = $name;
            }
        }

        foreach ($packages as $name) {
            $sqlFile = $path.'/'.$name.'/database/sql/install.sql';
            runSqlFile($sqlFile);

            //run install
            $packageName = getPackageName($name);
            $packageName = 'Packages\ShaunSocial\\'.$packageName.'\Repositories\Helpers\Package';
            if (class_exists($packageName)) {
                $package = app($packageName);
                if (method_exists($package, 'install')) {
                    $package->install();
                }
            }
        }

        //save setting
        $siteConfig = [
            'site.title' => $siteName,
            'site.email' => $siteAdminEmail,
            'site.url' => $siteUrl,
            'site.timezone' => array_key_first($timezoneList),
        ];
        $settings = Setting::whereIn('key', array_keys($siteConfig))->get();

        foreach ($settings as $setting) {
            $content =  $siteConfig[$setting->key];

            $setting->value = $content;
            $setting->save();
        }

        $secretKey = Setting::where('key', 'app.api_secret_key')->first();
        $secretKey->value = Str::random(6);
        $secretKey->save();

        $appUrl = env('APP_URL',false);
        if ($appUrl && $appUrl != 'http://localhost') {
            $appUrlKey = Setting::where('key', 'site.url')->first();
            $appUrlKey->value = $appUrl;
            $appUrlKey->save();
        }

        //cloud
        if (env('CLOUD_ENABLE')) {
            $ffmpegEnable = Setting::where('key', 'feature.ffmpeg_enable')->first();
            $ffmpegEnable->value = 1;
            $ffmpegEnable->save();
    
            $ffmpegPath = Setting::where('key', 'feature.ffmpeg_path')->first();
            $ffmpegPath->value = 'ffmpeg';
            $ffmpegPath->save();
    
            $broadcastEnable = Setting::where('key', 'broadcast.enable')->first();
            $broadcastEnable->value = 1;
            $broadcastEnable->save();
        }
        
        // create admin
        User::create([
            'name' => 'Admin',
            'email' => $siteAdminEmail,
            'password' => Hash::make($siteAdminPassword),
            'role_id' => config('shaun_core.role.id.root'),
            'user_name' => 'admin',
            'already_setup_login' => true,
            'is_active' => true,
            'timezone' => array_key_first($timezoneList),
            'email_verified' => true,
            'darkmode' => 'auto',
            'video_auto_play' => true
        ]);

        //create file language
        copy(getServerLanguagePath('install'),getServerLanguagePath('en'));
        copy(getClientLanguagePath('install'),getClientLanguagePath('en'));

        Artisan::call('storage:link');
        Artisan::call('cache:clear');
    }
}
