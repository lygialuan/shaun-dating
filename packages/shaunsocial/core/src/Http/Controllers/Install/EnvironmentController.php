<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Repositories\Helpers\Install\EnvironmentManager;

class EnvironmentController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @param  EnvironmentManager  $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentWizard()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('shaun_core::install.environment-wizard', compact('envConfig'));
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param  Request  $request
     * @param  Redirector  $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        $form_rules = config('shaun_core_install.validation.form');
        $rules = [];

        foreach ($form_rules as $rule) {
            $rules = array_merge($rules, $rule);
        }

        $messages = [
            'environment_custom.required_if' => __('Environment Name is required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $redirect->route('install.environmentWizard')->withInput()->withErrors($validator->errors());
        }

        if (! $this->checkDatabaseConnection($request)) {
            return $redirect->route('install.environmentWizard')->withInput()->withErrors([
                'database_connection' => __('Could not connect to the database.'),
            ]);
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        return $redirect->route('install.database')
                        ->with(['results' => $results]);
    }

    /**
     * Validate database connection with user credentials (Form Wizard).
     *
     * @param  Request  $request
     * @return bool
     */
    private function checkDatabaseConnection(Request $request)
    {
        $connection = $request->input('database_connection');

        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        try {
            DB::purge();
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate form data
     *
     * @return string
     */
    public function validate(Request $request)
    {
        $rules = config('shaun_core_install.validation.form.'.$request->input('action'));

        $messages = [
            'database_connection.required' => __('Database connection is required.'),
            'database_hostname.required' => __('Database hostname is required.'),
            'database_port.required' => __('Database port is required.'),
            'database_name.required' => __('Database name is required.'),
            'database_username.required' => __('Database username is required.'),
            'app_admin_prefix.required' => __('Admin prefix is required.'),
            'app_admin_prefix.alpha_dash' => __('Admin prefix must only contain letters, numbers, dashes and underscores.'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if (! $validator->fails() && $request->input('action') == 'database') {
            if (! $this->checkDatabaseConnection($request)) {
                return response()->json([
                    'error' => __('Could not connect to the database.'),
                ]);
            }
        }

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
    }
}
