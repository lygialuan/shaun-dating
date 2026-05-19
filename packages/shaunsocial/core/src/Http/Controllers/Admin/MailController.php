<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\MailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{
    protected $settingRepository;

    public function __construct()
    {
        $routerName = Route::getCurrentRoute()->getName();
        switch ($routerName) {
            case 'admin.mail.test':
            case 'admin.mail.store_test':
                return $this->middleware('has.permission:admin.setting.general');
                break;
        }
        $this->middleware('has.permission:admin.mail.manage');

    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Mail Template'),
            ],
        ];

        $title = __('Manage Mail Template');
        $templates = MailTemplate::all();

        return view('shaun_core::admin.mail.index', compact('breadcrumbs', 'title', 'templates'));
    }

    public function template($id)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Mail Template'),
                'route' => 'admin.mail.index',
            ],
            [
                'title' => __('Edit Mail Template'),
            ],
        ];

        $languages = Language::getAll();

        $template = MailTemplate::findOrFail($id);

        $title =  __($template->getKeyNameTranslate());

        return view('shaun_core::admin.mail.template', compact('breadcrumbs', 'title', 'languages', 'template'));
    }

    public function store_template(Request $request)
    {
        $data = $request->except('id', '_token');
        if (! $data['subject']) {
            $data['subject'] = '';
        }
        if (! $data['content']) {
            $data['content'] = '';
        }
        $template = MailTemplate::findOrFail($request->id);
        $template->update($data);
        $template->updateTranslations($data['language']);

        return redirect()->back()->with([
            'admin_message_success' => __('Mail template has been successfully saved.'),
        ]);
    }

    public function test(Request $request)
    {
        return view('shaun_core::admin.mail.test');
    }

    public function store_test(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',                
            ],
            [
                'email.required' => __('Email is required.'),
                'email.email' => __('The email must be a valid email address.'),
            ]
        );
        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $email = $request->email;
        try {
            Mail::raw('Text to e-mail', function($message) use ($email) {
                $message->subject('Test Email')->to($email);
            });
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => [$e->getMessage()],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('Test email was sent successfully! Please check your inbox to make sure it is delivered.')
        ]);
    }

    public function translate($package = 'shaun_core')
    {
        $templates = MailTemplate::all();
        foreach ($templates as $template) {
            if ($package == $template->package) {
                echo "__('".$template->getKeyNameTranslate()."');\n";
            }            
        }
    }
}
