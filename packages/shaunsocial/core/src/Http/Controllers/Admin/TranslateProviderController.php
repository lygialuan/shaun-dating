<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\TranslateProvider;

class TranslateProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.translate_provider.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Translate Providers'),
            ],
        ];
        $title = __('Translate Providers');
        $providers = TranslateProvider::all();

        return view('shaun_core::admin.translate_provider.index', compact('breadcrumbs', 'title', 'providers'));
    }

    public function edit($id)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Translate Providers'),
                'route' => 'admin.translate_provider.index',
            ],
            [
                'title' => __('Edit Translate Provider'),
            ],
        ];
        $title = __('Edit Translate Provider');
        $provider = TranslateProvider::findOrFail($id);

        return view('shaun_core::admin.translate_provider.edit', compact('title', 'provider', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $provider = TranslateProvider::findOrFail($request->id);

        $request->mergeIfMissing([
            'is_default' => 0,
        ]);

        $data = $request->except('id', '_token', 'key');
        
        $data['config'] = json_encode($data['config']);

        if ($provider->is_default) {
            $data['is_default'] = true;
        }

        if (! $provider->is_default && $data['is_default']) {
            TranslateProvider::query()->update(['is_default' => 0]);
        }
        
        $provider->update($data);

        return redirect()->route('admin.translate_provider.index')->with([
            'admin_message_success' => __('Your changes have been saved.'),
        ]);
    }
}
