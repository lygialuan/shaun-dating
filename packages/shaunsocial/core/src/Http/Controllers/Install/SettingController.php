<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Models\Setting;
use Packages\ShaunSocial\Core\Repositories\SettingRepository;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    protected $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Display the site setting page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $setting_fields = config('shaun_core_install.settingField');
        $settings = Setting::whereIn('key', $setting_fields)->orderBy('order')->get();

        return view('shaun_core::install.setting', compact('settings'));
    }

    /**
     * Processes the setting update
     *
     * @param  Request  $request
     * @param  Redirector  $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, Redirector $redirect)
    {
        $rules = config('shaun_core_install.validation.setting.rules');
        $messages = [];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $redirect->route('install.setting')->withInput()->withErrors($validator->errors());
        }

        $setting_fields = config('shaun_core_install.settingField');
        $settings = Setting::whereIn('key', $setting_fields)->get();

        foreach ($settings as $setting) {
            $content = $this->settingRepository->getContentBasedOnType($request, $setting);

            $setting->value = $content;
            $setting->save();
        }

        $secretKey = Setting::where('key', 'app.api_secret_key')->first();
        $secretKey->value = Str::random(6);
        $secretKey->save();

        return $redirect->route('install.admin');
    }

    /**
     * Validate form data
     *
     * @return string
     */
    public function validate(Request $request)
    {
        $rules = config('shaun_core_install.validation.setting.rules');
        $messages = [];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
    }
}
