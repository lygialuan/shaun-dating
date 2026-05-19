<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel server requirements, you can add as many
    | as your application require, we check if the extension is enabled
    | by looping through the array and run "extension_loaded" on it.
    |
    */
    'core' => [
        'minPhpVersion' => '8.2.0',
    ],
    'final' => [
        'key' => true,
        'publish' => false,
    ],
    'requirements' => [
        'php' => [
            'openssl',
            'pdo',
            'mbstring',
            'tokenizer',
            'JSON',
            'cURL',
            'gd',
            'zip',
            'exif',
            'fileinfo'
        ],
        'apache' => [
            'mod_rewrite',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        '/storage' => '755',
        '/bootstrap/cache' => '755'
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Form Wizard Validation Rules & Messages
    |--------------------------------------------------------------------------
    |
    | This are the default form field validation rules. Available Rules:
    | https://laravel.com/docs/5.4/validation#available-validation-rules
    |
    */
    'validation' => [
        'form' => [
            'environment' => [
                'app_admin_prefix' => 'required|string|max:50|alpha_dash'
            ],
            'database' => [
                'database_connection' => 'required|string|max:50',
                'database_hostname' => 'required|string|max:256',
                'database_port' => 'required|numeric',
                'database_name' => 'required|string|max:128',
                'database_username' => 'required|string|max:128',
                'database_password' => 'nullable|string|max:128',
            ],
        ],
        'setting' => [
            'rules' => [
                'site_title' => 'required|string|max:100',
                'site_email' => 'required|string|max:50|email',
            ],
        ],
        'admin' => [
            'rules' => [
                'name' => 'required|string|max:50',
                'email' => 'required|string|max:50|email|unique:users,email',
                'password' => 'required|string|max:50',
                'confirm_password' => 'required|string|max:50|same:password',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Installed Middleware Options
    |--------------------------------------------------------------------------
    | Different available status switch configuration for the
    | canInstall middleware located in `canInstall.php`.
    |
    */
    'installed' => [
        'redirectOptions' => [
            'route' => [
                'name' => 'welcome',
                'data' => [],
            ],
            'abort' => [
                'type' => '404',
            ],
            'dump' => [
                'data' => 'Dumping a not found message.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Selected Installed Middleware Option
    |--------------------------------------------------------------------------
    | The selected option fo what happens when an installer instance has been
    | Default output is to `/resources/views/error/404.blade.php` if none.
    | The available middleware options include:
    | route, abort, dump, 404, default, ''
    |
    */
    'installedAlreadyAction' => '',

    /*
    |--------------------------------------------------------------------------
    | Updater Enabled
    |--------------------------------------------------------------------------
    | Can the application run the '/update' route with the migrations.
    | The default option is set to False if none is present.
    | Boolean value
    |
    */
    'updaterEnabled' => 'true',

    /*
    |--------------------------------------------------------------------------
    | Updater Version Control
    |--------------------------------------------------------------------------
    */
    'version' => 'v1.0',

    /*
  |--------------------------------------------------------------------------
  | Default migration install folder
  |--------------------------------------------------------------------------
  */
    'migrationInstallFolder' => 'install',

    /*
  |--------------------------------------------------------------------------
  | Default env configuration
  |--------------------------------------------------------------------------
  */
    'env' => [
        'app_env' => 'production',
        'app_debug' => 'false',

        'log_channel' => 'shaun',
        'log_level' => 'warning',

        'broadcast_driver' => 'log',
        'cache_driver' => 'file',
        'file_system_driver' => 'local',
        'queue_connection' => 'database',
        'session_driver' => 'database',
        'session_lifetime' => '120',

        'redis_host' => '',
        'redis_password' => '',
        'redis_port' => '',

        'pusher_app_id' => '',
        'pusher_app_key' => '',
        'pusher_app_secret' => '',
        'pusher_app_cluster' => '',

        'mail_mailer' => 'smtp',
        'mail_host' => 'localhost',
        'mail_port' => '25',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => '',
        'mail_from_address' => '',
        'mail_from_name' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting fields display
    |--------------------------------------------------------------------------
    */
    'settingField' => [
        'site.title',
        'site.email',
        'site.url',
        'site.timezone',
    ],

    /*
    |--------------------------------------------------------------------------
    | Folder write
    |--------------------------------------------------------------------------
    */
    'writeFolders' => [
        '/',
        '/public',
        '/public/locales',
        '/lang',
        '/public/themes',
    ]
];
