<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Throwable;

class CacheController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.cache.setting');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Cache Settings'),
            ],
        ];
        $title = __('Cache Settings');
        $drivers = [
            'file' => 'Files',
            'memcached' => 'Memcached',
            'redis' => 'Redis',
        ];
        $cacheSetting = getCacheSetting();

        return view('shaun_core::admin.cache.index', compact('breadcrumbs', 'title', 'drivers', 'cacheSetting'));
    }

    public function store(Request $request)
    {
        __("Class 'Memcached' not found.");
        __("Class 'Redis' not found.");
        __('NOAUTH Authentication required.');
        __('No connection could be made because the target machine actively refused it.');

        $driver = $request->driver;

        try {
            switch ($driver) {
                case 'memcached':
                    $rules = [
                        'memcached_host' => 'required',
                    ];
                    $validation = Validator::make(
                        $request->all(),
                        $rules,
                        [
                            'memcached_host.required' => __('The host is required.'),
                        ]
                    );
                    if ($validation->fails()) {
                        return response()->json([
                            'status' => false,
                            'messages' => $validation->getMessageBag()->all(),
                        ]);
                    }
                    setCacheConfig($request->toArray(), true);
                    $checked = Cache::store('memcached')->put('test', 'test', 10);
                    break;
                case 'redis':
                    $rules = [
                        'redis_host' => 'required',
                    ];
                    $validation = Validator::make(
                        $request->all(),
                        $rules,
                        [
                            'redis_host.required' => __('The host is required.'),
                        ]
                    );
                    if ($validation->fails()) {
                        return response()->json([
                            'status' => false,
                            'messages' => $validation->getMessageBag()->all(),
                        ]);
                    }
                    setCacheConfig($request->toArray(), true);
                    $checked = Cache::store('redis')->put('test', 'test', 10);
                    break;
                default:
                    $checked = true;
                    break;
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'messages' => [__($e->getMessage())],
            ]);
        }

        if (! $checked) {
            return response()->json([
                'status' => false,
                'messages' => [__("Can't connect to server.")],
            ]);
        }

        $filePath = storage_path(config('shaun_core.cache.file_name_config'));

        file_put_contents($filePath, json_encode($request->toArray()));

        $request->session()->flash('admin_message_success', __('Cache has been successfully updated.'));

        return response()->json([
            'status' => true,
        ]);
    }
}
