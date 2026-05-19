<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingReportStatus;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatus;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Advertising\Models\AdvertisingReport;
use Packages\ShaunSocial\Advertising\Notification\AdvertisingAdminChangeStatusNotification;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Support\Facades\Notification;

class AdvertisingController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.advertising.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Advertisings'),
            ],
        ];

        $title = __('Advertisings');

        $prefix = env('DB_PREFIX');
        $builder = Advertising::selectRaw($prefix.'advertisings.*')->orderBy('advertisings.id','DESC');

        $statusArray = AdvertisingStatus::getAll();

        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }
        if ($status) {
            $builder->where('status', $status);
        }

        $userName = $request->query('user_name', '');
        if ($userName) {
            $builder->join('users', function ($join) use ($userName) {
                $join->on('users.id', '=', 'advertisings.user_id')->where(function ($query) use ($userName){
                    $query->where('users.name', 'LIKE', '%'.$userName.'%')->orWhere('users.user_name', 'LIKE', '%'.$userName.'%');
                });
                    
            });
        }

        $name = $request->query('name', '');
        if ($name) {
            $builder->where('advertisings.name', 'LIKE', '%'.$name.'%');
        }

        $start = $request->query('start', '');
        if ($start) {
            $builder->where('advertisings.start', '>=', $start.' 00:00:00');
        }

        $end = $request->query('end', '');
        if ($end) {
            $builder->where('advertisings.end', '<=', $end. ' 23:59:59');
        }

        $advertisings = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_advertising::admin.advertising.index', compact('breadcrumbs', 'title', 'advertisings', 'statusArray', 'status', 'userName', 'name', 'start', 'end'));
    }

    public function detail(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Advertisings'),
                'route' => 'admin.advertising.index',
            ],
            [
                'title' => __('Advertising detail'),
            ],
        ];
        $title = __('Advertising detail');
        $builder = AdvertisingReport::where('advertising_id', $request->id)->where('status',AdvertisingReportStatus::DONE)->orderBy('id', 'DESC');
        $reports = $builder->paginate(setting('feature.item_per_page'));
        $advertising = Advertising::findOrFail($request->id);

        return view('shaun_advertising::admin.advertising.detail', compact('breadcrumbs', 'title', 'advertising', 'reports'));        
    }

    public function store_enable(Request $request)
    {
        $id = $request->id;
        $advertising = Advertising::findOrFail($id);

        if (! $advertising->canEnable()) {
            abort(400);
        }

        $advertising->update([
            'status' => AdvertisingStatus::ACTIVE
        ]);

        Notification::send($advertising->getUser(), $request->user(), AdvertisingAdminChangeStatusNotification::class, $advertising, ['params' => ['status' => 'enable']], 'shaun_advertising', false);

        return redirect()->back()->with([
            'admin_message_success' => __('Advertising has been successfully enabled.'),
        ]);
    }

    public function store_stop(Request $request)
    {
        $id = $request->id;
        $advertising = Advertising::findOrFail($id);

        if (! $advertising->canStop()) {
            abort(400);
        }

        $advertising->update([
            'status' => AdvertisingStatus::STOP
        ]);

        Notification::send($advertising->getUser(), $request->user(), AdvertisingAdminChangeStatusNotification::class, $advertising, ['params' => ['status' => 'stop']], 'shaun_advertising', false);

        return redirect()->back()->with([
            'admin_message_success' => __('Advertising has been successfully stoped.'),
        ]);
    }

    public function store_complete(Request $request)
    {
        $id = $request->id;
        $advertising = Advertising::findOrFail($id);

        if (! $advertising->canComplete()) {
            abort(400);
        }

        $result = $advertising->onDone();

        if ($result['status']) {
            Notification::send($advertising->getUser(), $request->user(), AdvertisingAdminChangeStatusNotification::class, $advertising, ['params' => ['status' => 'complete']], 'shaun_advertising', false);

            return redirect()->back()->with([
                'admin_message_success' => __('Advertising has been successfully completed.'),
            ]);
        } else {
            return redirect()->back()->with([
                'admin_message_error' => __($result['message']),
            ]);
        }
    }
}
