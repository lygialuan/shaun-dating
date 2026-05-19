<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Install;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnvironmentManager
{
    /**
     * @var string
     */
    private $envPath;

    /**
     * @var string
     */
    private $envExamplePath;

    /**
     * Set the .env and .env.example paths.
     */
    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
    }

    /**
     * Get the content of the .env file.
     *
     * @return string
     */
    public function getEnvContent()
    {
        if (file_exists($this->envPath)) {
            return file_get_contents($this->envPath);
        }

        return '';
    }

    /**
     * Get the the .env file path.
     *
     * @return string
     */
    public function getEnvPath()
    {
        return $this->envPath;
    }

    /**
     * Get the the .env.example file path.
     *
     * @return string
     */
    public function getEnvExamplePath()
    {
        return $this->envExamplePath;
    }

    /**
     * Save the form content to the .env file.
     *
     * @param  Request  $request
     * @return string
     */
    public function saveFileWizard(Request $request)
    {
        $results = __('Your .env file settings have been saved.');

        $envFileData =
        'APP_NAME='.Str::random(32)."\n".
        'APP_ENV='.config('shaun_core_install.env.app_env')."\n".
        'APP_KEY='.'base64:'.base64_encode(Str::random(32))."\n".
        'APP_DEBUG='.config('shaun_core_install.env.app_debug')."\n".
        'APP_ADMIN_PREFIX='.$request->app_admin_prefix."\n\n".
        'APP_URL='."\n".
        'LOG_CHANNEL='.config('shaun_core_install.env.log_channel')."\n".
        'LOG_LEVEL='.config('shaun_core_install.env.log_level')."\n\n".
        'DB_CONNECTION='.$request->database_connection."\n".
        'DB_HOST='.$request->database_hostname."\n".
        'DB_PORT='.$request->database_port."\n".
        'DB_DATABASE='.$request->database_name."\n".
        'DB_USERNAME='.$request->database_username."\n".
        'DB_PASSWORD=\''.$request->database_password."'\n".
        'DB_PREFIX='.$request->database_prefix."\n\n".
        'BROADCAST_DRIVER='.config('shaun_core_install.env.broadcast_driver')."\n".
        'CACHE_DRIVER='.config('shaun_core_install.env.cache_driver')."\n".
        'FILESYSTEM_DRIVER='.config('shaun_core_install.env.file_system_driver')."\n".
        'QUEUE_CONNECTION='.config('shaun_core_install.env.queue_connection')."\n".
        'SESSION_DRIVER='.config('shaun_core_install.env.session_driver')."\n".
        'SESSION_LIFETIME='.config('shaun_core_install.env.session_lifetime')."\n\n".
        'REDIS_HOST='.config('shaun_core_install.env.redis_host')."\n".
        'REDIS_PASSWORD='.config('shaun_core_install.env.redis_password')."\n".
        'REDIS_PORT='.config('shaun_core_install.env.redis_port')."\n\n".
        'MAIL_MAILER='.config('shaun_core_install.env.mail_mailer')."\n".
        'MAIL_HOST='.config('shaun_core_install.env.mail_host')."\n".
        'MAIL_PORT='.config('shaun_core_install.env.mail_port')."\n".
        'MAIL_USERNAME='.config('shaun_core_install.env.mail_username')."\n".
        'MAIL_PASSWORD='.config('shaun_core_install.env.mail_password')."\n".
        'MAIL_ENCRYPTION='.config('shaun_core_install.env.mail_encryption')."\n".
        'MAIL_FROM_ADDRESS='.config('shaun_core_install.env.mail_from_address')."\n".
        'MAIL_FROM_NAME='.config('shaun_core_install.env.mail_from_name')."\n\n".
        'REVERB_APP_ID='.Str::random(16)."\n".
        'REVERB_APP_KEY='.Str::random(16)."\n".
        'REVERB_APP_SECRET='.Str::random(16)."\n".
        'REVERB_HOST='."\n".
        'REVERB_PORT=443'."\n".
        'REVERB_SCHEME=https'."\n".
        'REVERB_APP_CLUSTER=mt1'."\n".
        'AWS_ACCESS_KEY_ID='."\n".
        'AWS_SECRET_ACCESS_KEY='."\n".
        'AWS_DEFAULT_REGION=us-east-1'."\n".
        'AWS_BUCKET='."\n".
        'AWS_USE_PATH_STYLE_ENDPOINT=false'."\n".
        'ASSET_URL='."\n".
        'VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"'."\n".
        'VITE_PUSHER_HOST="${PUSHER_HOST}"'."\n".
        'VITE_PUSHER_PORT="${PUSHER_PORT}"'."\n".
        'VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"'."\n".
        'VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"'."\n".
        'CDN_CLOUD_URL='."\n".
        'SQL_LOG_ENABLE='."\n".
        'SERVER_FFMPEG_THREADS='."\n".
        'ADMIN_SHOW_MANAGE_MESSAGE=true'."\n".
        'VIDEO_NO_NEED_CONVERT_MP4=true'."\n".
        'FORCE_HTTPS='."\n";

        try {
            file_put_contents($this->envPath, $envFileData);
        } catch (Exception $e) {
            $results = __('Unable to save the .env file, Please create it manually.');
        }

        return $results;
    }
}
