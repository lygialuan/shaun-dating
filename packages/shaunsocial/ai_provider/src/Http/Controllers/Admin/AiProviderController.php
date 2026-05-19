<?php

namespace Packages\ShaunSocial\AiProvider\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Packages\ShaunSocial\AiProvider\Models\AiProvider;

class AiProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.ai_provider.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('AI Providers'),
            ],
        ];

        $title = __('AI Providers');
        $providers = AiProvider::withCount('keys')->get();

        return view('shaun_ai_provider::admin.ai_provider.index', compact('breadcrumbs', 'title', 'providers'));
    }
}
