<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\Core\Models\StorageService;
use Throwable;

class StorageController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.storage.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Storage Services'),
            ],
        ];
        $title = __('Storage Service');
        $services = StorageService::getAll();
        $transfer = Key::getValue('storage_service_transfer');
        $localService = null;
        if ($transfer) {
            foreach ($services as $service) {
                if ($service->key == 'public') {
                    $localService = $service;
                    break;
                }
            }
        }

        return view('shaun_core::admin.storage.index', compact('breadcrumbs', 'title', 'services', 'transfer', 'localService'));
    }

    public function edit($id)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Storage Services'),
                'route' => 'admin.storage.index',
            ],
            [
                'title' => __('Edit Storage Service'),
            ],
        ];
        $title = __('Edit Storage Service');
        $service = StorageService::findOrFail($id);

        return view('shaun_core::admin.storage.edit', compact('title', 'service', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $service = StorageService::findOrFail($request->id);
        $config = [];

        switch ($service->key) {
            case 'public':
                $config = [
                    'url' => $request->url,
                ];
                break;
            case 's3':
                $config = [
                    'key' => $request->key,
                    'secret' => $request->secret,
                    'region' => $request->region,
                    'bucket' => $request->bucket,
                    'url' => $request->url,
                ];
                $service->config = json_encode($config);
                setFileSystemsConfig([$service], true);
                try {
                    $result = Storage::disk('s3')->put('test.txt', 'hello');
                    if (! $result) {
                        throw new Exception(__('Please double-check your S3 Credentials.'));
                    }
                    Storage::disk('s3')->delete('test.txt');
                } catch (Throwable $e) {
                    return back()->withInput($request->input())->withErrors(['message' => $e->getMessage()]);
                }

                break;
            case 'wasabi':
                $config = [
                    'key' => $request->key,
                    'secret' => $request->secret,
                    'region' => $request->region,
                    'bucket' => $request->bucket,
                    'url' => $request->url,
                ];
                $service->config = json_encode($config);
                setFileSystemsConfig([$service], true);
                try {
                    $result = Storage::disk('wasabi')->put('test.txt', 'hello');
                    if (! $result) {
                        throw new Exception(__('Please double-check your wasabi S3 Credentials.'));
                    }
                    Storage::disk('wasabi')->delete('test.txt');
                } catch (Throwable $e) {
                    return back()->withInput($request->input())->withErrors(['message' => $e->getMessage()]);
                }

                break;
            case 'do':
                $config = [
                    'key' => $request->key,
                    'secret' => $request->secret,
                    'bucket' => $request->bucket,
                    'endpoint' => $request->endpoint,
                    'url' => $request->url
                ];
                $service->config = json_encode($config);
                setFileSystemsConfig([$service], true);
                try {
                    $result = Storage::disk('do')->put('test.txt', 'hello');
                    if (! $result) {
                        throw new Exception(__('Please double-check your Digitalocean S3 Credentials.'));
                    }
                    Storage::disk('do')->delete('test.txt');
                } catch (Throwable $e) {
                    return back()->withInput($request->input())->withErrors(['message' => $e->getMessage()]);
                }

                break;

            case 'r2':
                $config = [
                    'key' => $request->key,
                    'secret' => $request->secret,
                    'bucket' => $request->bucket,
                    'endpoint' => $request->endpoint,
                    'url' => $request->url
                ];
                $service->config = json_encode($config);
                setFileSystemsConfig([$service], true);
                try {
                    $result = Storage::disk('r2')->put('test.txt', 'hello');
                    if (! $result) {
                        throw new Exception(__('Please double-check your R2 Credentials.'));
                    }
                    Storage::disk('r2')->delete('test.txt');
                } catch (Throwable $e) {
                    return back()->withInput($request->input())->withErrors(['message' => $e->getMessage()]);
                }
                break;
            case 'backblaze':
                $config = [
                    'key' => $request->key,
                    'secret' => $request->secret,
                    'bucket' => $request->bucket,
                    'endpoint' => $request->endpoint,
                    'url' => $request->url,
                ];
                $service->config = json_encode($config);
                setFileSystemsConfig([$service], true);
                try {
                    $result = Storage::disk('backblaze')->put('test.txt', 'hello');
                    if (! $result) {
                        throw new Exception(__('Please double-check your Backblaze B2 Credentials.'));
                    }
                    Storage::disk('backblaze')->delete('test.txt');
                } catch (Throwable $e) {
                    return back()->withInput($request->input())->withErrors(['message' => $e->getMessage()]);
                }
                break;

        }
        $request->mergeIfMissing([
            'is_default' => 0,
        ]);

        $data = $request->except('id', '_token', 'key');
        $data['config'] = json_encode($config);

        if ($service->is_default) {
            $data['is_default'] = true;
        }

        if (! $service->is_default && $data['is_default']) {
            StorageService::query()->update(['is_default' => 0]);
        }
        $service->update($data);

        return redirect()->route('admin.storage.index')->with([
            'admin_message_success' => __('Your changes have been saved.'),
        ]);
    }

    public function transfer($id)
    {
        $service = StorageService::findOrFail($id);
        $transfer = Key::getValue('storage_service_transfer');
        if ($transfer) {
            abort(404);
        }

        Key::setValue('storage_service_transfer', $service->key);

        return redirect()->route('admin.storage.index')->with([
            'admin_message_success' => __('A job has been created to transfer files.'),
        ]);
    }

    public function stop_transfer()
    {
        $transfer = Key::getValue('storage_service_transfer');
        if (! $transfer) {
            abort(404);
        }

        Key::removeKey('storage_service_transfer');

        return redirect()->route('admin.storage.index')->with([
            'admin_message_success' => __('The transfer files have been stopped.'),
        ]);
    }
}
