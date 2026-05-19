<?php


namespace Packages\ShaunSocial\Core;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Packages\ShaunSocial\Core\Auth\ShaunEloquentUserProvider;
use Packages\ShaunSocial\Core\Console\Commands\MailDailyRun;
use Packages\ShaunSocial\Core\Console\Commands\MailSend;
use Packages\ShaunSocial\Core\Console\Commands\StorageTransfer;
use Packages\ShaunSocial\Core\Console\Commands\TaskCheck;
use Packages\ShaunSocial\Core\Console\Commands\UserFollowNotify;
use Packages\ShaunSocial\Core\Http\Middleware\Application;
use Packages\ShaunSocial\Core\Http\Middleware\ApplicationApi;
use Packages\ShaunSocial\Core\Http\Middleware\ApplicationInternalApi;
use Packages\ShaunSocial\Core\Http\Middleware\canInstall;
use Packages\ShaunSocial\Core\Http\Middleware\canUpdate;
use Packages\ShaunSocial\Core\Http\Middleware\forceInstall;
use Packages\ShaunSocial\Core\Http\Middleware\HasPermission;
use Packages\ShaunSocial\Core\Http\Middleware\IsAdmin;
use Packages\ShaunSocial\Core\Http\Middleware\IsAdminGuest;
use Packages\ShaunSocial\Core\Http\Middleware\IsSuperAdmin;
use Packages\ShaunSocial\Core\Models\HashtagTrending;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Models\Sanctum\PersonalAccessToken;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Models\StorageService;
use Packages\ShaunSocial\Core\Models\UserHashtagSuggest;
use Packages\ShaunSocial\Core\Services\File;
use Packages\ShaunSocial\Core\Services\Mail;
use Packages\ShaunSocial\Core\Services\Notification;
use Packages\ShaunSocial\Core\Services\Setting;
use Packages\ShaunSocial\Core\Services\Utility;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use jdavidbakr\LaravelCacheGarbageCollector\LaravelCacheGarbageCollector;
use Packages\ShaunSocial\Advertising\Models\AdvertisingDelivery;
use Packages\ShaunSocial\Advertising\Models\AdvertisingStatistic;
use Packages\ShaunSocial\Chatbot\Models\ChatbotHistory;
use Packages\ShaunSocial\Core\Console\Commands\Install;
use Packages\ShaunSocial\Core\Console\Commands\PostQueueRun;
use Packages\ShaunSocial\Core\Console\Commands\PostStatisticRun;
use Packages\ShaunSocial\Core\Console\Commands\SitemapRun;
use Packages\ShaunSocial\Core\Console\Commands\SubscriptionRun;
use Packages\ShaunSocial\Core\Console\Commands\TaskCheckTmpFile;
use Packages\ShaunSocial\Core\Console\Commands\TranslateCompare;
use Packages\ShaunSocial\Core\Console\Commands\TranslateExport;
use Packages\ShaunSocial\Core\Console\Commands\TranslateExportApp;
use Packages\ShaunSocial\Core\Console\Commands\TranslateGoogleExport;
use Packages\ShaunSocial\Core\Console\Commands\Update;
use Packages\ShaunSocial\Core\Console\Commands\UserDeleteRun;
use Packages\ShaunSocial\Core\Console\Commands\UserDownloadRun;
use Packages\ShaunSocial\Core\Console\Commands\UserListMessageRun;
use Packages\ShaunSocial\Core\Http\Middleware\restrictIpAddress;
use Packages\ShaunSocial\Core\Models\CommentItem;
use Packages\ShaunSocial\Core\Models\CommentReplyItem;
use Packages\ShaunSocial\Core\Models\Distinct;
use Packages\ShaunSocial\PaidContent\Models\UserPostPaidOrder;
use Packages\ShaunSocial\Core\Models\PostQueue;
use Packages\ShaunSocial\Core\Models\UserActionLog;
use Packages\ShaunSocial\Core\Models\UserHashtag;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Services\Firebase;
use Packages\ShaunSocial\Core\Services\Subscription;
use Packages\ShaunSocial\Core\Services\Theme;
use Packages\ShaunSocial\Group\Models\GroupHashtagTrending;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Core\Models\PhotoVerifyItem;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //add helpers
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }

        //Define blade
        Blade::directive('isRouter', function ($name) {
            return "<?php if (in_array(Route::current()->getName(),explode(',','{$name}'))) : ?>";
        });

        Blade::directive('endIsRouter', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('isModerator', function () {
            return '<?php if(auth()->check() && auth()->user()->isModerator()) : ?>';
        });

        Blade::directive('endIsModerator', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('isSuperAdmin', function () {
            return '<?php if(auth()->check() && auth()->user()->isSuperAdmin()) : ?>';
        });

        Blade::directive('endIsSuperAdmin', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('hasPermission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$permission})) : ?>";
        });

        Blade::directive('endHasPermission', function ($permission) {
            return '<?php endif; ?>';
        });

        //add config
        $this->mergeConfigFrom(__DIR__.'/../config/constant.php', 'shaun_core');
        $this->mergeConfigFrom(__DIR__.'/../config/install.php', 'shaun_core_install');

        //add router
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/install.php');

        //add migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //add view
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shaun_core');

        //define constant
        if (! defined('SHAUN_SOCIAL_CORE_PATH')) {
            define('SHAUN_SOCIAL_CORE_PATH', __DIR__.'/..');
        }

        $this->app->bind('setting', function () {
            return new Setting;
        });

        $this->app->bind('mail', function () {
            return new Mail;
        });

        $this->app->bind('file', function () {
            return new File;
        });

        $this->app->bind('utility', function () {
            return new Utility;
        });

        $this->app->bind('notification', function () {
            return new Notification;
        });

        $this->app->bind('theme', function () {
            return new Theme;
        });

        $this->app->bind('subscription', function () {
            return new Subscription;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
        //add validate
        Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
            if (!$value instanceof UploadedFile) {
                return false;
            }
    
            $extensions = implode(',', $parameters);
            $validator->addReplacer('file_extension', function (
                $message,
                $attribute,
                $rule,
                $parameters
            ) use ($extensions) {
                return str_replace(':values', $extensions, $message);
            });
    
            $extension = strtolower($value->getClientOriginalExtension());
    
            return $extension !== '' && in_array($extension, $parameters);
        });

        //add middleware
        app('router')->middlewareGroup('install', [CanInstall::class]);
        app('router')->middlewareGroup('update', [CanUpdate::class]);
        app('router')->middlewareGroup('is.admin.guest', [IsAdminGuest::class]);
        app('router')->aliasMiddleware('is.admin', IsAdmin::class);
        app('router')->aliasMiddleware('is.supper.admin', IsSuperAdmin::class);
        app('router')->aliasMiddleware('has.permission', HasPermission::class);
        app('router')->pushMiddlewareToGroup('web', forceInstall::class);
        app('router')->pushMiddlewareToGroup('web', Application::class);
        app('router')->pushMiddlewareToGroup('api', ApplicationApi::class);
        app('router')->pushMiddlewareToGroup('web', restrictIpAddress::class);
        app('router')->pushMiddlewareToGroup('api', restrictIpAddress::class);
        app('router')->aliasMiddleware('internal', ApplicationInternalApi::class);

        if ($this->app->request->headers->get('SupportCookie')) {
            $route = Route::getRoutes()->match($this->app->request);
            $arrayRouter = ['auth_logout', 'auth_login', 'auth_signup'];
            if ($route && in_array($route->action['as'], $arrayRouter)) {
                app('router')->pushMiddlewareToGroup('api', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
            }
        }

        if ($this->app->request->headers->get('Cf-Connecting-Ip')) {
            $this->app->request->server->add(['REMOTE_ADDR' => $this->app->request->headers->get('Cf-Connecting-Ip')]);
        }
        
        if ($this->app->request->server->get('HTTP_X_REAL_IP')) {
            $this->app->request->server->add(['REMOTE_ADDR' => $this->app->request->server->get('HTTP_X_REAL_IP')]);
        }

        // command
        $this->commands([
            MailSend::class,
            StorageTransfer::class,
            TaskCheck::class,
            UserFollowNotify::class,
            MailDailyRun::class,
            UserDeleteRun::class,
            TranslateExport::class,
            TranslateGoogleExport::class,
            TranslateExportApp::class,
            Install::class,
            Update::class,
            PostQueueRun::class,
            UserDownloadRun::class,
            LaravelCacheGarbageCollector::class,
            SubscriptionRun::class,
            TranslateCompare::class,
            TaskCheckTmpFile::class,
            PostStatisticRun::class,
            UserListMessageRun::class,
            SitemapRun::class
        ]);
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('shaun_core:task_check')->everyFiveMinutes();
            $schedule->command('shaun_core:mail_send')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_core:storage_transfer')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_core:user_follow_notify')->withoutOverlapping(10)->everyTwoMinutes();
            $schedule->command('shaun_core:mail_daily_run')->withoutOverlapping(10)->everyTwoMinutes();
            $schedule->command('shaun_core:user_delete_run')->withoutOverlapping(10)->everyTwoMinutes();
            $schedule->command('shaun_core:post_queue_run')->withoutOverlapping(60)->everyMinute();
            $schedule->command('shaun_core:user_download_run')->withoutOverlapping(10)->everyMinute();
            $schedule->command('model:prune',[
                '--model' => [
                    HashtagTrending::class, 
                    PostHome::class, 
                    PostItem::class, 
                    StorageFile::class, 
                    UserHashtagSuggest::class, 
                    PersonalAccessToken::class, 
                    Distinct::class, 
                    UserHashtag::class, 
                    PostQueue::class, 
                    UserNotification::class, 
                    AdvertisingDelivery::class, 
                    AdvertisingStatistic::class, 
                    CommentItem::class, 
                    CommentReplyItem::class,
                    GroupHashtagTrending::class,
                    UserPostPaidOrder::class,
                    UserActionLog::class,
                    StoryItem::class,
                    ChatbotHistory::class,
                    PhotoVerifyItem::class,
                ],
            ])->everyTenMinutes();
            $schedule->command('cache:gc')->daily();
            $schedule->command('shaun_core:subscription_run')->withoutOverlapping(10)->everyFiveMinutes();
            $schedule->command('shaun_core:task_check_tmp_file')->withoutOverlapping(10)->everyFiveMinutes();
            $schedule->command('shaun_core:post_statistic_run')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_core:user_list_message_run')->withoutOverlapping(10)->everyMinute();
            $schedule->command('shaun_core:sitemap_run')->withoutOverlapping(10)->everyMinute();
        });

        if (! alreadyInstalled()) {
            return;
        }

        //Sql log
        if (env('SQL_LOG_ENABLE')) {
            DB::listen(function ($query) {
                $sql = $query->sql;
                foreach ($query->bindings as $binding) {
                    if (is_string($binding)) {
                        $binding = "'{$binding}'";
                    } elseif ($binding === null) {
                        $binding = 'NULL';
                    } elseif ($binding instanceof Carbon) {
                        $binding = "'{$binding->toDateTimeString()}'";
                    } elseif ($binding instanceof DateTime) {
                        $binding = "'{$binding->format('Y-m-d H:i:s')}'";
                    }

                    $sql = preg_replace("/\?/", $binding, $sql, 1);
                }
                Log::channel('shaun_sql')->info('SQL', ['sql' => $sql, 'time' => "$query->time ms"]);
            });

            Event::listen(TransactionBeginning::class, function (TransactionBeginning $event) {
                Log::channel('shaun_sql')->info('START TRANSACTION');
            });

            Event::listen(TransactionCommitted::class, function (TransactionCommitted $event) {
                Log::channel('shaun_sql')->info('COMMIT');
            });

            Event::listen(TransactionRolledBack::class, function (TransactionRolledBack $event) {
                Log::channel('shaun_sql')->info('ROLLBACK');
            });
        }

        //Cache sanctum
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Auth::provider('eloquent', function () {
            return resolve(ShaunEloquentUserProvider::class);
        });

        //Overwrite cache config
        if (! config('app.debug')) {
            if (env('CLOUD_ENABLE') && env('REDIS_HOST')) {
                $cacheSetting = [
                    'driver' => 'redis',
                    'redis_host' => env('REDIS_HOST'),
                    'redis_port' => env('REDIS_PORT'),
                    'redis_password' => env('REDIS_PASSWORD'),
                ];
            } else {
                $cacheSetting = getCacheSetting(); 
            }
            setCacheConfig($cacheSetting);
        }

        //Overwrite core config

        if (env('CLOUD_ENABLE') && env('APP_URL')) {
            setSetting('site.url', env('APP_URL'));
        }

        if (! request()->is(env('APP_ADMIN_PREFIX', 'admin').'/*')) {
            $assetUrl = env('CDN_CLOUD_URL');
            config([
                'app.url' => setting('site.url',env('APP_URL')),
                'app.asset_url' => $assetUrl ? $assetUrl : (setting('site.cdn_url') ? setting('site.cdn_url') : setting('site.url')),
                'filesystems.disks.public.url' => setting('site.url').'/storage'
            ]);
            app('url')->forceRootUrl(setting('site.url',env('APP_URL')));
        }
        $mailScheme = setting('mail.encryption', 'tls');
        if ($mailScheme == 'ssl') {
            $mailScheme = 'smtps';
        }
        //Overwrite mail config
        config([
            'mail.default' => setting('mail.engine', 'smtp'),
            'mail.mailers.smtp.host' => setting('mail.host', 'smtp.mailgun.org'),
            'mail.mailers.smtp.port' => setting('mail.port', 587),
            'mail.mailers.smtp.scheme' => $mailScheme,
            'mail.mailers.smtp.username' => setting('mail.username'),
            'mail.mailers.smtp.password' => setting('mail.password'),
            'mail.from.address' => setting('mail.address', setting('site.email')),
            'mail.from.name' => setting('mail.name', setting('site.title')),
        ]);

        //Overwrite filesystems config
        if (! env('FILESYSTEM_CLOUD')) {
            try {
                $storageServices = StorageService::getAll();
                setFileSystemsConfig($storageServices);
            } catch (Exception $e) {
    
            }
        }

        //Broadcast
        if (setting('broadcast.enable') && setting('broadcast.key') && ! env('CLOUD_ENABLE')) {
            $host = setting('broadcast.host');
            config([
                'broadcasting.default' => 'pusher',
                'broadcasting.connections.pusher.key' => setting('broadcast.key'),
                'broadcasting.connections.pusher.secret' => setting('broadcast.secret'),
                'broadcasting.connections.pusher.app_id' => setting('broadcast.id'),
                'broadcasting.connections.pusher.options.host' => $host ? $host : 'api-'.setting('broadcast.cluster').'.pusher.com',
                'broadcasting.connections.pusher.options.port' => setting('broadcast.port'),
                'broadcasting.connections.pusher.options.scheme' => setting('broadcast.scheme'),
                'broadcasting.connections.pusher.options.useTLS' => setting('broadcast.force_tls'),
            ]);
        }

        //Rate limit
        RateLimiter::for('api', function (Request $request) {
            if (env('CLOUD_ENABLE')) {
                if ($request->user()) {
                    return Limit::perMinute(config('shaun_core.core.attempt_limit'))->by($request->user()->id);
                }
                
                return Limit::perMinute(env('CLOUD_RATE_LIMIT_API', 1000))->by($request->ip());
            }

            return Limit::perMinute(config('shaun_core.core.attempt_limit'))->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $limit = setting('feature.bad_login_limit');
            if (!$limit || ! is_int($limit)) {
                $limit = config('shaun_core.core.attempt_limit');    
            }
            $time = setting('feature.bad_login_time');
            if (!$time || ! is_int($time)) {
                $time = 5;
            }

            return Limit::perMinutes($time, $limit)->by($request->input('email'));
        });

        RateLimiter::for('send_email', function (Request $request) {
            return Limit::perMinute(config('shaun_core.core.send_email_attempt_limit'))->by($request->user()?->id ?: $request->ip());
        });
    }
}
